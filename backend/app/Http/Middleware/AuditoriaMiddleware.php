<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditoriaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            AuditLog::create([
                'usuario_id' => $request->user()->id,
                'accion' => strtolower($request->method()) . '.' . str_replace('/', '.', $request->path()),
                'tabla_afectada' => $this->tablaDesdeRuta($request->path()),
                'registro_id' => $request->route('id'),
                'datos_antes' => null,
                'datos_despues' => $request->except(['password', 'plantilla', 'token_2fa']),
                'ip' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    private function tablaDesdeRuta(string $path): ?string
    {
        $segment = explode('/', trim($path, '/'))[1] ?? null;

        return $segment ?: null;
    }
}
