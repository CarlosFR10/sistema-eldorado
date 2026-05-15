<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AbordajeService;

class AbordajeController extends Controller
{
    protected $abordajeService;

    public function __construct(AbordajeService $abordajeService)
    {
        $this->abordajeService = $abordajeService;
    }

    public function validar(Request $request)
    {
        $data = $request->validate([
            'boleto_id' => 'required|integer|exists:boletos,id',
            'biometria' => 'nullable|string',
        ]);
        $result = $this->abordajeService->validarAbordaje($data['boleto_id'], $data['biometria'] ?? null);
        if (!$result['valido']) {
            return response()->json(['error' => $result['mensaje']], 403);
        }
        return response()->json(['mensaje' => 'Abordaje registrado', 'evento' => $result['evento']]);
    }
}
