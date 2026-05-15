<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\AbordajeRegistrado;
use App\Models\EventoAbordaje;

class RegistrarEventoAbordajeListener
{
    public function handle(AbordajeRegistrado $event)
    {
        EventoAbordaje::create([
            'boleto_id' => $event->boletoId,
            'pasajero_id' => $event->pasajeroId,
            'viaje_id' => $event->viajeId,
            'timestamp' => now(),
        ]);
    }
}
