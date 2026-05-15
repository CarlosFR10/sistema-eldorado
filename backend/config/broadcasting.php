<?php

declare(strict_types=1);

return [
    'default' => env('BROADCAST_CONNECTION', env('BROADCAST_DRIVER', 'reverb')),
    'connections' => [
        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY', 'eldorado-key'),
            'secret' => env('REVERB_APP_SECRET', 'eldorado-secret'),
            'app_id' => env('REVERB_APP_ID', 'eldorado'),
            'options' => [
                'host' => env('REVERB_HOST', 'localhost'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
            ],
        ],
        'log' => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],
];
