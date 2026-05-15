<?php

declare(strict_types=1);

return [
    'name' => env('APP_NAME', 'SistemaEldorado'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY'),
    'qr_secret' => env('QR_SECRET', env('APP_KEY')),
    'cipher' => 'AES-256-CBC',
    'locale' => 'es',
    'fallback_locale' => 'es',
    'faker_locale' => 'es_BO',
    'timezone' => env('APP_TIMEZONE', 'America/La_Paz'),
];
