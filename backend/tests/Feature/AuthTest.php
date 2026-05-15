<?php

declare(strict_types=1);

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('login retorna token para vendedor activo', function (): void {
    Usuario::factory()->create(['email' => 'vendedor@test.bo', 'rol' => 'vendedor']);

    $this->postJson('/api/auth/login', [
        'email' => 'vendedor@test.bo',
        'password' => 'Eldorado2026!',
    ])->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['data' => ['token', 'usuario']]);
});
