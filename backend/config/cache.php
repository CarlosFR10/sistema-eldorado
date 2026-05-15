<?php

declare(strict_types=1);

return [
    'default' => env('CACHE_DRIVER', 'redis'),
    'stores' => [
        'array' => ['driver' => 'array'],
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => 'cache',
        ],
    ],
    'prefix' => env('CACHE_PREFIX', 'eldorado_cache'),
];
