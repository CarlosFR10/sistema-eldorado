<?php

declare(strict_types=1);

namespace App\Services;

class NotificacionService
{
    public function enviar($usuarioId, $mensaje)
    {
        // Simulación de envío de notificación (puede ser WebSocket, email, etc.)
        return true;
    }
}
