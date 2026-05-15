<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AsientoEstadoCambiado;
use App\Models\Asiento;

class ActualizarEstadoAsientoListener
{
    public function handle(AsientoEstadoCambiado $event)
    {
        $asiento = Asiento::find($event->asientoId);
        if ($asiento) {
            $asiento->estado = $event->nuevoEstado;
            $asiento->save();
        }
    }
}
