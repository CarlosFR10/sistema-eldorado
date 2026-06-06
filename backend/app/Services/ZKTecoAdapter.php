<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HuellaDactilar;

class ZKTecoAdapter extends SimuladorAdapter
{
    public function registrarHuella(int $pasajeroId, string $plantilla, string $dedo, int $calidad, int $usuarioId): HuellaDactilar
    {
        return parent::registrarHuella($pasajeroId, $plantilla, $dedo, $calidad, $usuarioId);
    }
}