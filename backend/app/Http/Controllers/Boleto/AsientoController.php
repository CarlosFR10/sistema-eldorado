<?php

declare(strict_types=1);

namespace App\Http\Controllers\Boleto;

use App\Http\Controllers\Controller;
use App\Services\AsientoService;
use Illuminate\Http\Request;

class AsientoController extends Controller
{
    public function __construct(private readonly AsientoService $asientoService)
    {
    }

    public function bloquear(Request $request)
    {
        $data = $request->validate([
            'viaje_id' => ['required', 'integer', 'exists:viajes,id'],
            'numero_asiento' => ['required', 'integer', 'min:1'],
        ]);

        try {
            $asiento = $this->asientoService->bloquearAsiento((int) $data['viaje_id'], (int) $data['numero_asiento']);

            return $this->success($asiento, 'Asiento bloqueado por 10 minutos.');
        } catch (\RuntimeException $exception) {
            return $this->error(
                $exception->getMessage(),
                ['numero_asiento' => [$exception->getMessage()]],
                'ASIENTO_NO_DISPONIBLE',
                409
            );
        }
    }

    public function liberar(Request $request)
    {
        $data = $request->validate([
            'viaje_id' => ['required', 'integer', 'exists:viajes,id'],
            'numero_asiento' => ['required', 'integer', 'min:1'],
        ]);

        $asiento = $this->asientoService->liberarAsiento((int) $data['viaje_id'], (int) $data['numero_asiento']);

        return $this->success($asiento, 'Asiento liberado correctamente.');
    }

    public function disponibles(int $id)
    {
        return $this->success($this->asientoService->disponibles($id), 'Asientos disponibles.');
    }
}
