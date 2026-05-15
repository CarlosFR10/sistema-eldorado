<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PasajeroAbordado implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $viajeId,
        public readonly string $pasajeroNombre,
        public readonly int $asiento,
        public readonly int $totalAbordados,
        public readonly int $totalBoletos
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("viaje.{$this->viajeId}.abordaje");
    }

    public function broadcastAs(): string
    {
        return 'pasajero.abordado';
    }

    public function broadcastWith(): array
    {
        return [
            'viaje_id' => $this->viajeId,
            'pasajero_nombre' => $this->pasajeroNombre,
            'asiento' => $this->asiento,
            'timestamp' => now()->toIso8601String(),
            'total_abordados' => $this->totalAbordados,
            'total_boletos' => $this->totalBoletos,
        ];
    }
}
