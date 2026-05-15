<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('viaje.{viajeId}.asientos', function ($user, int $viajeId): bool {
    return in_array($user->rol, ['vendedor', 'supervisor', 'administrador'], true);
});

Broadcast::channel('viaje.{viajeId}.abordaje', function ($user, int $viajeId): bool {
    return in_array($user->rol, ['auxiliar', 'supervisor', 'administrador'], true);
});

Broadcast::channel('gps.buses', function ($user): bool {
    return in_array($user->rol, ['supervisor', 'administrador'], true);
});

Broadcast::channel('alertas.supervisores', function ($user): bool {
    return in_array($user->rol, ['supervisor', 'administrador'], true);
});
