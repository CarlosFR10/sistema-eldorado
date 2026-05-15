<?php

declare(strict_types=1);

namespace App\Http\Controllers\Viaje;

use App\Http\Controllers\Controller;
use App\Models\Asiento;
use App\Models\Boleto;
use App\Models\Bus;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Models\Viaje;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ViajeController extends Controller
{
    private const ESTADOS = ['en_venta', 'abordando', 'en_ruta', 'completado', 'cancelado'];

    public function index(Request $request)
    {
        $viajes = Viaje::query()
            ->with(['ruta', 'bus', 'conductor.usuario'])
            ->withCount([
                'asientos as asientos_disponibles_count' => fn ($q) => $q->where('estado', 'disponible'),
                'boletos as boletos_vendidos_count' => fn ($q) => $q->whereIn('estado', ['pagado', 'abordado']),
            ])
            ->when($request->filled('fecha'), fn ($q) => $q->whereDate('fecha_salida', $request->date('fecha')))
            ->when($request->filled('ruta_id'), fn ($q) => $q->where('ruta_id', $request->integer('ruta_id')))
            ->when($request->filled('estado'), fn ($q) => $q->where('estado', $request->string('estado')))
            ->when(
                $request->is('api/public/viajes') && !$request->filled('estado'),
                fn ($q) => $q->whereNotIn('estado', ['cancelado', 'completado'])
            )
            ->orderBy('fecha_salida')
            ->paginate((int) $request->integer('per_page', 20));

        return $this->success($viajes, 'Lista de viajes.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ruta_id' => ['required', 'integer', 'exists:rutas,id'],
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'conductor_id' => ['nullable', 'integer', 'exists:conductores,id'],
            'fecha_salida' => ['required', 'date', 'after:now'],
            'fecha_llegada_est' => ['required', 'date', 'after:fecha_salida'],
            'precio_final' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['sometimes', Rule::in(self::ESTADOS)],
            'observaciones' => ['nullable', 'string'],
        ]);

        $viaje = DB::transaction(function () use ($data): Viaje {
            $ruta = Ruta::findOrFail($data['ruta_id']);
            $bus = Bus::findOrFail($data['bus_id']);

            $fechaSalida = \Carbon\Carbon::parse($data['fecha_salida']);
            $fechaLlegada = \Carbon\Carbon::parse($data['fecha_llegada_est']);

            $conflicto = Viaje::where('bus_id', $bus->id)
                ->whereIn('estado', ['en_venta', 'abordando', 'en_ruta', 'programado'])
                ->where(function ($query) use ($fechaSalida) {
                    $query->where('fecha_salida', '>=', $fechaSalida->copy()->subMinutes(150))
                          ->where('fecha_salida', '<=', $fechaSalida->copy()->addMinutes(10));
                })
                ->exists();

            if ($conflicto) {
                throw new \RuntimeException("El bus {$bus->placa} necesita 2:30 horas minimas desde su ultimo viaje. Escolja otro bus o cambie la hora.");
            }

            $viaje = Viaje::create([
                ...$data,
                'codigo_viaje' => $this->generarCodigoViaje(),
                'conductor_id' => $data['conductor_id'] ?? Conductor::query()->value('id'),
                'vendedor_id' => auth()->id() ?: 1,
                'precio_final' => $data['precio_final'] ?? $ruta->precio_base,
                'estado' => $data['estado'] ?? 'en_venta',
            ]);

            $this->generarAsientos($viaje, $bus);

            return $viaje;
        });

        return $this->success($viaje->load(['ruta', 'bus', 'asientos']), 'Viaje creado correctamente.', 201);
    }

    public function show(int $id)
    {
        $viaje = Viaje::with(['ruta', 'bus', 'conductor.usuario', 'vendedor', 'asientos', 'boletos.pasajero'])
            ->findOrFail($id);

        return $this->success($viaje, 'Detalle del viaje.');
    }

    public function updateEstado(Request $request, int $id)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(self::ESTADOS)],
            'observaciones' => ['nullable', 'string'],
        ]);

        $viaje = Viaje::findOrFail($id);
        $viaje->update($data);

        return $this->success($viaje->fresh(['ruta', 'bus']), 'Estado del viaje actualizado.');
    }

    public function asientos(int $id)
    {
        $viaje = Viaje::with('asientos')->findOrFail($id);
        $asientoIds = $viaje->asientos->pluck('id');
        $boletos = Boleto::query()
            ->where('viaje_id', $viaje->id)
            ->whereIn('asiento_id', $asientoIds)
            ->whereIn('estado', ['pendiente_verificacion', 'pagado', 'abordado'])
            ->with(['pasajero', 'adultoResponsable'])
            ->get()
            ->keyBy('asiento_id');

        $menoresPorAdulto = Boleto::query()
            ->where('viaje_id', $viaje->id)
            ->whereIn('estado', ['pendiente_verificacion', 'pagado', 'abordado'])
            ->whereNotNull('adulto_resp_id')
            ->with('pasajero')
            ->get()
            ->groupBy('adulto_resp_id')
            ->map(fn ($items) => $items
                ->map(fn (Boleto $boleto) => trim((string) ($boleto->pasajero?->nombres . ' ' . $boleto->pasajero?->apellidos)))
                ->filter()
                ->values()
                ->all());

        $asientos = $viaje->asientos->map(function (Asiento $asiento) use ($boletos, $menoresPorAdulto): array {
            $boleto = $boletos->get($asiento->id);
            $pasajero = $boleto?->pasajero;
            $adulto = $boleto?->adultoResponsable;
            $edad = $pasajero?->edad;

            return array_merge($asiento->toArray(), [
                'boleto_id' => $boleto?->id,
                'boleto_estado' => $boleto?->estado,
                'pasajero_nombre' => $pasajero ? trim($pasajero->nombres . ' ' . $pasajero->apellidos) : null,
                'pasajero_ci' => $pasajero?->numero_ci,
                'pasajero_edad' => $edad,
                'es_menor' => (bool) ($boleto?->es_menor ?? false),
                'es_adulto_mayor' => $edad !== null && $edad >= 60,
                'adulto_responsable_nombre' => $adulto ? trim($adulto->nombres . ' ' . $adulto->apellidos) : null,
                'menores_acompanados' => $pasajero ? ($menoresPorAdulto->get($pasajero->id) ?? []) : [],
            ]);
        });

        return $this->success($asientos, 'Croquis de asientos del viaje.');
    }

    public function manifiesto(int $id)
    {
        $viaje = Viaje::with([
            'ruta',
            'bus',
            'conductor.usuario',
            'boletos.pasajero',
            'boletos.asiento',
            'boletos.adultoResponsable',
        ])->findOrFail($id);

        return $this->success($viaje, 'Manifiesto del viaje.');
    }

    public function boletos(int $id)
    {
        $boletos = Boleto::where('viaje_id', $id)
            ->with(['pasajero', 'asiento', 'adultoResponsable'])
            ->orderBy('id')
            ->get();

        return $this->success($boletos, 'Boletos del viaje.');
    }

    public function delDia()
    {
        $viajes = Viaje::with(['ruta', 'bus', 'asientos'])
            ->whereDate('fecha_salida', now())
            ->orderBy('fecha_salida')
            ->get()
            ->groupBy(fn (Viaje $viaje): string => $viaje->ruta?->codigo ?: 'SIN-RUTA');

        return $this->success($viajes, 'Viajes del dia agrupados por ruta.');
    }

    public function conductores()
    {
        return $this->success(
            Conductor::with('usuario')->orderBy('id')->get(),
            'Lista de conductores.'
        );
    }

    public function horasDisponibles(Request $request)
    {
        $busId = $request->integer('bus_id');
        $fecha = $request->date('fecha');

        if (!$busId || !$fecha) {
            return $this->error('Faltan bus_id o fecha.', 422);
        }

        $horasEstandares = ['06:00', '07:30', '09:00', '10:30', '12:00', '13:30', '15:00', '16:30', '18:00', '20:00'];
        $ahora = now();
        $esFechaHoy = $fecha->toDateString() === $ahora->toDateString();

        $viajesBusSeleccionado = Viaje::where('bus_id', $busId)
            ->whereDate('fecha_salida', $fecha)
            ->whereIn('estado', ['en_venta', 'programado', 'abordando', 'en_ruta'])
            ->orderBy('fecha_salida')
            ->get(['id', 'fecha_salida', 'estado']);

        $viajesTodosBuses = Viaje::whereDate('fecha_salida', $fecha)
            ->whereIn('estado', ['en_venta', 'programado', 'abordando', 'en_ruta'])
            ->get(['id', 'bus_id', 'fecha_salida', 'estado']);

        $horasOcupadasMap = [];
        foreach ($viajesBusSeleccionado as $v) {
            $horaExacta = $v->fecha_salida->format('H:i');
            $horasOcupadasMap[$horaExacta] = [
                'viaje_id' => $v->id,
                'hora' => $horaExacta,
                'estado' => $v->estado,
            ];
        }

        $horasOcupadasTodosBuses = $viajesTodosBuses
            ->groupBy(fn ($v) => $v->fecha_salida->format('H:i'))
            ->map(fn ($group) => $group->count())
            ->toArray();

        $disponibles = [];
        $bloqueados = [];
        $pasados = [];

        foreach ($horasEstandares as $horaStr) {
            [$h, $m] = array_map('intval', explode(':', $horaStr));
            $fechaHoraSlot = $fecha->copy()->setTime($h, $m, 0, 0);

            $yaPaso = $esFechaHoy && $fechaHoraSlot->lt($ahora);

            if ($yaPaso) {
                $pasados[] = $horaStr;
            } elseif (isset($horasOcupadasTodosBuses[$horaStr])) {
                $bloqueados[] = $horaStr;
            } else {
                $disponibles[] = $horaStr;
            }
        }

        return $this->success([
            'bus_id' => $busId,
            'fecha' => $fecha->toDateString(),
            'horas_estandar' => $horasEstandares,
            'disponibles' => $disponibles,
            'bloqueados' => $bloqueados,
            'pasados' => $pasados,
            'ocupados' => array_values($horasOcupadasMap),
        ], 'Horas disponibles para el bus en la fecha indicada.');
    }

    private function generarCodigoViaje(): string
    {
        $base = 'VJ-' . now()->format('Ymd') . '-';
        $ultimo = Viaje::where('codigo_viaje', 'like', $base . '%')
            ->get('codigo_viaje')
            ->map(fn (Viaje $v) => (int) str_replace($base, '', $v->codigo_viaje))
            ->max() + 1;

        return $base . str_pad((string) $ultimo, 3, '0', STR_PAD_LEFT);
    }

    private function generarAsientos(Viaje $viaje, Bus $bus): void
    {
        $config = $bus->config_asientos ?: ['columnas' => 4, 'pasillo' => 2];
        $columnas = max((int) ($config['columnas'] ?? 4), 1);
        $pasillo = (int) ($config['pasillo'] ?? 2);
        $especiales = $config['especiales'] ?? [];

        for ($numero = 1; $numero <= $bus->capacidad; $numero++) {
            $fila = (int) ceil($numero / $columnas);
            $columna = (($numero - 1) % $columnas) + 1;

            Asiento::create([
                'viaje_id' => $viaje->id,
                'numero' => $numero,
                'fila' => $fila,
                'columna' => $columna >= $pasillo ? $columna + 1 : $columna,
                'piso' => $bus->tipo_bus === 'doble_piso' && $numero > ($bus->capacidad / 2) ? 2 : 1,
                'tipo' => in_array($numero, $especiales, true) ? 'preferencial' : 'normal',
                'estado' => 'disponible',
            ]);
        }
    }
}
