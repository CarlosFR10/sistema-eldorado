<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BoletoService;
use Illuminate\Support\Facades\Auth;

class BoletoController extends Controller
{
    protected $boletoService;

    public function __construct(BoletoService $boletoService)
    {
        $this->boletoService = $boletoService;
    }

    public function emitir(Request $request)
    {
        $data = $request->validate([
            'viaje_id' => 'required|integer|exists:viajes,id',
            'asiento_id' => 'required|integer|exists:asientos,id',
            'pasajero_id' => 'nullable|integer|exists:pasajeros,id',
        ]);
        $pasajeroId = $data['pasajero_id'] ?? Auth::id();
        $boleto = $this->boletoService->emitirBoleto($data['viaje_id'], $data['asiento_id'], $pasajeroId);
        return response()->json($boleto, 201);
    }

    public function show($id)
    {
        $boleto = $this->boletoService->obtenerBoleto($id);
        if (!$boleto) {
            return response()->json(['error' => 'Boleto no encontrado'], 404);
        }
        return response()->json($boleto);
    }
}
