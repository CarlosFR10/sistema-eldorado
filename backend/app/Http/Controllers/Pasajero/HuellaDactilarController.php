<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasajero;

use App\Http\Controllers\Controller;
use App\Models\Pasajero;
use App\Services\BiometriaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HuellaDactilarController extends Controller
{
    public function __construct(private readonly BiometriaService $biometriaService)
    {
    }

    public function store(Request $request, int $id)
    {
        Pasajero::findOrFail($id);
        $data = $this->validarHuella($request);

        $huella = $this->biometriaService->registrarHuella(
            $id,
            $data['plantilla'],
            $data['dedo'],
            (int) $data['calidad'],
            auth()->id() ?: 1
        );

        return $this->success($huella, 'Huella registrada correctamente.', 201);
    }

    public function update(Request $request, int $id)
    {
        return $this->store($request, $id);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'pasajero_id' => ['required', 'integer', 'exists:pasajeros,id'],
            'plantilla' => ['required', 'string'],
        ]);

        return $this->success([
            'coincide' => $this->biometriaService->verificarHuella((int) $data['pasajero_id'], $data['plantilla']),
        ], 'Verificacion biometrica completada.');
    }

    public function destroy(int $id)
    {
        Pasajero::findOrFail($id);

        return $this->success([
            'eliminadas' => $this->biometriaService->eliminarHuella($id),
        ], 'Huella eliminada correctamente.');
    }

    private function validarHuella(Request $request): array
    {
        return $request->validate([
            'plantilla' => ['required', 'string'],
            'dedo' => ['required', Rule::in(['pulgar_der', 'indice_der', 'medio_der', 'pulgar_izq', 'indice_izq'])],
            'calidad' => ['required', 'integer', 'between:0,100'],
        ]);
    }
}
