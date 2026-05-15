<?php

declare(strict_types=1);

return [
    'biometria' => [
        'driver' => env('BIOMETRIA_DRIVER', 'simulador'),
    ],
    'sentry' => [
        'dsn' => env('SENTRY_LARAVEL_DSN'),
    ],
];
