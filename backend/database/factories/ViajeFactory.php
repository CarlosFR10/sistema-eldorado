<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bus;
use App\Models\Conductor;
use App\Models\Ruta;
use App\Models\Usuario;
use App\Models\Viaje;
use Illuminate\Database\Eloquent\Factories\Factory;

class ViajeFactory extends Factory
{
    protected $model = Viaje::class;

    public function definition(): array
    {
        return [
            'codigo_viaje' => 'VJ-' . now()->format('Ymd') . '-' . $this->faker->unique()->numberBetween(100, 999),
            'ruta_id' => Ruta::factory(),
            'bus_id' => Bus::factory(),
            'conductor_id' => Conductor::factory(),
            'vendedor_id' => Usuario::factory()->create(['rol' => 'vendedor'])->id,
            'fecha_salida' => now()->addDay(),
            'fecha_llegada_est' => now()->addDay()->addHours(6),
            'fecha_llegada_real' => null,
            'precio_final' => 50,
            'estado' => 'en_venta',
            'observaciones' => null,
        ];
    }
}
