<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ViajeSeeder extends Seeder
{
    private const HORAS_SALIDA = [
        '06:00', '07:30', '09:00', '10:30', '12:00',
        '13:30', '15:00', '16:30', '18:00', '20:00',
    ];

    public function run(): void
    {
        $rutas = DB::table('rutas')->where('activa', true)->orderBy('codigo')->get()->keyBy('codigo');
        $buses = DB::table('buses')->where('activo', true)->orderBy('id')->get();
        $conductores = DB::table('conductores')->orderBy('id')->get();
        $vendedorId = DB::table('usuarios')->where('email', 'vendedor1@eldorado.bo')->value('id');
        $base = now()->startOfDay();

        $rutasOrdenadas = [
            $rutas->get('CBB-SCZ'),
            $rutas->get('CBB-LPZ'),
            $rutas->get('CBB-ORU'),
            $rutas->get('CBB-SRE'),
            $rutas->get('CBB-PTS'),
            $rutas->get('CBB-TJA'),
            $rutas->get('CBB-TDD'),
            $rutas->get('CBB-CIJ'),
        ];

        for ($dia = -2; $dia < 20; $dia++) {
            $fechaBase = $base->copy()->addDays($dia);
            $busesOcupados = [];

            foreach (self::HORAS_SALIDA as $orden => $horaStr) {
                [$hora, $minuto] = array_map('intval', explode(':', $horaStr));
                $fechaSalida = $fechaBase->copy()->addHours($hora)->addMinutes($minuto);

                $ruta = $rutasOrdenadas[$orden % count($rutasOrdenadas)];
                if (!$ruta) continue;

                $conductor = $conductores->get($orden % $conductores->count()) ?? $conductores->first();

                $busAsignado = null;
                foreach ($buses as $bus) {
                    $busId = $bus->id;
                    $conflicto = false;
                    foreach ($busesOcupados as $ocupado) {
                        if ($ocupado['bus_id'] !== $busId) continue;
                        if ($fechaSalida->lt($ocupado['disponible_desde'])) {
                            $conflicto = true;
                            break;
                        }
                    }
                    if (!$conflicto) {
                        $busAsignado = $bus;
                        break;
                    }
                }

                if (!$busAsignado) continue;

                $busId = $busAsignado->id;
                $duracion = (float) $ruta->duracion_horas;
                $fechaLlegada = $fechaSalida->copy()->addMinutes((int) round($duracion * 60));
                $disponibleDesde = $fechaSalida->copy()->addMinutes(150);
                $busesOcupados[] = [
                    'bus_id' => $busId,
                    'disponible_desde' => $disponibleDesde,
                ];

                $codigo = 'VJ-' . $fechaSalida->format('Ymd') . '-' . str_pad((string) ($orden + 1), 3, '0', STR_PAD_LEFT);

                DB::table('viajes')->updateOrInsert(
                    ['codigo_viaje' => $codigo],
                    [
                        'ruta_id' => $ruta->id,
                        'bus_id' => $busId,
                        'conductor_id' => $conductor->id,
                        'vendedor_id' => $vendedorId,
                        'fecha_salida' => $fechaSalida,
                        'fecha_llegada_est' => $fechaLlegada,
                        'fecha_llegada_real' => null,
                        'precio_final' => $ruta->precio_base,
                        'estado' => 'en_venta',
                        'observaciones' => 'Viaje demo generado para pruebas de venta publica',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $viajeId = DB::table('viajes')->where('codigo_viaje', $codigo)->value('id');
                $this->generarAsientos((int) $viajeId, $busAsignado);
            }
        }
    }

    public static function horasEstandares(): array
    {
        return self::HORAS_SALIDA;
    }

    private function generarAsientos(int $viajeId, object $bus): void
    {
        $config = json_decode((string) $bus->config_asientos, true) ?: [];
        $columnas = max((int) ($config['columnas'] ?? 4), 1);
        $pasillo = (int) ($config['pasillo'] ?? 2);
        $especiales = $config['especiales'] ?? [];

        for ($numero = 1; $numero <= (int) $bus->capacidad; $numero++) {
            $columna = (($numero - 1) % $columnas) + 1;

            DB::table('asientos')->updateOrInsert(
                ['viaje_id' => $viajeId, 'numero' => $numero],
                [
                    'fila' => (int) ceil($numero / $columnas),
                    'columna' => $columna >= $pasillo ? $columna + 1 : $columna,
                    'piso' => $bus->tipo_bus === 'doble_piso' && $numero > ((int) $bus->capacidad / 2) ? 2 : 1,
                    'tipo' => in_array($numero, $especiales, true) ? 'preferencial' : 'normal',
                    'estado' => 'disponible',
                    'bloqueado_hasta' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}