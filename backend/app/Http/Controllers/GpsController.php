<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GpsService;

class GpsController extends Controller
{
    protected $gpsService;

    public function __construct(GpsService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    public function registrar(Request $request)
    {
        $data = $request->validate([
            'bus_id' => 'required|integer|exists:buses,id',
            'viaje_id' => 'required|integer|exists:viajes,id',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'velocidad' => 'nullable|numeric',
            'rumbo' => 'nullable|numeric',
            'altitud' => 'nullable|numeric',
            'precision_m' => 'nullable|numeric',
        ]);
        $ubicacion = $this->gpsService->registrarUbicacion(
            $data['bus_id'],
            $data['viaje_id'],
            $data['latitud'],
            $data['longitud'],
            $data['velocidad'] ?? null,
            $data['rumbo'] ?? null,
            $data['altitud'] ?? null,
            $data['precision_m'] ?? null
        );
        return response()->json($ubicacion, 201);
    }

    public function historial($viajeId)
    {
        // Suponiendo que el modelo UbicacionGps existe y está relacionado
        $historial = \App\Models\UbicacionGps::where('viaje_id', $viajeId)
            ->orderBy('timestamp')
            ->get();
        return response()->json($historial);
    }
}
