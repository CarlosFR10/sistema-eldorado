<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\AsientoEstadoCambiado::class => [
            \App\Listeners\ActualizarEstadoAsientoListener::class,
        ],
        \App\Events\AbordajeRegistrado::class => [
            \App\Listeners\RegistrarEventoAbordajeListener::class,
        ],
        \App\Events\NotificacionGenerada::class => [
            \App\Listeners\EnviarNotificacionListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
