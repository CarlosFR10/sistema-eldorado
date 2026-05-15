<?php

declare(strict_types=1);

use App\Models\Asiento;
use App\Models\Pasajero;
use App\Models\Usuario;
use App\Models\Viaje;
use App\Services\BoletoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('validacion qr aprueba pasajero con boleto pagado', function (): void {
    $vendedor = Usuario::factory()->create(['rol' => 'vendedor']);
    $auxiliar = Usuario::factory()->create(['rol' => 'auxiliar']);
    $this->actingAs($vendedor, 'api');

    $viaje = Viaje::factory()->create(['estado' => 'abordando']);
    $asiento = Asiento::factory()->create(['viaje_id' => $viaje->id]);
    $pasajero = Pasajero::factory()->create();
    $viaje->update(['estado' => 'en_venta']);
    $boleto = app(BoletoService::class)->emitir($viaje->fresh(), $asiento, $pasajero, 'efectivo');
    $viaje->update(['estado' => 'abordando']);

    $this->actingAs($auxiliar, 'api')
        ->postJson('/api/abordaje/validar-qr', ['codigo_boleto' => $boleto->codigo_boleto])
        ->assertOk()
        ->assertJsonPath('success', true);
});
