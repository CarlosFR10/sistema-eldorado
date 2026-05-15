<?php

declare(strict_types=1);

use App\Http\Controllers\Abordaje\AbordajeController;
use App\Http\Controllers\Admin\ReporteController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Autoridad\ConsultaViajeController;
use App\Http\Controllers\Boleto\AsientoController;
use App\Http\Controllers\Boleto\BoletoController;
use App\Http\Controllers\GPS\GpsController;
use App\Http\Controllers\Pasajero\HuellaDactilarController;
use App\Http\Controllers\Pasajero\PasajeroController;
use App\Http\Controllers\Viaje\BusController;
use App\Http\Controllers\Viaje\RutaController;
use App\Http\Controllers\Viaje\ViajeController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Broadcast::routes(['middleware' => ['auth:api']]);

Route::prefix('auth')->group(function (): void {
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:login');
    Route::post('verify-2fa', [LoginController::class, 'verify2fa'])->middleware('throttle:login');

    Route::middleware('auth:api')->group(function (): void {
        Route::post('logout', [LogoutController::class, 'logout']);
        Route::post('refresh', [LoginController::class, 'refresh']);
        Route::get('me', [LoginController::class, 'me']);
    });
});

Route::prefix('consulta')->group(function (): void {
    Route::get('viaje/{codigo_qr}', [ConsultaViajeController::class, 'consultaPorQr']);
    Route::get('viaje/{id}/manifiesto', [ConsultaViajeController::class, 'manifiesto'])->whereNumber('id');
    Route::get('boleto/{codigo}', [ConsultaViajeController::class, 'boleto']);
    Route::get('cliente/{ci}/boletos', [ConsultaViajeController::class, 'boletosPorCi']);
});

Route::prefix('public')->group(function (): void {
    Route::get('rutas', [RutaController::class, 'index']);
    Route::get('viajes', [ViajeController::class, 'index']);
    Route::get('viajes/{id}/asientos', [ViajeController::class, 'asientos'])->whereNumber('id');
    Route::get('rastreo/{codigo}', [ConsultaViajeController::class, 'rastreo']);
    Route::get('pasajeros/buscar', [PasajeroController::class, 'publicBuscar']);
    Route::post('pasajeros/pre-registro', [PasajeroController::class, 'publicStore']);
    Route::post('boletos/reservar', [BoletoController::class, 'publicReservar']);
});

