<?php

declare(strict_types=1);

use App\Models\Asiento;
use App\Models\Viaje;
use App\Services\AsientoService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('bloquea asiento por diez minutos', function (): void {
    $viaje = Viaje::factory()->create();
    $asiento = Asiento::factory()->create(['viaje_id' => $viaje->id, 'numero' => 7]);

    $bloqueado = app(AsientoService::class)->bloquearAsiento($viaje->id, $asiento->numero);

    expect($bloqueado->estado)->toBe('bloqueado')
        ->and($bloqueado->bloqueado_hasta)->not()->toBeNull();
});
