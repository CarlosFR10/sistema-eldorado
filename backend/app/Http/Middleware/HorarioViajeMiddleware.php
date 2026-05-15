<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Viaje;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HorarioViajeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $viajeId = $request->route('viaje') ?: $request->route('id') ?: $request->input('viaje_id');
        $viaje = $viajeId ? Viaje::find($viajeId) : null;

        if (!$viaje || $viaje->fecha_salida->isFuture()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'El viaje aun no inicia o no existe.',
                'errors' => ['viaje_id' => ['Viaje no habilitado para esta operacion.']],
                'code' => 'VIAJE_FUERA_DE_HORARIO',
            ], 403);
        }

        return $next($request);
    }
}
