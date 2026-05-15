<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsientoEstadoCambiado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $asientoId;
    public $nuevoEstado;
    public $viajeId;

    public function __construct($asientoId, $nuevoEstado, $viajeId)
    {
        $this->asientoId = $asientoId;
        $this->nuevoEstado = $nuevoEstado;
        $this->viajeId = $viajeId;
    }
}
