<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Ruta;
use Illuminate\Database\Eloquent\Factories\Factory;

class RutaFactory extends Factory
{
    protected $model = Ruta::class;

    public function definition(): array
    {
        return [
            'codigo' => 'CBB-' . strtoupper($this->faker->unique()->lexify('???')),
            'origen' => 'Cochabamba',
            'destino' => $this->faker->city(),
            'distancia_km' => 300,
            'duracion_horas' => 5.5,
            'precio_base' => 50,
            'activa' => true,
            'paradas' => [],
        ];
    }
}
