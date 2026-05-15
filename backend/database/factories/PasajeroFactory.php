<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pasajero;
use Illuminate\Database\Eloquent\Factories\Factory;

class PasajeroFactory extends Factory
{
    protected $model = Pasajero::class;

    public function definition(): array
    {
        return [
            'nombres' => $this->faker->firstName(),
            'apellidos' => $this->faker->lastName(),
            'numero_ci' => (string) $this->faker->unique()->numberBetween(1000000, 9999999),
            'complemento_ci' => null,
            'expedido_en' => 'CB',
            'fecha_nacimiento' => now()->subYears(30)->toDateString(),
            'telefono' => '70000000',
            'email' => $this->faker->safeEmail(),
            'tiene_huella' => false,
        ];
    }
}
