<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conductor;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConductorFactory extends Factory
{
    protected $model = Conductor::class;

    public function definition(): array
    {
        return [
            'usuario_id' => Usuario::factory()->create(['rol' => 'conductor'])->id,
            'licencia' => strtoupper($this->faker->unique()->bothify('CB-######')),
            'categoria' => 'C',
            'vencimiento_lic' => now()->addYears(3)->toDateString(),
        ];
    }
}
