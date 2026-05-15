<?php

declare(strict_types=1);

use App\Http\Middleware\AuditoriaMiddleware;
use App\Http\Middleware\HorarioViajeMiddleware;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        channels: __DIR__ . '/../routes/channels.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'audit' => AuditoriaMiddleware::class,
            'horario.viaje' => HorarioViajeMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $e->getMessage(),
                'errors' => [],
                'code' => 'NO_AUTENTICADO',
            ], 401);
        });

        $exceptions->render(function (\Symfony\Component\Routing\Exception\RouteNotFoundException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Debe iniciar sesión para acceder a este recurso.',
                    'errors' => [],
                    'code' => 'NO_AUTENTICADO',
                ], 401);
            }
            return null;
        });
    })
    ->create();
