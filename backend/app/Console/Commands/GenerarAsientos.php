<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Viaje;
use App\Models\Bus;
use App\Models\Asiento;

class GenerarAsientos extends Command
{
    protected $signature = 'viajes:generar-asientos {--from=228}';
    protected $description = 'Generate asientos for viajes';

    public function handle()
    {
        $fromId = (int) $this->option('from');
        $viajes = Viaje::where('id', '>=', $fromId)->with('bus')->get();
        
        $this->info("Found {$viajes->count()} viajes to process");
        
        foreach ($viajes as $viaje) {
            $bus = $viaje->bus;
            $config = is_string($bus->config_asientos) ? json_decode($bus->config_asientos, true) : ($bus->config_asientos ?: []);
            
            $columnas = max((int)($config['columnas'] ?? 4), 1);
            $pasillo = (int)($config['pasillo'] ?? 2);
            $especiales = $config['especiales'] ?? [];
            
            $count = 0;
            for ($numero = 1; $numero <= $bus->capacidad; $numero++) {
                $fila = (int)ceil($numero / $columnas);
                $colEnOriginal = (($numero - 1) % $columnas) + 1;
                $columna = $colEnOriginal >= $pasillo ? $colEnOriginal + 1 : $colEnOriginal;
                $piso = $bus->tipo_bus === 'doble_piso' && $numero > ($bus->capacidad / 2) ? 2 : 1;
                $tipo = in_array($numero, $especiales, true) ? 'preferencial' : 'normal';
                
                Asiento::create([
                    'viaje_id' => $viaje->id,
                    'numero' => $numero,
                    'fila' => $fila,
                    'columna' => $columna,
                    'piso' => $piso,
                    'tipo' => $tipo,
                    'estado' => 'disponible',
                ]);
                $count++;
            }
            
            $this->info("Viaje {$viaje->id}: Generated {$count} asientos (bus {$bus->placa}, capacidad {$bus->capacidad})");
        }
        
        $this->info('Done!');
        return 0;
    }
}