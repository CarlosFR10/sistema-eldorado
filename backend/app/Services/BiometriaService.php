<?php

declare(strict_types=1);

namespace App\Services;

class BiometriaService
{
    private SimuladorAdapter|ZKTecoAdapter $driver;

    public function __construct()
    {
        $this->driver = config('services.biometria.driver', env('BIOMETRIA_DRIVER', 'simulador')) === 'zkteco'
            ? new ZKTecoAdapter()
            : new SimuladorAdapter();
    }

    public function registrarHuella(int $pasajeroId, string $plantilla, string $dedo, int $calidad, int $usuarioId)
    {
        return $this->driver->registrarHuella($pasajeroId, $plantilla, $dedo, $calidad, $usuarioId);
    }

    public function verificarHuella(int $pasajeroId, string $plantilla): bool
    {
        return $this->driver->verificarHuella($pasajeroId, $plantilla);
    }

    public function eliminarHuella(int $pasajeroId): int
    {
        return $this->driver->eliminarHuella($pasajeroId);
    }
}
