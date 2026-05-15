<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->rol, $roles, true)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'No autorizado para esta operacion.',
                'errors' => ['rol' => ['El rol del usuario no tiene permiso.']],
                'code' => 'RBAC_DENEGADO',
            ], 403);
        }

        return $next($request);
    }
}
