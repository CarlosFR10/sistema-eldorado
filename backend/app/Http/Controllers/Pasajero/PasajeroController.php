<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasajero;

use App\Http\Controllers\Controller;
use App\Models\MenorAdultoResponsable;
use App\Models\Pasajero;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PasajeroController extends Controller
{
    public function index(Request $request)
    {
        $query = Pasajero::query()
            ->with(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor'])
            ->when($request->filled('ci'), fn ($q) => $q->where('numero_ci', 'like', '%' . $request->string('ci') . '%'))
            ->when($request->filled('nombre'), function ($q) use ($request): void {
                $term = '%' . $request->string('nombre') . '%';
                $q->where(fn ($inner) => $inner->where('nombres', 'like', $term)->orWhere('apellidos', 'like', $term));
            });

        if ($request->filled('es_menor')) {
            $query->whereRaw('(DATEDIFF(CURDATE(), fecha_nacimiento) < 6570) = ?', [$request->boolean('es_menor') ? 1 : 0]);
        }

        if ($request->filled('expedido_en')) {
            $query->where('expedido_en', $request->string('expedido_en'));
        }

        if ($request->filled('huella')) {
            $query->where('tiene_huella', $request->string('huella')->toString() === 'verificada');
        }

        $query->orderBy('apellidos')->orderBy('nombres');

        return $this->success($query->paginate((int) $request->integer('per_page', 50)), 'Lista de pasajeros.');
    }

    public function store(Request $request)
    {
        if ($request->filled('numero_ci')) {
            $porCi = Pasajero::where('numero_ci', $request->string('numero_ci'))->first();
            if ($porCi) {
                return $this->error(
                    'El pasajero ya existe en el registro.',
                    ['numero_ci' => ['Ya existe: ' . trim($porCi->nombres . ' ' . $porCi->apellidos) . ' / CI ' . $porCi->numero_ci]],
                    'PASAJERO_DUPLICADO',
                    409
                );
            }
        }

        $data = $this->validatedData($request);

        if ($existente = $this->buscarDuplicado($data)) {
            return $this->error(
                'El pasajero ya existe en el registro.',
                ['pasajero' => ['Ya existe: ' . trim($existente->nombres . ' ' . $existente->apellidos) . ' / CI ' . $existente->numero_ci]],
                'PASAJERO_DUPLICADO',
                409
            );
        }

        $pasajero = Pasajero::create($data);

        return $this->success(
            $pasajero->fresh(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor']),
            'Pasajero registrado correctamente.',
            201
        );
    }

    public function show(int $id)
    {
        $pasajero = Pasajero::with([
            'huellas',
            'boletos.viaje.ruta',
            'boletos.asiento',
            'adultoResponsable.adultoResponsable',
            'menoresResponsables.menor',
        ])
            ->findOrFail($id);

        return $this->success($pasajero, 'Detalle del pasajero.');
    }

    public function update(Request $request, int $id)
    {
        $pasajero = Pasajero::findOrFail($id);
        $pasajero->update($this->validatedData($request, $pasajero->id));

        return $this->success($pasajero->fresh(), 'Pasajero actualizado correctamente.');
    }

    public function buscar(Request $request)
    {
        $data = $request->validate([
            'ci' => ['required', 'string', 'max:20'],
        ]);

        $pasajero = Pasajero::with(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor'])
            ->where('numero_ci', $data['ci'])
            ->first();

        if (!$pasajero) {
            return $this->error('No se encontro un pasajero con ese CI.', ['ci' => ['Pasajero no registrado']], 'PASAJERO_NO_ENCONTRADO', 404);
        }

        return $this->success($pasajero, 'Pasajero encontrado.');
    }

    public function publicBuscar(Request $request)
    {
        $data = $request->validate([
            'ci' => ['required', 'string', 'max:20'],
        ]);

        $pasajero = Pasajero::with(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor'])
            ->where('numero_ci', $data['ci'])
            ->first();

        if (!$pasajero) {
            return $this->error(
                'Pasajero no registrado. Puede pre-registrarse y completar huella en plataforma.',
                ['ci' => ['Pasajero no registrado']],
                'PASAJERO_NO_REGISTRADO',
                404
            );
        }

        return $this->success([
            ...$pasajero->toArray(),
            'requiere_huella' => !$pasajero->tiene_huella,
        ], 'Pasajero encontrado.');
    }

    public function publicStore(Request $request)
    {
        if ($request->filled('numero_ci')) {
            $existente = Pasajero::with(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor'])
                ->where('numero_ci', $request->string('numero_ci'))
                ->first();

            if ($existente) {
                return $this->success([
                    ...$existente->toArray(),
                    'requiere_huella' => !$existente->tiene_huella,
                ], 'Pasajero ya estaba registrado.', 200);
            }
        }

        $data = $this->validatedData($request);
        $pasajero = Pasajero::firstOrCreate(
            ['numero_ci' => $data['numero_ci']],
            $data + ['tiene_huella' => false]
        );

        return $this->success([
            ...$pasajero->fresh(['adultoResponsable.adultoResponsable', 'menoresResponsables.menor'])->toArray(),
            'requiere_huella' => !$pasajero->tiene_huella,
        ], $pasajero->wasRecentlyCreated ? 'Pre-registro realizado.' : 'Pasajero ya estaba registrado.', 201);
    }

    public function boletos(int $id)
    {
        $pasajero = Pasajero::findOrFail($id);

        return $this->success(
            $pasajero->boletos()->with(['viaje.ruta', 'viaje.bus', 'asiento'])->latest('id')->paginate(20),
            'Historial de boletos.'
        );
    }

    public function vincularAdulto(Request $request, int $id)
    {
        $menor = Pasajero::findOrFail($id);

        if ($menor->edad >= 18) {
            return $this->error('Solo se puede vincular adulto responsable a menores de edad.', [], 'PASAJERO_NO_ES_MENOR', 422);
        }

        $data = $request->validate([
            'adulto_responsable_id' => ['required', 'integer', 'exists:pasajeros,id', Rule::notIn([$menor->id])],
            'tipo_relacion' => ['required', Rule::in(['padre', 'madre', 'tutor_legal', 'acompanante_autorizado'])],
            'numero_permiso_dna' => ['nullable', 'string', 'max:50'],
            'fecha_permiso' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $adulto = Pasajero::findOrFail($data['adulto_responsable_id']);

        if ($adulto->edad < 18) {
            return $this->error('El adulto responsable debe ser mayor de edad.', [], 'ADULTO_INVALIDO', 422);
        }

        $relacion = MenorAdultoResponsable::updateOrCreate(
            ['menor_id' => $menor->id, 'adulto_responsable_id' => $adulto->id],
            [
                ...$data,
                'verificado_manualmente' => true,
                'verificado_por' => auth()->id(),
            ]
        );

        return $this->success($relacion->load(['menor', 'adultoResponsable']), 'Adulto responsable vinculado correctamente.', 201);
    }

    public function adultoResponsable(int $id)
    {
        $relacion = MenorAdultoResponsable::with(['menor', 'adultoResponsable'])
            ->where('menor_id', $id)
            ->latest('id')
            ->first();

        if (!$relacion) {
            return $this->error('El menor no tiene adulto responsable registrado.', [], 'ADULTO_NO_REGISTRADO', 404);
        }

        return $this->success($relacion, 'Adulto responsable encontrado.');
    }

    public function actualizarPermiso(Request $request, int $id)
    {
        $data = $request->validate([
            'numero_permiso_dna' => ['required', 'string', 'max:50'],
            'fecha_permiso' => ['required', 'date'],
            'observaciones' => ['nullable', 'string'],
        ]);

        $relacion = MenorAdultoResponsable::where('menor_id', $id)->latest('id')->firstOrFail();
        $relacion->update($data + ['verificado_por' => auth()->id(), 'verificado_manualmente' => true]);

        return $this->success($relacion->fresh(['menor', 'adultoResponsable']), 'Permiso DNA actualizado correctamente.');
    }

    private function validatedData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'numero_ci' => ['required', 'string', 'max:20', Rule::unique('pasajeros', 'numero_ci')->ignore($id)],
            'complemento_ci' => ['nullable', 'string', 'max:5'],
            'expedido_en' => ['required', 'string', 'size:2'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);
    }

    private function buscarDuplicado(array $data): ?Pasajero
    {
        $nombres = mb_strtolower(trim((string) $data['nombres']));
        $apellidos = mb_strtolower(trim((string) $data['apellidos']));

        return Pasajero::query()
            ->where(function ($query) use ($data, $nombres, $apellidos): void {
                $query
                    ->where('numero_ci', $data['numero_ci'])
                    ->orWhere(function ($inner) use ($data, $nombres, $apellidos): void {
                        $inner
                            ->whereRaw('LOWER(TRIM(nombres)) = ?', [$nombres])
                            ->whereRaw('LOWER(TRIM(apellidos)) = ?', [$apellidos])
                            ->whereDate('fecha_nacimiento', $data['fecha_nacimiento']);
                    });
            })
            ->first();
    }
}
