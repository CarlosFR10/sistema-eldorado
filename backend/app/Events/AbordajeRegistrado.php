<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbordajeRegistrado
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $boletoId;
    public $pasajeroId;
    public $viajeId;

    public function __construct($boletoId, $pasajeroId, $viajeId)
    {
        $this->boletoId = $boletoId;
        $this->pasajeroId = $pasajeroId;
        $this->viajeId = $viajeId;
    }
}
