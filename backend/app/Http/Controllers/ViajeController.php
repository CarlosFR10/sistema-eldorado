<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Viaje;

class ViajeController extends Controller
{
    public function index()
    {
        $viajes = Viaje::with(['ruta', 'bus', 'asientos'])->get();
        return response()->json($viajes);
    }

    public function show($id)
    {
        $viaje = Viaje::with(['ruta', 'bus', 'asientos'])->find($id);
        if (!$viaje) {
            return response()->json(['error' => 'Viaje no encontrado'], 404);
        }
        return response()->json($viaje);
    }

    public function disponibles()
    {
        $viajes = Viaje::where('fecha_salida', '>=', now())
            ->with(['ruta', 'bus', 'asientos'])
            ->get();
        return response()->json($viajes);
    }
}
