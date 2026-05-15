<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificacionGenerada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $usuarioId;
    public $mensaje;

    public function __construct($usuarioId, $mensaje)
    {
        $this->usuarioId = $usuarioId;
        $this->mensaje = $mensaje;
    }
}
