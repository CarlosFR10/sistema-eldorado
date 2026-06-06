<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Viaje;
use App\Models\Ruta;
use App\Models\Bus;
use Carbon\Carbon;

class CrearViajesProximos20Dias extends Command
{
    protected $signature = 'viajes:crear-proximos-20-dias';
    protected $description = 'Create viajes for the next 20 days';

    public function handle()
    {
        $startDate = Carbon::parse('2026-06-07'); // Start from June 7 (we already have June 5-6)
        $endDate = $startDate->copy()->addDays(19); // 20 days total: June 7-26
        
        $this->info("Creating viajes from {$startDate->toDateString()} to {$endDate->toDateString()}");
        
        // Routes from Cochabamba (excludes return routes)
        $rutasCbb = [1, 3, 5, 7, 9, 11, 13, 15]; // CBB-LPZ, CBB-SCZ, CBB-ORU, CBB-PTS, CBB-SRE, CBB-TJA, CBB-TDD, CBB-CIJ
        $buses = Bus::where('activo', 1)->get();
        $conductores = [1, 2];
        $vendedorId = 3;
        
        $viajesCount = 0;
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            
            // Create 12 departures per day
            $horarios = [
                ['hora' => '06:00', 'ruta_idx' => 0, 'bus_idx' => 0],
                ['hora' => '07:00', 'ruta_idx' => 1, 'bus_idx' => 1],
                ['hora' => '08:00', 'ruta_idx' => 2, 'bus_idx' => 2],
                ['hora' => '09:00', 'ruta_idx' => 3, 'bus_idx' => 3],
                ['hora' => '10:00', 'ruta_idx' => 4, 'bus_idx' => 4],
                ['hora' => '06:30', 'ruta_idx' => 5, 'bus_idx' => 5],
                ['hora' => '07:30', 'ruta_idx' => 6, 'bus_idx' => 6],
                ['hora' => '08:30', 'ruta_idx' => 7, 'bus_idx' => 0],
                ['hora' => '14:00', 'ruta_idx' => 0, 'bus_idx' => 1],
                ['hora' => '15:00', 'ruta_idx' => 1, 'bus_idx' => 2],
                ['hora' => '16:00', 'ruta_idx' => 2, 'bus_idx' => 3],
                ['hora' => '17:00', 'ruta_idx' => 3, 'bus_idx' => 4],
            ];
            
            foreach ($horarios as $idx => $h) {
                $rutaId = $rutasCbb[$h['ruta_idx']];
                $bus = $buses[$h['bus_idx']];
                $conductorId = $conductores[$idx % 2];
                
                $ruta = Ruta::find($rutaId);
                $fechaSalida = Carbon::parse("{$dateStr} {$h['hora']}");
                $fechaLlegada = $fechaSalida->copy()->addHours((float)$ruta->duracion_horas);
                
                // Check for conflicts
                $conflicto = Viaje::where('bus_id', $bus->id)
                    ->whereIn('estado', ['en_venta', 'abordando', 'en_ruta', 'programado'])
                    ->where('fecha_salida', '>=', $fechaSalida->copy()->subMinutes(150))
                    ->where('fecha_salida', '<=', $fechaSalida->copy()->addMinutes(10))
                    ->exists();
                
                if ($conflicto) {
                    $this->warn("Skipping {$dateStr} {$h['hora']} - conflict for bus {$bus->placa}");
                    continue;
                }
                
                $codigo = "VJ-" . $currentDate->format('Ymd') . "-" . str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
                
                Viaje::create([
                    'codigo_viaje' => $codigo,
                    'ruta_id' => $rutaId,
                    'bus_id' => $bus->id,
                    'conductor_id' => $conductorId,
                    'vendedor_id' => $vendedorId,
                    'fecha_salida' => $fechaSalida,
                    'fecha_llegada_est' => $fechaLlegada,
                    'precio_final' => $ruta->precio_base,
                    'estado' => 'en_venta',
                    'observaciones' => 'Salida generada automaticamente',
                ]);
                $viajesCount++;
            }
            
            $currentDate->addDay();
        }
        
        $this->info("Created {$viajesCount} viajes");
        
        // Now generate asientos for new viajes
        $newViajes = Viaje::where('fecha_salida', '>=', $startDate)->with('bus')->get();
        $this->info("Generating asientos for {$newViajes->count()} viajes...");
        
        foreach ($newViajes as $viaje) {
            $bus = $viaje->bus;
            $config = is_string($bus->config_asientos) ? json_decode($bus->config_asientos, true) : ($bus->config_asientos ?: []);
            
            $columnas = max((int)($config['columnas'] ?? 4), 1);
            $pasillo = (int)($config['pasillo'] ?? 2);
            $especiales = $config['especiales'] ?? [];
            
            for ($numero = 1; $numero <= $bus->capacidad; $numero++) {
                $fila = (int)ceil($numero / $columnas);
                $colEnOriginal = (($numero - 1) % $columnas) + 1;
                $columna = $colEnOriginal >= $pasillo ? $colEnOriginal + 1 : $colEnOriginal;
                $piso = $bus->tipo_bus === 'doble_piso' && $numero > ($bus->capacidad / 2) ? 2 : 1;
                $tipo = in_array($numero, $especiales, true) ? 'preferencial' : 'normal';
                
                \App\Models\Asiento::create([
                    'viaje_id' => $viaje->id,
                    'numero' => $numero,
                    'fila' => $fila,
                    'columna' => $columna,
                    'piso' => $piso,
                    'tipo' => $tipo,
                    'estado' => 'disponible',
                ]);
            }
        }
        
        $this->info('Done!');
        return 0;
    }
}