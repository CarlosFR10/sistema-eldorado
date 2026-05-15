<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::call(function (): void {
    app(\App\Services\AsientoService::class)->liberarExpirados();
})->everyMinute();
