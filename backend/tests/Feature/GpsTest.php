<?php

declare(strict_types=1);

use App\Models\Bus;
use App\Models\Usuario;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registra ubicacion gps', function (): void {
    $usuario = Usuario::factory()->create(['rol' => 'supervisor']);
    $viaje = Viaje::factory()->create();
    $bus = Bus::find($viaje->bus_id);

    $this->actingAs($usuario, 'api')
        ->postJson('/api/gps/ubicacion', [
            'bus_id' => $bus->id,
            'viaje_id' => $viaje->id,
            'latitud' => -17.3895,
            'longitud' => -66.1568,
            'velocidad' => 55,
        ])->assertCreated()
        ->assertJsonPath('success', true);
});
