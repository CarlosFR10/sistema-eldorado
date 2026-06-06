<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\HuellaDactilar;
use App\Models\Pasajero;
use Illuminate\Support\Facades\Crypt;

class SimuladorAdapter
{
    public function registrarHuella(int $pasajeroId, string $plantilla, string $dedo, int $calidad, int $usuarioId): HuellaDactilar
    {
        $huella = HuellaDactilar::updateOrCreate(
            ['pasajero_id' => $pasajeroId, 'dedo' => $dedo],
            [
                'plantilla' => Crypt::encryptString($plantilla),
                'calidad' => $calidad,
                'registrado_por' => $usuarioId,
            ]
        );

        Pasajero::whereKey($pasajeroId)->update(['tiene_huella' => true]);

        return $huella;
    }

    public function verificarHuella(int $pasajeroId, string $plantilla): bool
    {
        $huellas = HuellaDactilar::where('pasajero_id', $pasajeroId)->get();

        foreach ($huellas as $huella) {
            if (hash_equals(Crypt::decryptString($huella->plantilla), $plantilla)) {
                return true;
            }
        }

        return false;
    }

    public function eliminarHuella(int $pasajeroId): int
    {
        $eliminadas = HuellaDactilar::where('pasajero_id', $pasajeroId)->delete();
        Pasajero::whereKey($pasajeroId)->update(['tiene_huella' => false]);

        return $eliminadas;
    }
}