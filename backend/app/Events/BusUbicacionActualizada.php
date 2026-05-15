<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BusUbicacionActualizada implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $busId,
        public readonly string $placa,
        public readonly float $latitud,
        public readonly float $longitud,
        public readonly ?float $velocidad,
        public readonly ?float $rumbo,
        public readonly ?int $viajeId
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('gps.buses');
    }

    public function broadcastAs(): string
    {
        return 'bus.ubicacion_actualizada';
    }

    public function broadcastWith(): array
    {
        return [
            'bus_id' => $this->busId,
            'placa' => $this->placa,
            'latitud' => $this->latitud,
            'longitud' => $this->longitud,
            'velocidad' => $this->velocidad,
            'rumbo' => $this->rumbo,
            'viaje_id' => $this->viajeId,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
