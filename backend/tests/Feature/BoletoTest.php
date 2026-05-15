<?php

declare(strict_types=1);

use App\Models\Asiento;
use App\Models\Pasajero;
use App\Models\Usuario;
use App\Models\Viaje;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('api emite boleto pagado', function (): void {
    $usuario = Usuario::factory()->create(['rol' => 'vendedor']);
    $viaje = Viaje::factory()->create(['estado' => 'en_venta']);
    $asiento = Asiento::factory()->create(['viaje_id' => $viaje->id]);
    $pasajero = Pasajero::factory()->create();

    $this->actingAs($usuario, 'api')
        ->postJson('/api/boletos', [
            'viaje_id' => $viaje->id,
            'asiento_id' => $asiento->id,
            'pasajero_id' => $pasajero->id,
            'metodo_pago' => 'efectivo',
        ])->assertCreated()
        ->assertJsonPath('success', true);
});
