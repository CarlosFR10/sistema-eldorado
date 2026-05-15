<?php

declare(strict_types=1);

use App\Models\Asiento;
use App\Models\Pasajero;
use App\Models\Usuario;
use App\Models\Viaje;
use App\Services\BoletoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('emitir boleto bloquea asiento correctamente', function (): void {
    $usuario = Usuario::factory()->create(['rol' => 'vendedor']);
    $this->actingAs($usuario, 'api');

    $viaje = Viaje::factory()->create(['estado' => 'en_venta']);
    $asiento = Asiento::factory()->create(['viaje_id' => $viaje->id, 'estado' => 'disponible']);
    $pasajero = Pasajero::factory()->create();

    $boleto = app(BoletoService::class)->emitir($viaje, $asiento, $pasajero, 'efectivo');

    expect($boleto->estado)->toBe('pagado')
        ->and($asiento->fresh()->estado)->toBe('reservado');
});

test('menor sin adulto responsable no puede comprar boleto', function (): void {
    $usuario = Usuario::factory()->create(['rol' => 'vendedor']);
    $this->actingAs($usuario, 'api');

    $viaje = Viaje::factory()->create(['estado' => 'en_venta']);
    $asiento = Asiento::factory()->create(['viaje_id' => $viaje->id]);
    $menor = Pasajero::factory()->create(['fecha_nacimiento' => now()->subYears(10)->toDateString()]);

    expect(fn () => app(BoletoService::class)->emitir($viaje, $asiento, $menor, 'efectivo'))
        ->toThrow(RuntimeException::class, 'Menor requiere adulto responsable registrado');
});
