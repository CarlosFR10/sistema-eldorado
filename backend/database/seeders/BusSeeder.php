<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        $catalogo = [
            ['placa' => '1234ABC', 'marca' => 'Marcopolo', 'modelo' => 'Paradiso 1200', 'anio' => 2022, 'capacidad' => 42, 'tipo_bus' => 'semicama'],
            ['placa' => '5678DEF', 'marca' => 'Volvo', 'modelo' => '9800 DD', 'anio' => 2023, 'capacidad' => 60, 'tipo_bus' => 'doble_piso'],
            ['placa' => '9012GHI', 'marca' => 'Scania', 'modelo' => 'K360', 'anio' => 2021, 'capacidad' => 44, 'tipo_bus' => 'semicama'],
            ['placa' => '3456JKL', 'marca' => 'Mercedes-Benz', 'modelo' => 'O500 RS', 'anio' => 2020, 'capacidad' => 40, 'tipo_bus' => 'ejecutivo'],
            ['placa' => '7890MNO', 'marca' => 'Volvo', 'modelo' => 'B450R', 'anio' => 2024, 'capacidad' => 56, 'tipo_bus' => 'cama_completa'],
            ['placa' => '1122PQR', 'marca' => 'Yutong', 'modelo' => 'ZK6122', 'anio' => 2022, 'capacidad' => 38, 'tipo_bus' => 'ejecutivo'],
            ['placa' => '3344STU', 'marca' => 'Marcopolo', 'modelo' => 'G7 1800 DD', 'anio' => 2023, 'capacidad' => 64, 'tipo_bus' => 'doble_piso'],
            ['placa' => '5566VWX', 'marca' => 'Scania', 'modelo' => 'K410', 'anio' => 2021, 'capacidad' => 46, 'tipo_bus' => 'semicama'],
            ['placa' => '7788YZA', 'marca' => 'Mercedes-Benz', 'modelo' => 'O500 RSD', 'anio' => 2024, 'capacidad' => 52, 'tipo_bus' => 'cama_completa'],
            ['placa' => '9900BCD', 'marca' => 'Yutong', 'modelo' => 'T13', 'anio' => 2022, 'capacidad' => 36, 'tipo_bus' => 'ejecutivo'],
            ['placa' => '1357EFG', 'marca' => 'Volvo', 'modelo' => '9800', 'anio' => 2021, 'capacidad' => 44, 'tipo_bus' => 'semicama'],
            ['placa' => '2468HIJ', 'marca' => 'Marcopolo', 'modelo' => 'Paradiso 1600', 'anio' => 2020, 'capacidad' => 48, 'tipo_bus' => 'cama_completa'],
            ['placa' => '3579KLM', 'marca' => 'Scania', 'modelo' => 'K440 DD', 'anio' => 2023, 'capacidad' => 62, 'tipo_bus' => 'doble_piso'],
            ['placa' => '4680NOP', 'marca' => 'Mercedes-Benz', 'modelo' => 'O500 M', 'anio' => 2022, 'capacidad' => 34, 'tipo_bus' => 'ejecutivo'],
            ['placa' => '5791QRS', 'marca' => 'Marcopolo', 'modelo' => 'G8 1200', 'anio' => 2024, 'capacidad' => 42, 'tipo_bus' => 'semicama'],
            ['placa' => '6802TUV', 'marca' => 'Volvo', 'modelo' => 'B420R', 'anio' => 2021, 'capacidad' => 50, 'tipo_bus' => 'cama_completa'],
            ['placa' => '7913WXY', 'marca' => 'Yutong', 'modelo' => 'ZK6138', 'anio' => 2023, 'capacidad' => 58, 'tipo_bus' => 'doble_piso'],
            ['placa' => '8024ZAB', 'marca' => 'Scania', 'modelo' => 'K310', 'anio' => 2020, 'capacidad' => 38, 'tipo_bus' => 'semicama'],
            ['placa' => '9135CDE', 'marca' => 'Mercedes-Benz', 'modelo' => 'OF 1721', 'anio' => 2022, 'capacidad' => 32, 'tipo_bus' => 'ejecutivo'],
            ['placa' => '0246FGH', 'marca' => 'Volvo', 'modelo' => 'B430R', 'anio' => 2024, 'capacidad' => 54, 'tipo_bus' => 'cama_completa'],
        ];

        DB::table('buses')
            ->whereNotIn('placa', array_column($catalogo, 'placa'))
            ->update([
                'activo' => false,
                'updated_at' => now(),
            ]);

        foreach ($catalogo as $index => $bus) {
            DB::table('buses')->updateOrInsert(
                ['placa' => $bus['placa']],
                [
                    ...$bus,
                    'config_asientos' => json_encode($this->configuracion((int) $bus['capacidad'], $bus['tipo_bus'])),
                    'gps_imei' => '8657330200' . str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function configuracion(int $capacidad, string $tipoBus): array
    {
        $columnas = $capacidad <= 36 ? 3 : 4;

        return [
            'filas' => (int) ceil($capacidad / $columnas),
            'columnas' => $columnas,
            'pasillo' => $columnas === 3 ? 2 : 3,
            'especiales' => $tipoBus === 'ejecutivo' ? [1, 2] : [1, 2, 3, 4],
            'categoria' => $capacidad >= 56 ? 'grande' : ($capacidad >= 40 ? 'mediano' : 'pequeno'),
        ];
    }
}
