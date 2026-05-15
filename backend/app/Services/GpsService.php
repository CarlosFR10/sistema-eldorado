<?php

namespace App\Services;

use App\Events\AlertaVelocidadExcesiva;
use App\Events\BusUbicacionActualizada;
use App\Models\AuditLog;
use App\Models\Bus;
use App\Models\UbicacionGps;
use App\Models\Viaje;

class GpsService
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

    private array $simulacionesActivas = [];

    public function registrarUbicacion(
        int $busId,
        ?int $viajeId,
        float $latitud,
        float $longitud,
        ?float $velocidad,
        ?float $rumbo,
        ?float $altitud,
        ?float $precision
    ): UbicacionGps {
        $ubicacion = UbicacionGps::create([
            'bus_id' => $busId,
            'viaje_id' => $viajeId,
            'latitud' => $latitud,
            'longitud' => $longitud,
            'velocidad' => $velocidad,
            'rumbo' => $rumbo,
            'altitud' => $altitud,
            'precision_m' => $precision,
            'timestamp' => now(),
        ]);
        $ubicacion->load(['bus', 'viaje.conductor.usuario']);

        event(new BusUbicacionActualizada(
            (int) $busId,
            (string) ($ubicacion->bus?->placa ?? ''),
            $latitud,
            $longitud,
            $velocidad,
            $rumbo,
            $viajeId
        ));

        if ($velocidad !== null && $velocidad > 100) {
            AuditLog::create([
                'usuario_id' => null,
                'accion' => 'alerta.velocidad_excesiva',
                'tabla_afectada' => 'ubicaciones_gps',
                'registro_id' => $ubicacion->id,
                'datos_antes' => null,
                'datos_despues' => [
                    'bus_id' => $busId,
                    'viaje_id' => $viajeId,
                    'velocidad' => $velocidad,
                    'latitud' => $latitud,
                    'longitud' => $longitud,
                ],
                'ip' => request()->ip() ?: '127.0.0.1',
                'user_agent' => request()->userAgent(),
            ]);
            event(new AlertaVelocidadExcesiva(
                (int) $busId,
                (string) ($ubicacion->bus?->placa ?? ''),
                $velocidad,
                $ubicacion->viaje?->conductor?->usuario?->nombre
            ));
        }

        return $ubicacion->fresh(['bus', 'viaje.ruta']);
    }

    public function busesActivos()
    {
        return Bus::query()
            ->where('activo', true)
            ->with(['viajes' => fn ($query) => $query->whereIn('estado', ['en_ruta', 'abordando'])->latest('fecha_salida')])
            ->get()
            ->map(function (Bus $bus): array {
                $ultima = $bus->ubicacionesGps()->orderBy('timestamp', 'desc')->first();
                $viaje = $bus->viajes->first();
                if ($viaje) {
                    $viaje->load(['ruta', 'conductor.usuario']);
                }

                return [
                    'bus' => $bus,
                    'viaje' => $viaje,
                    'ubicacion' => $ultima,
                ];
            });
    }

    public function historialViaje(int $viajeId)
    {
        return UbicacionGps::where('viaje_id', $viajeId)
            ->with('bus')
            ->orderBy('timestamp')
            ->get();
    }

    public function historialBus(int $busId)
    {
        return UbicacionGps::where('bus_id', $busId)
            ->with('viaje.ruta')
            ->orderBy('timestamp')
            ->get();
    }

    public function simular(int $busId, ?int $viajeId = null): UbicacionGps
    {
        $puntos = [
            [-17.3895, -66.1568],
            [-17.1978, -66.3196],
            [-17.0031, -66.5139],
            [-16.8139, -66.8058],
            [-16.5000, -68.1500],
        ];

        $punto = $puntos[array_rand($puntos)];

        return $this->registrarUbicacion(
            $busId,
            $viajeId,
            $punto[0] + random_int(-50, 50) / 10000,
            $punto[1] + random_int(-50, 50) / 10000,
            random_int(45, 108),
            random_int(0, 359),
            random_int(2500, 4100),
            random_int(5, 30)
        );
    }

    public function iniciarSimulacion(int $viajeId): array
    {
        $viaje = Viaje::with(['ruta', 'bus'])->findOrFail($viajeId);

        if ($viaje->estado !== 'en_venta' && $viaje->estado !== 'abordando') {
            throw new \RuntimeException("El viaje no puede iniciar. Estado actual: {$viaje->estado}");
        }

        $codigoRuta = $viaje->ruta->codigo;
        $waypoints = self::RUTAS_WAYPOINTS[$codigoRuta] ?? self::RUTAS_WAYPOINTS['CBB-LPZ'];

        $this->simulacionesActivas[$viajeId] = [
            'viaje_id' => $viajeId,
            'bus_id' => $viaje->bus_id,
            'waypoints' => $waypoints,
            'waypoint_actual' => 0,
            'progreso' => 0.0,
            'velocidad' => 70.0,
            'signal_loss' => false,
            'signal_loss_until' => null,
            'inicio' => now(),
            'duracion_minutos' => 1,
            'ultima_actualizacion' => now(),
        ];

        $viaje->update(['estado' => 'en_ruta']);

        $inicio = $waypoints[0];
        return $this->registrarUbicacion(
            $viaje->bus_id,
            $viajeId,
            $inicio['lat'],
            $inicio['lng'],
            0.0,
            0.0,
            3400.0,
            10.0
        );
    }

    public function avanzarSimulacion(int $viajeId): array
    {
        if (!isset($this->simulacionesActivas[$viajeId])) {
            throw new \RuntimeException('No hay simulacion activa para este viaje.');
        }

        $sim = &$this->simulacionesActivas[$viajeId];
        $viaje = Viaje::with('ruta')->find($viajeId);

        if (!$viaje || $viaje->estado !== 'en_ruta') {
            unset($this->simulacionesActivas[$viajeId]);
            return ['fin' => true, 'mensaje' => 'Viaje finalizado o no existe.'];
        }

        $elapsed = now()->diffInSeconds($sim['inicio']);
        $totalSeconds = $sim['duracion_minutos'] * 60;
        $progreso = min(1.0, $elapsed / $totalSeconds);
        $sim['progreso'] = $progreso;

        $sePerdioSenal = random_int(1, 8) === 1;
        if ($sePerdioSenal && !$sim['signal_loss']) {
            $sim['signal_loss'] = true;
            $sim['signal_loss_until'] = now()->addSeconds(random_int(5, 15));
        }

        if ($sim['signal_loss'] && now()->lt($sim['signal_loss_until'])) {
            return [
                'signal_loss' => true,
                'latitud' => $sim['waypoints'][$sim['waypoint_actual']]['lat'],
                'longitud' => $sim['waypoints'][$sim['waypoint_actual']]['lng'],
                'velocidad' => 0,
                'progreso' => $progreso * 100,
                'eta_minutos' => ceil(($totalSeconds - $elapsed) / 60),
                'mensaje' => 'Sin seal GPS. Reintentando...',
            ];
        }

        if ($sim['signal_loss'] && now()->gte($sim['signal_loss_until'])) {
            $sim['signal_loss'] = false;
            $sim['signal_loss_until'] = null;
        }

        $waypointIdx = (int) floor($progreso * (count($sim['waypoints']) - 1));
        $waypointIdx = min($waypointIdx, count($sim['waypoints']) - 2);
        $sim['waypoint_actual'] = $waypointIdx;

        $nextIdx = min($waypointIdx + 1, count($sim['waypoints']) - 1);
        $actual = $sim['waypoints'][$waypointIdx];
        $siguiente = $sim['waypoints'][$nextIdx];

        $localProgress = ($progreso * (count($sim['waypoints']) - 1)) - $waypointIdx;
        $lat = $actual['lat'] + ($siguiente['lat'] - $actual['lat']) * $localProgress;
        $lng = $actual['lng'] + ($siguiente['lng'] - $actual['lng']) * $localProgress;

        $velocidadBase = random_int(60, 90);
        $velocidad = $sim['signal_loss'] ? 0 : $velocidadBase;
        $rumbo = $this->calcularRumbo($actual['lat'], $actual['lng'], $siguiente['lat'], $siguiente['lng']);

        $this->registrarUbicacion(
            $sim['bus_id'],
            $viajeId,
            $lat,
            $lng,
            $velocidad,
            $rumbo,
            3400.0,
            10.0
        );

        $remainingSeconds = $totalSeconds - $elapsed;
        $etaMinutos = max(1, (int) ceil($remainingSeconds / 60));

        if ($progreso >= 1.0) {
            $destino = $sim['waypoints'][count($sim['waypoints']) - 1];
            $this->registrarUbicacion(
                $sim['bus_id'],
                $viajeId,
                $destino['lat'],
                $destino['lng'],
                0.0,
                0.0,
                3400.0,
                10.0
            );
            $viaje->update([
                'estado' => 'completado',
                'fecha_llegada_real' => now(),
            ]);
            unset($this->simulacionesActivas[$viajeId]);

            return [
                'fin' => true,
                'mensaje' => 'Viaje completado',
                'progreso' => 100,
            ];
        }

        return [
            'fin' => false,
            'signal_loss' => false,
            'latitud' => $lat,
            'longitud' => $lng,
            'velocidad' => $velocidad,
            'rumbo' => $rumbo,
            'progreso' => $progreso * 100,
            'eta_minutos' => $etaMinutos,
            'waypoint_actual' => $actual['nombre'] ?? '',
            'waypoint_siguiente' => $siguiente['nombre'] ?? '',
            'mensaje' => "En ruta hacia {$siguiente['nombre']}",
        ];
    }

    public function estadoSimulacion(int $viajeId): ?array
    {
        return $this->simulacionesActivas[$viajeId] ?? null;
    }

    private function calcularRumbo(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLng = deg2rad($lng2 - $lng1);
        $dLat = deg2rad($lat2 - $lat1);
        $y = sin($dLng) * cos(deg2rad($lat2));
        $x = cos(deg2rad($lat1)) * sin(deg2rad($lat2)) - sin(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos($dLng);
        $rumbo = rad2deg(atan2($y, $x));
        return ($rumbo + 360) % 360;
    }
}