Route::middleware(['auth:api', 'audit'])->group(function (): void {
    Route::get('pasajeros/buscar', [PasajeroController::class, 'buscar']);
    Route::apiResource('pasajeros', PasajeroController::class)->except(['destroy']);
    Route::get('pasajeros/{id}/boletos', [PasajeroController::class, 'boletos'])->whereNumber('id');
    Route::post('pasajeros/{id}/huella', [HuellaDactilarController::class, 'store'])->whereNumber('id');
    Route::put('pasajeros/{id}/huella', [HuellaDactilarController::class, 'update'])->whereNumber('id');
    Route::delete('pasajeros/{id}/huella', [HuellaDactilarController::class, 'destroy'])->whereNumber('id');
    Route::post('biometria/verificar', [HuellaDactilarController::class, 'verify']);
    Route::post('pasajeros/{id}/menor/vincular', [PasajeroController::class, 'vincularAdulto'])->whereNumber('id');
    Route::get('pasajeros/{id}/menor/adulto', [PasajeroController::class, 'adultoResponsable'])->whereNumber('id');
    Route::put('pasajeros/{id}/menor/permiso', [PasajeroController::class, 'actualizarPermiso'])->whereNumber('id');

    Route::get('rutas', [RutaController::class, 'index']);
    Route::post('rutas', [RutaController::class, 'store'])->middleware('role:administrador,supervisor');
    Route::put('rutas/{id}', [RutaController::class, 'update'])->whereNumber('id')->middleware('role:administrador,supervisor');
    Route::get('buses', [BusController::class, 'index']);
    Route::post('buses', [BusController::class, 'store'])->middleware('role:administrador,supervisor');
    Route::put('buses/{id}', [BusController::class, 'update'])->whereNumber('id')->middleware('role:administrador,supervisor');
    Route::get('buses/{id}/croquis', [BusController::class, 'croquis'])->whereNumber('id');
    Route::get('conductores', [ViajeController::class, 'conductores']);

    Route::get('viajes', [ViajeController::class, 'index']);
    Route::post('viajes', [ViajeController::class, 'store'])->middleware('role:vendedor,supervisor,administrador');
    Route::get('viajes/{id}', [ViajeController::class, 'show'])->whereNumber('id');
    Route::put('viajes/{id}/estado', [ViajeController::class, 'updateEstado'])->whereNumber('id')->middleware('role:vendedor,supervisor,administrador');
    Route::get('viajes/{id}/asientos', [ViajeController::class, 'asientos'])->whereNumber('id');
    Route::get('viajes/{id}/manifiesto', [ViajeController::class, 'manifiesto'])->whereNumber('id');
    Route::get('viajes/{id}/boletos', [ViajeController::class, 'boletos'])->whereNumber('id');
    Route::post('viajes/del-dia', [ViajeController::class, 'delDia']);
    Route::get('viajes/horas-disponibles', [ViajeController::class, 'horasDisponibles']);

    Route::post('asientos/bloquear', [AsientoController::class, 'bloquear'])->middleware('role:vendedor,supervisor,administrador');
    Route::post('asientos/liberar', [AsientoController::class, 'liberar'])->middleware('role:vendedor,supervisor,administrador');
    Route::get('viajes/{id}/asientos/disponibles', [AsientoController::class, 'disponibles'])->whereNumber('id');

    Route::post('boletos', [BoletoController::class, 'store'])->middleware('role:vendedor,supervisor,administrador');
    Route::get('boletos/{id}', [BoletoController::class, 'show'])->whereNumber('id');
    Route::get('boletos/{codigo}', [BoletoController::class, 'showByCodigo']);
    Route::put('boletos/{id}/cancelar', [BoletoController::class, 'cancelar'])->whereNumber('id');
    Route::get('boletos/{id}/qr', [BoletoController::class, 'qr'])->whereNumber('id');
    Route::post('boletos/{id}/reemitir', [BoletoController::class, 'reemitir'])->whereNumber('id');

    Route::post('abordaje/validar-qr', [AbordajeController::class, 'validarQr'])->middleware('role:auxiliar,supervisor,administrador');
    Route::post('abordaje/validar-huella', [AbordajeController::class, 'validarHuella'])->middleware('role:auxiliar,supervisor,administrador');
    Route::post('abordaje/validar-qr-huella', [AbordajeController::class, 'validarQrHuella'])->middleware('role:auxiliar,supervisor,administrador');
    Route::get('abordaje/viaje/{id}/pendientes', [AbordajeController::class, 'pendientes'])->whereNumber('id');
    Route::get('abordaje/viaje/{id}/abordados', [AbordajeController::class, 'abordados'])->whereNumber('id');
    Route::get('abordaje/eventos/{viaje_id}', [AbordajeController::class, 'eventos'])->whereNumber('viaje_id');

    Route::post('gps/ubicacion', [GpsController::class, 'recibirUbicacion'])->middleware('auth:api');
    Route::post('gps/simular', [GpsController::class, 'simular'])->middleware('role:supervisor,administrador');
    Route::post('gps/viaje/{id}/iniciar', [GpsController::class, 'iniciarViaje'])->whereNumber('id')->middleware('auth:api', 'role:vendedor,supervisor,administrador');
    Route::post('gps/viaje/{id}/avanzar', [GpsController::class, 'avanzarSimulacion'])->whereNumber('id')->middleware('auth:api');
    Route::get('gps/viaje/{id}/estado', [GpsController::class, 'estadoSimulacion'])->whereNumber('id')->middleware('auth:api');
    Route::get('gps/buses/activos', [GpsController::class, 'busesActivos'])->middleware('auth:api');
    Route::get('gps/bus/{id}/ruta', [GpsController::class, 'ruta'])->whereNumber('id')->middleware('auth:api');
    Route::get('gps/viaje/{id}/timeline', [GpsController::class, 'timeline'])->whereNumber('id')->middleware('auth:api');

    Route::get('reportes/ventas-diarias', [ReporteController::class, 'ventasDiarias'])->middleware('role:supervisor,administrador');
    Route::get('reportes/ocupacion-por-ruta', [ReporteController::class, 'ocupacionPorRuta'])->middleware('role:supervisor,administrador');
    Route::get('reportes/abordajes-por-viaje', [ReporteController::class, 'abordajesPorViaje'])->middleware('role:supervisor,administrador');
    Route::get('reportes/auditoria', [ReporteController::class, 'auditoria'])->middleware('role:administrador');
    Route::get('reportes/ingresos', [ReporteController::class, 'ingresos'])->middleware('role:supervisor,administrador');
    Route::apiResource('usuarios', UsuarioController::class)->except(['destroy'])->middleware('role:administrador');
    Route::delete('usuarios/{id}', [UsuarioController::class, 'destroy'])->whereNumber('id')->middleware('role:administrador');
});
