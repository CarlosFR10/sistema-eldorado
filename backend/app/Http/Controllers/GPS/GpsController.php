<?php

namespace App\Http\Controllers\GPS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GpsController extends Controller
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

    private const DURACION_SIMULACION_MINUTOS = 3;

    public function recibirUbicacion(Request $request)
    {
        $data = $request->validate([
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'viaje_id' => ['nullable', 'integer', 'exists:viajes,id'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'velocidad' => ['nullable', 'numeric', 'min:0'],
            'rumbo' => ['nullable', 'numeric', 'between:0,359'],
            'altitud' => ['nullable', 'numeric'],
            'precision_m' => ['nullable', 'numeric', 'min:0'],
        ]);

        $ubicacion = \App\Models\UbicacionGps::create([
            'bus_id' => (int) $data['bus_id'],
            'viaje_id' => $data['viaje_id'] ?? null,
            'latitud' => (float) $data['latitud'],
            'longitud' => (float) $data['longitud'],
            'velocidad' => isset($data['velocidad']) ? (float) $data['velocidad'] : null,
            'rumbo' => isset($data['rumbo']) ? (float) $data['rumbo'] : null,
            'altitud' => isset($data['altitud']) ? (float) $data['altitud'] : null,
            'precision_m' => isset($data['precision_m']) ? (float) $data['precision_m'] : null,
            'timestamp' => now(),
        ]);

        return $this->success($ubicacion->fresh(['bus', 'viaje.ruta']), 'Ubicacion GPS recibida.', 201);
    }

    public function busesActivos()
    {
        $buses = \App\Models\Bus::where('activo', true)->get();

        return $this->success($buses->map(function ($bus) {
            $viaje = $bus->viajes()->whereIn('estado', ['en_ruta', 'abordando'])->latest('fecha_salida')->first();
            $ultima = $bus->ubicacionesGps()->orderBy('timestamp', 'desc')->first();

            return [
                'bus' => $bus,
                'viaje' => $viaje,
                'ubicacion' => $ultima,
            ];
        }), 'Buses activos en ruta.');
    }

    public function ruta(int $id)
    {
        $historial = \App\Models\UbicacionGps::where('bus_id', $id)->with('viaje.ruta')->orderBy('timestamp')->get();
        return $this->success($historial, 'Historial GPS del bus.');
    }

    public function timeline(int $id)
    {
        $historial = \App\Models\UbicacionGps::where('viaje_id', $id)->with('bus')->orderBy('timestamp')->get();
        return $this->success($historial, 'Timeline GPS del viaje.');
    }

    public function simular(Request $request)
    {
        $data = $request->validate([
            'bus_id' => ['required', 'integer', 'exists:buses,id'],
            'viaje_id' => ['nullable', 'integer', 'exists:viajes,id'],
        ]);

        $busId = (int) $data['bus_id'];
        $viajeId = $data['viaje_id'] ?? null;

        $puntos = [
            [-17.3895, -66.1568],
            [-17.1978, -66.3196],
            [-17.0031, -66.5139],
            [-16.8139, -66.8058],
            [-16.5000, -68.1500],
        ];
        $punto = $puntos[array_rand($puntos)];

        $ubicacion = \App\Models\UbicacionGps::create([
            'bus_id' => $busId,
            'viaje_id' => $viajeId,
            'latitud' => $punto[0] + random_int(-50, 50) / 10000,
            'longitud' => $punto[1] + random_int(-50, 50) / 10000,
            'velocidad' => random_int(45, 108),
            'rumbo' => random_int(0, 359),
            'altitud' => random_int(2500, 4100),
            'precision_m' => random_int(5, 30),
            'timestamp' => now(),
        ]);

        return $this->success($ubicacion, 'Ubicacion simulada generada.', 201);
    }

    public function iniciarViaje(int $id)
    {
        $viaje = \App\Models\Viaje::with(['ruta', 'bus'])->findOrFail($id);

        if ($viaje->estado !== 'en_venta' && $viaje->estado !== 'abordando') {
            return $this->error("El viaje no puede iniciar. Estado actual: {$viaje->estado}", 422);
        }

        $codigoRuta = $viaje->ruta->codigo ?? 'CBB-LPZ';

        $rutaPuntos = \App\Models\RutaPunto::whereHas('ruta', function ($q) use ($codigoRuta) {
            $q->where('codigo', $codigoRuta);
        })
            ->orderBy('orden')
            ->get();

        if ($rutaPuntos->count() > 0) {
            $waypoints = $rutaPuntos->map(fn ($p) => [
                'lat' => (float) $p->latitud,
                'lng' => (float) $p->longitud,
                'nombre' => $p->nombre,
            ])->all();
        } else {
            $waypoints = self::RUTAS_WAYPOINTS[$codigoRuta] ?? self::RUTAS_WAYPOINTS['CBB-LPZ'];
        }

        $inicio = $waypoints[0];
        $destino = $waypoints[count($waypoints) - 1];
        $distanciaKm = $this->calcularDistancia($inicio['lat'], $inicio['lng'], $destino['lat'], $destino['lng']);

        $llamadasTotales = 30;

        $viaje->update([
            'estado' => 'en_ruta',
            'simulacion_llamada_actual' => 0,
            'simulacion_llamadas_totales' => $llamadasTotales,
            'simulacion_progreso' => 0.0,
            'simulacion_waypoints' => $waypoints,
            'simulacion_inicio' => now(),
        ]);

        $ubicacion = \App\Models\UbicacionGps::create([
            'bus_id' => $viaje->bus_id,
            'viaje_id' => $viaje->id,
            'latitud' => $inicio['lat'],
            'longitud' => $inicio['lng'],
            'velocidad' => 0,
            'rumbo' => 0,
            'altitud' => 3400,
            'precision_m' => 10,
            'timestamp' => now(),
        ]);

        return $this->success([
            'ubicacion' => $ubicacion,
            'waypoints' => $waypoints,
            'llamadas_totales' => $llamadasTotales,
            'distancia_km' => round($distanciaKm, 1),
            'duracion_minutos' => self::DURACION_SIMULACION_MINUTOS,
            'intervalo_segundos' => 2,
            'mensaje' => 'Simulacion iniciada. Duracion: 1 minuto.',
        ], 'Simulacion del viaje iniciada.', 201);
    }

    public function avanzarSimulacion(int $id)
    {
        $viaje = \App\Models\Viaje::find($id);

        if (!$viaje) {
            return $this->error('Viaje no encontrado.', [], 'NOT_FOUND', 404);
        }

        if ($viaje->estado !== 'en_ruta') {
            return $this->error("El viaje no esta en ruta. Estado: {$viaje->estado}.", [], 'INVALID_STATE', 422);
        }

        if (!$viaje->simulacion_inicio || !$viaje->simulacion_waypoints) {
            return $this->error('No hay simulacion activa. Llame a iniciarViaje primero.', [], 'NO_SIMULATION', 422);
        }

        $llamada = (int) $viaje->simulacion_llamada_actual;
        $total = (int) $viaje->simulacion_llamadas_totales;
        $waypoints = $viaje->simulacion_waypoints;

        if ($llamada >= $total) {
            $destino = $waypoints[count($waypoints) - 1];
            \App\Models\UbicacionGps::create([
                'bus_id' => $viaje->bus_id,
                'viaje_id' => $id,
                'latitud' => $destino['lat'],
                'longitud' => $destino['lng'],
                'velocidad' => 0,
                'rumbo' => 0,
                'altitud' => 3400,
                'precision_m' => 10,
                'signal_loss' => false,
                'timestamp' => now(),
            ]);
            $viaje->update([
                'estado' => 'completado',
                'fecha_llegada_real' => now(),
                'simulacion_progreso' => 100,
            ]);

            return $this->success([
                'fin' => true,
                'mensaje' => 'Viaje completado!',
                'progreso' => 100,
                'latitud' => $destino['lat'],
                'longitud' => $destino['lng'],
                'waypoints' => $waypoints,
            ], 'Viaje completado.');
        }

        $llamada++;
        $progreso = min(1.0, $llamada / $total);

        $sePerdioSenal = random_int(1, 8) === 1;
        $signalLoss = $sePerdioSenal && $llamada > 1 && random_int(0, 1) === 0;

        $totalWp = count($waypoints);
        $idx = (int) floor($progreso * ($totalWp - 1));
        $idx = min($idx, $totalWp - 1);
        $nextIdx = min($idx + 1, $totalWp - 1);
        $actual = $waypoints[$idx];
        $siguiente = $waypoints[$nextIdx];

        $localProgress = ($progreso * ($totalWp - 1)) - $idx;
        $lat = $actual['lat'] + ($siguiente['lat'] - $actual['lat']) * $localProgress;
        $lng = $actual['lng'] + ($siguiente['lng'] - $actual['lng']) * $localProgress;

        $velocidad = $signalLoss ? 0 : random_int(60, 90);
        $rumbo = $this->calcularRumbo($actual['lat'], $actual['lng'], $siguiente['lat'], $siguiente['lng']);

        \App\Models\UbicacionGps::create([
            'bus_id' => $viaje->bus_id,
            'viaje_id' => $id,
            'latitud' => $lat,
            'longitud' => $lng,
            'velocidad' => $velocidad,
            'rumbo' => $rumbo,
            'altitud' => 3400,
            'precision_m' => 10,
            'signal_loss' => $signalLoss,
            'timestamp' => now(),
        ]);

        $viaje->update([
            'simulacion_llamada_actual' => $llamada,
            'simulacion_progreso' => $progreso * 100,
        ]);

        $etaMinutos = max(1, (int) ceil(($total - $llamada) * 2 / 60));

        return $this->success([
            'fin' => false,
            'signal_loss' => $signalLoss,
            'latitud' => $lat,
            'longitud' => $lng,
            'velocidad' => $velocidad,
            'rumbo' => $rumbo,
            'progreso' => $progreso * 100,
            'eta_minutos' => $etaMinutos,
            'waypoint_actual' => $actual['nombre'] ?? '',
            'waypoint_siguiente' => $siguiente['nombre'] ?? '',
            'mensaje' => "En ruta... {$llamada}/{$total}",
            'llamada_actual' => $llamada,
            'llamadas_totales' => $total,
            'waypoints' => $waypoints,
        ], 'Avance registrado.');
    }

    public function estadoSimulacion(int $id)
    {
        $viaje = \App\Models\Viaje::with('ruta')->find($id);

        if (!$viaje) {
            return $this->success(null, 'Viaje no encontrado.');
        }

        if ($viaje->estado === 'en_ruta' && $viaje->simulacion_inicio) {
            $llamada = (int) $viaje->simulacion_llamada_actual;
            $total = (int) $viaje->simulacion_llamadas_totales;
            $waypoints = $viaje->simulacion_waypoints ?: [];

            $elapsed = now()->diffInSeconds($viaje->simulacion_inicio);
            $totalSeconds = self::DURACION_SIMULACION_MINUTOS * 60;
            $progreso = $total > 0 ? min(1.0, $llamada / $total) : min(1.0, $elapsed / $totalSeconds);

            $ultima = \App\Models\UbicacionGps::where('viaje_id', $id)
                ->where('velocidad', '>', 0)
                ->latest('timestamp')
                ->first();

            if (!$ultima) {
                $ultima = \App\Models\UbicacionGps::where('viaje_id', $id)
                    ->latest('timestamp')
                    ->first();
            }

            $signalLossActive = $ultima && $ultima->signal_loss;

            return $this->success([
                'viaje_id' => $id,
                'bus_id' => $viaje->bus_id,
                'estado' => 'en_ruta',
                'signal_loss' => $signalLossActive,
                'progreso' => $progreso * 100,
                'eta_minutos' => max(0, (int) ceil(($totalSeconds - $elapsed) / 60)),
                'waypoints' => $waypoints,
                'waypoint_actual' => $waypoints[0]['nombre'] ?? '',
                'velocidad' => $signalLossActive ? 0 : ((int) ($ultima?->velocidad ?? random_int(60, 90))),
                'latitud' => $ultima?->latitud,
                'longitud' => $ultima?->longitud,
                'llamada_actual' => $llamada,
                'llamadas_totales' => $total,
            ], 'Estado de la simulacion.');
        }

        if ($viaje->estado === 'completado') {
            $waypoints = $viaje->simulacion_waypoints ?: [];
            $destino = count($waypoints) > 0 ? $waypoints[count($waypoints) - 1] : null;

            return $this->success([
                'viaje_id' => $id,
                'bus_id' => $viaje->bus_id,
                'estado' => 'completado',
                'signal_loss' => false,
                'progreso' => 100,
                'eta_minutos' => 0,
                'waypoints' => $waypoints,
                'latitud' => $destino['lat'] ?? null,
                'longitud' => $destino['lng'] ?? null,
            ], 'Viaje completado.');
        }

        $codigoRuta = $viaje->ruta?->codigo ?? 'CBB-LPZ';
        $waypoints = self::RUTAS_WAYPOINTS[$codigoRuta] ?? self::RUTAS_WAYPOINTS['CBB-LPZ'];

        return $this->success([
            'viaje_id' => $id,
            'estado' => $viaje->estado,
            'signal_loss' => false,
            'progreso' => 0,
            'waypoints' => $waypoints,
            'waypoint_actual' => '',
            'velocidad' => 0,
            'latitud' => null,
            'longitud' => null,
            'eta_minutos' => null,
        ], 'Estado de la simulacion.');
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

    private function calcularDistancia(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }
}