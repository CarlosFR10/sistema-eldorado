<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AsientoActualizado;
use App\Models\Asiento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AsientoService
{
    public function bloquearAsiento(int $viajeId, int $numeroAsiento, int $minutos = 10): Asiento
    {
        return DB::transaction(function () use ($viajeId, $numeroAsiento, $minutos): Asiento {
            $asiento = Asiento::where('viaje_id', $viajeId)
                ->where('numero', $numeroAsiento)
                ->lockForUpdate()
                ->first();

            if (!$asiento) {
                throw new \RuntimeException('El asiento solicitado no existe.');
            }

            if ($asiento->estado !== 'disponible') {
                throw new \RuntimeException('El asiento ya no esta disponible.');
            }

            $asiento->update([
                'estado' => 'bloqueado',
                'bloqueado_hasta' => now()->addMinutes($minutos),
            ]);

            Cache::put($this->cacheKey($viajeId, $numeroAsiento), 'bloqueado', now()->addMinutes($minutos));
            event(new AsientoActualizado($viajeId, $numeroAsiento, 'bloqueado', $asiento->bloqueado_hasta?->toIso8601String()));

            return $asiento->fresh();
        });
    }

    public function liberarAsiento(int $viajeId, int $numeroAsiento): Asiento
    {
        return DB::transaction(function () use ($viajeId, $numeroAsiento): Asiento {
            $asiento = Asiento::where('viaje_id', $viajeId)
                ->where('numero', $numeroAsiento)
                ->lockForUpdate()
                ->first();

            if (!$asiento) {
                throw new \RuntimeException('El asiento solicitado no existe.');
            }

            if (!in_array($asiento->estado, ['bloqueado', 'reservado'], true)) {
                throw new \RuntimeException('El asiento no puede liberarse desde su estado actual.');
            }

            $asiento->update([
                'estado' => 'disponible',
                'bloqueado_hasta' => null,
            ]);

            Cache::forget($this->cacheKey($viajeId, $numeroAsiento));
            event(new AsientoActualizado($viajeId, $numeroAsiento, 'disponible'));

            return $asiento->fresh();
        });
    }

    public function disponibles(int $viajeId)
    {
        $this->liberarExpirados($viajeId);

        return Asiento::where('viaje_id', $viajeId)
            ->where('estado', 'disponible')
            ->orderBy('numero')
            ->get();
    }

    public function liberarExpirados(?int $viajeId = null): int
    {
        $query = Asiento::where('estado', 'bloqueado')
            ->whereNotNull('bloqueado_hasta')
            ->where('bloqueado_hasta', '<', now());

        if ($viajeId !== null) {
            $query->where('viaje_id', $viajeId);
        }

        $asientos = $query->get();

        foreach ($asientos as $asiento) {
            $asiento->update([
                'estado' => 'disponible',
                'bloqueado_hasta' => null,
            ]);
            Cache::forget($this->cacheKey((int) $asiento->viaje_id, (int) $asiento->numero));
            event(new AsientoActualizado((int) $asiento->viaje_id, (int) $asiento->numero, 'disponible'));
        }

        return $asientos->count();
    }

    private function cacheKey(int $viajeId, int $numeroAsiento): string
    {
        return "asiento:{$viajeId}:{$numeroAsiento}";
    }
}
