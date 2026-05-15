<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\NotificacionGenerada;
use App\Services\NotificacionService;

class EnviarNotificacionListener
{
    public function handle(NotificacionGenerada $event)
    {
        app(NotificacionService::class)->enviar($event->usuarioId, $event->mensaje);
    }
}
