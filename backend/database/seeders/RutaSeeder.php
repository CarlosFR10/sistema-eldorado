<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RutaSeeder extends Seeder
{
    public function run(): void
    {
        $destinos = [
            ['codigo' => 'LPZ', 'ciudad' => 'La Paz', 'distancia_km' => 390, 'duracion_horas' => 6.5, 'precio_base' => 50.00, 'paradas' => ['Oruro', 'El Alto']],
            ['codigo' => 'SCZ', 'ciudad' => 'Santa Cruz', 'distancia_km' => 500, 'duracion_horas' => 8.0, 'precio_base' => 60.00, 'paradas' => ['Villa Tunari', 'Montero']],
            ['codigo' => 'ORU', 'ciudad' => 'Oruro', 'distancia_km' => 210, 'duracion_horas' => 3.5, 'precio_base' => 30.00, 'paradas' => ['Caracollo']],
            ['codigo' => 'PTS', 'ciudad' => 'Potosi', 'distancia_km' => 320, 'duracion_horas' => 5.0, 'precio_base' => 45.00, 'paradas' => ['Oruro']],
            ['codigo' => 'SRE', 'ciudad' => 'Sucre', 'distancia_km' => 340, 'duracion_horas' => 5.5, 'precio_base' => 50.00, 'paradas' => ['Aiquile']],
            ['codigo' => 'TJA', 'ciudad' => 'Tarija', 'distancia_km' => 610, 'duracion_horas' => 10.0, 'precio_base' => 85.00, 'paradas' => ['Sucre', 'Camargo']],
            ['codigo' => 'TDD', 'ciudad' => 'Trinidad', 'distancia_km' => 610, 'duracion_horas' => 11.0, 'precio_base' => 90.00, 'paradas' => ['Villa Tunari', 'San Ignacio de Moxos']],
            ['codigo' => 'CIJ', 'ciudad' => 'Cobija', 'distancia_km' => 1240, 'duracion_horas' => 22.0, 'precio_base' => 180.00, 'paradas' => ['Trinidad', 'Riberalta']],
        ];

        $codigosCatalogo = collect($destinos)
            ->flatMap(fn (array $destino): array => ['CBB-' . $destino['codigo'], $destino['codigo'] . '-CBB'])
            ->all();

        DB::table('rutas')
            ->whereNotIn('codigo', $codigosCatalogo)
            ->update([
                'activa' => false,
                'updated_at' => now(),
            ]);

        foreach ($destinos as $destino) {
            $this->guardarRuta(
                codigo: 'CBB-' . $destino['codigo'],
                origen: 'Cochabamba',
                destino: $destino['ciudad'],
                distanciaKm: $destino['distancia_km'],
                duracionHoras: $destino['duracion_horas'],
                precioBase: $destino['precio_base'],
                paradas: $destino['paradas']
            );

            $this->guardarRuta(
                codigo: $destino['codigo'] . '-CBB',
                origen: $destino['ciudad'],
                destino: 'Cochabamba',
                distanciaKm: $destino['distancia_km'],
                duracionHoras: $destino['duracion_horas'],
                precioBase: $destino['precio_base'],
                paradas: array_reverse($destino['paradas'])
            );
        }
    }

    private function guardarRuta(string $codigo, string $origen, string $destino, float $distanciaKm, float $duracionHoras, float $precioBase, array $paradas): void
    {
        DB::table('rutas')->updateOrInsert(
            ['codigo' => $codigo],
            [
                'origen' => $origen,
                'destino' => $destino,
                'distancia_km' => $distanciaKm,
                'duracion_horas' => $duracionHoras,
                'precio_base' => $precioBase,
                'activa' => true,
                'paradas' => json_encode($paradas),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
