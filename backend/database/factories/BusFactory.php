<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bus;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusFactory extends Factory
{
    protected $model = Bus::class;

    public function definition(): array
    {
        return [
            'placa' => strtoupper($this->faker->unique()->bothify('####???')),
            'marca' => 'Volvo',
            'modelo' => '9800',
            'anio' => 2023,
            'capacidad' => 42,
            'tipo_bus' => 'semicama',
            'config_asientos' => ['filas' => 11, 'columnas' => 4, 'pasillo' => 2, 'especiales' => [1, 2]],
            'gps_imei' => $this->faker->numerify('8657330200#####'),
            'activo' => true,
        ];
    }
}
