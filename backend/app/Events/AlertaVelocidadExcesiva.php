<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AlertaVelocidadExcesiva implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $busId,
        public readonly string $placa,
        public readonly float $velocidad,
        public readonly ?string $conductorNombre = null
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('alertas.supervisores');
    }

    public function broadcastAs(): string
    {
        return 'alerta.velocidad_excesiva';
    }

    public function broadcastWith(): array
    {
        return [
            'bus_id' => $this->busId,
            'placa' => $this->placa,
            'velocidad' => $this->velocidad,
            'conductor_nombre' => $this->conductorNombre,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
