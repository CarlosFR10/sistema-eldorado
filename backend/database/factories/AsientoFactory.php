<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asiento;
use App\Models\Viaje;
use Illuminate\Database\Eloquent\Factories\Factory;

class AsientoFactory extends Factory
{
    protected $model = Asiento::class;

    public function definition(): array
    {
        return [
            'viaje_id' => Viaje::factory(),
            'numero' => $this->faker->unique()->numberBetween(1, 42),
            'fila' => 1,
            'columna' => 1,
            'piso' => 1,
            'tipo' => 'normal',
            'estado' => 'disponible',
            'bloqueado_hasta' => null,
        ];
    }
}
