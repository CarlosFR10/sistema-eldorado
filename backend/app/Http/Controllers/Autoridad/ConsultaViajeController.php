<?php

declare(strict_types=1);

namespace App\Http\Controllers\Autoridad;

use App\Http\Controllers\Controller;
use App\Models\Asiento;
use App\Models\Bus;
use App\Models\Boleto;
use App\Models\Pasajero;
use App\Models\UbicacionGps;
use App\Models\Viaje;
use App\Services\QrService;

class ConsultaViajeController extends Controller
{
    private const RUTAS_WAYPOINTS = [
        'CBB-LPZ' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -17.2500, 'lng' => -66.3500, 'nombre' => 'Oruro'],
            ['lat' => -16.5000, 'lng' => -68.1500, 'nombre' => 'El Alto'],
            ['lat' => -16.4890, 'lng' => -68.1190, 'nombre' => 'La Paz'],
        ],
        'CBB-SCZ' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -17.1000, 'lng' => -65.8000, 'nombre' => 'Villa Tunari'],
            ['lat' => -16.5000, 'lng' => -64.9000, 'nombre' => 'Montero'],
            ['lat' => -17.8140, 'lng' => -63.1710, 'nombre' => 'Santa Cruz'],
        ],
        'CBB-ORU' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -17.2500, 'lng' => -66.3500, 'nombre' => 'Oruro'],
        ],
        'CBB-PTS' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -17.2500, 'lng' => -66.3500, 'nombre' => 'Oruro'],
            ['lat' => -19.5836, 'lng' => -65.7556, 'nombre' => 'Potosi'],
        ],
        'CBB-SRE' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -18.2000, 'lng' => -65.8000, 'nombre' => 'Aiquile'],
            ['lat' => -19.0459, 'lng' => -65.2594, 'nombre' => 'Sucre'],
        ],
        'CBB-TJA' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -19.0459, 'lng' => -65.2594, 'nombre' => 'Sucre'],
            ['lat' => -19.6000, 'lng' => -64.9000, 'nombre' => 'Camargo'],
            ['lat' => -21.5355, 'lng' => -64.7299, 'nombre' => 'Tarija'],
        ],
        'CBB-TDD' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -16.9000, 'lng' => -65.5000, 'nombre' => 'Villa Tunari'],
            ['lat' => -15.8000, 'lng' => -64.8000, 'nombre' => 'San Ignacio de Moxos'],
            ['lat' => -14.8333, 'lng' => -64.9000, 'nombre' => 'Trinidad'],
        ],
        'CBB-CIJ' => [
            ['lat' => -17.3895, 'lng' => -66.1568, 'nombre' => 'Cochabamba'],
            ['lat' => -14.8333, 'lng' => -64.9000, 'nombre' => 'Trinidad'],
            ['lat' => -11.1000, 'lng' => -66.4000, 'nombre' => 'Riberalta'],
            ['lat' => -11.0267, 'lng' => -68.7347, 'nombre' => 'Cobija'],
        ],
    ];

    public function __construct(private readonly QrService $qrService)
    {
    }

    public function consultaPorQr(string $codigoQr)
    {
        $codigoViaje = $this->normalizarCodigoConsulta($codigoQr);

        try {
            $payload = $this->qrService->decodificarPayload($codigoViaje);
            $codigoViaje = $payload['codigo_viaje'] ?? $codigoViaje;
        } catch (\Throwable) {
        }

        $viaje = Viaje::with(['ruta', 'bus', 'conductor.usuario'])
            ->where('codigo_viaje', $codigoViaje)
            ->first();

        if (!$viaje) {
            return $this->error(
                'No se encontro un viaje con el codigo ingresado.',
                ['codigo' => ['El codigo de viaje no existe.']],
                'VIAJE_NO_ENCONTRADO',
                404
            );
        }

        return $this->success($viaje, 'Informacion del viaje.');
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

        $codigoBus = $this->codigoBus($viaje->bus);
        $consultaUrl = rtrim((string) config('app.url'), '/') . '/consulta?codigo=' . urlencode($codigoBus);

        $viaje->setAttribute('codigo_bus', $codigoBus);
        $viaje->setAttribute('bus_qr_url', $consultaUrl);
        $viaje->setAttribute('bus_qr_imagen', $this->qrService->generarQrTexto($consultaUrl));
        $viaje->setAttribute('croquis_asientos', $this->croquisAsientos($viaje));
        $viaje->setAttribute('pasajeros_alfabetico', $this->pasajerosAlfabetico($viaje));

        return $this->success($viaje, 'Manifiesto autorizado del viaje.');
    }

    public function boleto(string $codigo)
    {
        $boleto = Boleto::with(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento'])
            ->where('codigo_boleto', $codigo)
            ->firstOrFail();

        return $this->success([
            'valido' => in_array($boleto->estado, ['pagado', 'abordado'], true),
            'boleto' => $boleto,
        ], 'Verificacion de boleto.');
    }

    public function boletosPorCi(string $ci)
    {
        $pasajero = Pasajero::query()
            ->where('numero_ci', $ci)
            ->first();

        if (!$pasajero) {
            return $this->success([
                'pasajero' => null,
                'boletos' => [],
            ], 'No se encontraron boletos para este carnet.');
        }

        $boletos = Boleto::with([
            'viaje.ruta',
            'viaje.bus',
            'pasajero',
            'asiento',
            'adultoResponsable',
        ])
            ->where('pasajero_id', $pasajero->id)
            ->latest('fecha_emision')
            ->get();

        return $this->success([
            'pasajero' => $pasajero,
            'boletos' => $boletos,
        ], 'Boletos del pasajero.');
    }

    public function rastreo(string $codigo)
    {
        $boleto = Boleto::with(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento'])
            ->where('codigo_boleto', $codigo)
            ->first();

        $viaje = $boleto?->viaje;
        $bus = $viaje?->bus;
        $tipo = $boleto ? 'boleto' : null;

        if (!$viaje) {
            $viaje = Viaje::with(['ruta', 'bus'])
                ->where('codigo_viaje', $codigo)
                ->first();
            $bus = $viaje?->bus;
            $tipo = $viaje ? 'viaje' : $tipo;
        }

        if (!$bus) {
            $bus = Bus::query()
                ->where('placa', $codigo)
                ->orWhere('gps_imei', $codigo)
                ->firstOrFail();
            $viaje = $this->viajeActualPorBus($bus)?->loadMissing('ruta');
            $tipo = 'bus';
        }

        $ultima = UbicacionGps::query()
            ->where('bus_id', $bus->id)
            ->when($viaje, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('viaje_id', $viaje->id)
                ->orWhereNull('viaje_id')))
            ->latest('timestamp')
            ->first();

        $historial = UbicacionGps::query()
            ->where('bus_id', $bus->id)
            ->when($viaje, fn ($query) => $query->where(fn ($inner) => $inner
                ->where('viaje_id', $viaje->id)
                ->orWhereNull('viaje_id')))
            ->latest('timestamp')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        $ubicacion = $ultima ?: [
            'latitud' => -17.3895,
            'longitud' => -66.1568,
            'velocidad' => 0,
            'rumbo' => 0,
            'timestamp' => now(),
            'simulada' => true,
        ];

        $rutaCode = $viaje?->ruta?->codigo;

        // Usar waypoints de la simulacion si existen, sino los hardcodeados
        if ($viaje && $viaje->estado === 'en_ruta' && !empty($viaje->simulacion_waypoints)) {
            $waypoints = $viaje->simulacion_waypoints;
        } else {
            $waypoints = self::RUTAS_WAYPOINTS[$rutaCode] ?? self::RUTAS_WAYPOINTS['CBB-LPZ'];
        }

        // Calcular progreso basado en simulacion
        $progreso = 0;
        $velocidadAnterior = null;
        $signalLossActive = false;
        $etaMinutos = null;

        if ($viaje && $viaje->estado === 'en_ruta' && $viaje->simulacion_llamadas_totales > 0) {
            $llamada = (int) $viaje->simulacion_llamada_actual;
            $total = (int) $viaje->simulacion_llamadas_totales;
            $progreso = ($llamada / $total) * 100;

            // Buscar velocidad anterior al signal loss
            $ultimaConVelocidad = UbicacionGps::where('viaje_id', $viaje->id)
                ->where('signal_loss', false)
                ->where('velocidad', '>', 0)
                ->latest('timestamp')
                ->first();
            $velocidadAnterior = $ultimaConVelocidad?->velocidad;

            // Obtener ultima ubicacion para signal loss
            $ultimaGps = UbicacionGps::where('viaje_id', $viaje->id)
                ->latest('timestamp')
                ->first();
            $signalLossActive = $ultimaGps && $ultimaGps->signal_loss;

            // Calcular ETA
            $llamadasRestantes = $total - $llamada;
            $etaMinutos = max(1, (int) ceil($llamadasRestantes * 2 / 60));
            if ($signalLossActive) {
                $etaMinutos = (int) ceil($etaMinutos * 1.5);
            }
        }

        // Ajustar velocidad si hay seal perdida
        if ($signalLossActive && $velocidadAnterior) {
            $ubicacion['velocidad'] = (float) $velocidadAnterior;
            $ubicacion['velocidad_estimada'] = true;
            $ubicacion['signal_loss'] = true;
        } elseif ($ultima) {
            $ubicacion['velocidad'] = (float) $ultima->velocidad;
            $ubicacion['signal_loss'] = $signalLossActive;
        } else {
            $ubicacion['signal_loss'] = $signalLossActive;
        }

        // Obtener waypoints actuales para el mapa
        $totalWp = count($waypoints);
        $waypointIdx = 0;
        $waypointActualNombre = $waypoints[0]['nombre'] ?? '';
        $waypointSiguienteNombre = $waypoints[1]['nombre'] ?? '';

        if ($viaje && $viaje->estado === 'en_ruta' && $viaje->simulacion_llamadas_totales > 0) {
            $llamada = (int) $viaje->simulacion_llamada_actual;
            $total = (int) $viaje->simulacion_llamadas_totales;
            $progresoTmp = $llamada / $total;
            $waypointIdx = min((int) floor($progresoTmp * ($totalWp - 1)), $totalWp - 2);
            $waypointActualNombre = $waypoints[$waypointIdx]['nombre'] ?? '';
            $waypointSiguienteNombre = $waypoints[$waypointIdx + 1]['nombre'] ?? '';
        }

        return $this->success([
            'tipo_consulta' => $tipo,
            'codigo' => $codigo,
            'estado_operativo' => $this->estadoOperativo($viaje),
            'mensaje_estado' => $this->mensajeEstado($viaje),
            'eta_minutos' => $etaMinutos,
            'progreso' => round($progreso, 1),
            'boleto' => $boleto,
            'viaje' => $viaje?->loadMissing(['ruta', 'bus']),
            'bus' => $bus,
            'pasajero' => $boleto?->pasajero,
            'asiento' => $boleto?->asiento,
            'ubicacion' => $ubicacion,
            'historial' => $historial,
            'waypoints' => $waypoints,
            'waypoint_actual' => $waypointActualNombre,
            'waypoint_siguiente' => $waypointSiguienteNombre,
            'velocidad_estimada' => $velocidadAnterior,
            'signal_loss' => $signalLossActive,
        ], 'Rastreo del bus.');
    }

    private function estadoOperativo(?Viaje $viaje): string
    {
        if (!$viaje) {
            return 'Sin viaje activo';
        }

        return match ($viaje->estado) {
            'abordando' => 'En abordaje',
            'en_ruta' => 'En ruta',
            'completado' => 'Viaje finalizado',
            'cancelado' => 'Viaje cancelado',
            'en_venta' => 'Aun no partio',
            default => 'Sin viaje activo',
        };
    }

    private function mensajeEstado(?Viaje $viaje): string
    {
        if (!$viaje) {
            return 'No hay un viaje activo asociado a este bus en este momento.';
        }

        return match ($viaje->estado) {
            'abordando' => 'El bus esta en la terminal realizando abordaje.',
            'en_ruta' => 'El bus ya partio y se encuentra en ruta.',
            'completado' => 'El viaje llego a destino.',
            'cancelado' => 'Este viaje fue cancelado por administracion.',
            default => 'El bus aun no partio. Se muestra la terminal como punto actual.',
        };
    }

    private function etaMinutos(?Viaje $viaje, bool $signalLossActive = false, ?float $velocidadAnterior = null): ?int
    {
        if (!$viaje || in_array($viaje->estado, ['completado', 'cancelado'], true)) {
            return null;
        }

        if ($viaje->estado === 'en_ruta') {
            // Usar datos de simulacion si existen
            if ($viaje->simulacion_llamadas_totales > 0) {
                $llamada = (int) $viaje->simulacion_llamada_actual;
                $total = (int) $viaje->simulacion_llamadas_totales;
                $progreso = $llamada / $total;
                $distanciaKm = $this->estimarDistanciaRuta($viaje?->ruta?->codigo);

                // Si hay perdida de seal, usar la velocidad anterior para estimar
                $velocidadProm = $signalLossActive && $velocidadAnterior ? $velocidadAnterior : 70;
                $kmRestantes = $distanciaKm * (1 - $progreso);

                // Si hay seal perdida, estimar cuanto km no se registraron
                if ($signalLossActive && $velocidadAnterior) {
                    $ultimaGps = UbicacionGps::where('viaje_id', $viaje->id)
                        ->where('signal_loss', true)
                        ->latest('timestamp')
                        ->first();
                    if ($ultimaGps) {
                        $segundosPerdida = now()->diffInSeconds($ultimaGps->timestamp);
                        $kmNoRegistrados = ($velocidadAnterior / 3600) * $segundosPerdida;
                        $kmRestantes = max(0, $kmRestantes - $kmNoRegistrados);
                    }
                }

                return max(1, (int) ceil($kmRestantes / $velocidadProm * 60));
            }

            // Fallback: usar ubicaciones GPS
            $ultima = UbicacionGps::where('viaje_id', $viaje->id)
                ->latest('timestamp')
                ->first();
            if ($ultima && $ultima->velocidad > 0) {
                $totalUbicaciones = UbicacionGps::where('viaje_id', $viaje->id)->count();
                if ($totalUbicaciones > 2) {
                    $porcentajeAvanzado = min(95, ($totalUbicaciones / 60) * 100);
                    $distanciaKm = $this->estimarDistanciaRuta($viaje?->ruta?->codigo);
                    $velocidadProm = $ultima->velocidad ?: 70;
                    $kmRestantes = $distanciaKm * (100 - $porcentajeAvanzado) / 100;
                    return max(1, (int) ceil($kmRestantes / $velocidadProm * 60));
                }
            }
            return 20 + (($viaje->id * 13) % 80);
        }

        if (in_array($viaje->estado, ['en_venta', 'abordando'], true)) {
            return 40;
        }

        return null;
    }

    private function estimarDistanciaRuta(?string $codigoRuta): float
    {
        return match ($codigoRuta) {
            'CBB-LPZ' => 230,
            'CBB-SCZ' => 460,
            'CBB-ORU' => 120,
            'CBB-PTS' => 380,
            'CBB-SRE' => 290,
            'CBB-TJA' => 520,
            'CBB-TDD' => 400,
            'CBB-CIJ' => 650,
            default => 300,
        };
    }

    private function normalizarCodigoConsulta(string $codigo): string
    {
        $codigo = trim($codigo);
        $parts = parse_url($codigo);

        if (is_array($parts) && isset($parts['query'])) {
            parse_str((string) $parts['query'], $query);

            if (isset($query['codigo'])) {
                return (string) $query['codigo'];
            }
        }

        return $codigo;
    }

    private function viajeActualPorBus(Bus $bus): ?Viaje
    {
        return $bus->viajes()
            ->whereIn('estado', ['abordando', 'en_ruta', 'en_venta', 'programado'])
            ->orderByRaw("CASE estado WHEN 'en_ruta' THEN 1 WHEN 'abordando' THEN 2 WHEN 'en_venta' THEN 3 WHEN 'programado' THEN 4 ELSE 5 END")
            ->latest('fecha_salida')
            ->first();
    }

    private function codigoBus(?Bus $bus): string
    {
        if (!$bus) {
            return 'SIN-BUS';
        }

        return $bus->gps_imei ?: $bus->placa;
    }

    private function croquisAsientos(Viaje $viaje): array
    {
        $viaje->loadMissing('asientos');
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

        return $viaje->asientos->map(function (Asiento $asiento) use ($boletos, $menoresPorAdulto): array {
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
        })->values()->all();
    }

    private function pasajerosAlfabetico(Viaje $viaje): array
    {
        return $viaje->boletos
            ->filter(fn (Boleto $boleto) => in_array($boleto->estado, ['pendiente_verificacion', 'pagado', 'abordado'], true))
            ->sortBy([
                fn (Boleto $boleto) => mb_strtolower((string) $boleto->pasajero?->apellidos),
                fn (Boleto $boleto) => mb_strtolower((string) $boleto->pasajero?->nombres),
            ])
            ->values()
            ->map(fn (Boleto $boleto): array => [
                'boleto_id' => $boleto->id,
                'codigo_boleto' => $boleto->codigo_boleto,
                'nombres' => $boleto->pasajero?->nombres,
                'apellidos' => $boleto->pasajero?->apellidos,
                'nombre_completo' => trim((string) ($boleto->pasajero?->nombres . ' ' . $boleto->pasajero?->apellidos)),
                'ci' => $boleto->pasajero?->numero_ci,
                'asiento' => $boleto->asiento?->numero,
                'estado' => $boleto->estado,
                'es_menor' => (bool) $boleto->es_menor,
                'adulto_responsable' => $boleto->adultoResponsable
                    ? trim($boleto->adultoResponsable->nombres . ' ' . $boleto->adultoResponsable->apellidos)
                    : null,
            ])
            ->all();
    }
}
