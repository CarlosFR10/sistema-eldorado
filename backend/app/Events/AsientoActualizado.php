<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AsientoActualizado implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $viajeId,
        public readonly int $numeroAsiento,
        public readonly string $estado,
        public readonly ?string $bloqueadoHasta = null
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("viaje.{$this->viajeId}.asientos");
    }

    public function broadcastAs(): string
    {
        return 'asiento.actualizado';
    }

    public function broadcastWith(): array
    {
        return [
            'viaje_id' => $this->viajeId,
            'numero_asiento' => $this->numeroAsiento,
            'estado' => $this->estado,
            'bloqueado_hasta' => $this->bloqueadoHasta,
        ];
    }
}
