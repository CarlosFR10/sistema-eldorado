<?php

namespace App\Console\Commands;

use App\Models\Viaje;
use App\Models\UbicacionGps;
use App\Services\GpsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AvanzarSimulacionesGps extends Command
{
    protected $signature = 'gps:avanzar-simulaciones {--daemon}';
    protected $description = 'Advance GPS simulations in background';

    private GpsService $gpsService;
    private bool $running = true;

    public function handle()
    {
        $this->gpsService = new GpsService();
        $isDaemon = $this->option('daemon');

        if ($isDaemon) {
            $this->info('GPS Simulation Daemon started. Press Ctrl+C to stop.');
            pcntl_async_signals(true);
            pcntl_signal(SIGTERM, fn() => $this->running = false);
            pcntl_signal(SIGINT, fn() => $this->running = false);

            while ($this->running) {
                $this->avanzarTodasSimulaciones();
                sleep(5);
            }
            $this->info('GPS Simulation Daemon stopped.');
        } else {
            $this->avanzarTodasSimulaciones();
        }

        return 0;
    }

    private function avanzarTodasSimulaciones()
    {
        $viajes = Viaje::where('estado', 'en_ruta')
            ->whereNotNull('simulacion_inicio')
            ->where('simulacion_progreso', '<', 100)
            ->get();

        if ($viajes->isEmpty()) {
            $this->info('No active simulations found.');
            return;
        }

        $this->info("Processing {$viajes->count()} active simulations...");
        foreach ($viajes as $viaje) {
            $this->procesarSimulacion($viaje);
        }
    }

    private function procesarSimulacion(Viaje $viaje)
    {
        try {
            $viaje->load('ruta');
            $inicioLocalStr = $viaje->getRawOriginal('simulacion_inicio');
            $inicio = \Carbon\Carbon::parse($inicioLocalStr . ' +00:00');
            $now = \Carbon\Carbon::now('UTC');

            if ($inicio->gt($now)) {
                $this->warn("Viaje {$viaje->id}: simulacion_inicio is in the future ({$inicio->toDateTimeString()}), fixing...");
                $viaje->update(['simulacion_inicio' => $now]);
                $inicio = $now;
            }

            $duracionMinutos = (int) ($viaje->ruta->duracion_horas * 60);
            $elapsed = (int) $inicio->diffInSeconds($now);
            $totalSeconds = $duracionMinutos * 60;
            $progreso = min(1.0, $elapsed / $totalSeconds) * 100;

            if ($progreso >= 100) {
                $viaje->update([
                    'estado' => 'completado',
                    'fecha_llegada_real' => now(),
                    'simulacion_progreso' => 100,
                ]);
                $this->info("Viaje {$viaje->id} completado.");
                return;
            }

            $waypoints = is_string($viaje->simulacion_waypoints) 
                ? json_decode($viaje->simulacion_waypoints, true) 
                : ($viaje->simulacion_waypoints ?? []);

            if (empty($waypoints) || count($waypoints) < 2) {
                $this->warn("Viaje {$viaje->id}: no waypoints or less than 2, skipping.");
                return;
            }

            $numWaypoints = count($waypoints) - 1;
            $waypointIdx = (int) floor(($progreso / 100) * $numWaypoints);
            $waypointIdx = max(0, min($waypointIdx, $numWaypoints - 1));
            $nextIdx = min($waypointIdx + 1, $numWaypoints);

            $actual = $waypoints[$waypointIdx];
            $siguiente = $waypoints[$nextIdx];

            $localProgress = (($progreso / 100) * (count($waypoints) - 1)) - $waypointIdx;
            $lat = $actual['lat'] + ($siguiente['lat'] - $actual['lat']) * $localProgress;
            $lng = $actual['lng'] + ($siguiente['lng'] - $actual['lng']) * $localProgress;

            $signalLoss = random_int(1, 20) === 1;
            $velocidad = $signalLoss ? 0 : random_int(60, 90);

            UbicacionGps::create([
                'bus_id' => $viaje->bus_id,
                'viaje_id' => $viaje->id,
                'latitud' => $lat,
                'longitud' => $lng,
                'velocidad' => $velocidad,
                'rumbo' => $this->calcularRumbo($actual['lat'], $actual['lng'], $siguiente['lat'], $siguiente['lng']),
                'altitud' => 3400.0,
                'precision' => 10.0,
            ]);

            $viaje->update(['simulacion_progreso' => $progreso]);
            $this->line("Viaje {$viaje->id}: {$progreso}%");

        } catch (\Exception $e) {
            $this->error("Error procesando viaje {$viaje->id}: " . $e->getMessage());
        }
    }

    private function calcularRumbo(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLng = deg2rad($lng2 - $lng1);
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);

        $x = sin($dLng) * cos($lat2Rad);
        $y = cos($lat1Rad) * sin($lat2Rad) - sin($lat1Rad) * cos($lat2Rad) * cos($dLng);

        $rumbo = rad2deg(atan2($x, $y));
        return ($rumbo + 360) % 360;
    }
}