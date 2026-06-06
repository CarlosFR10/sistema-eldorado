<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AsientoActualizado;
use App\Events\PasajeroAbordado;
use App\Models\Boleto;
use App\Models\EventoAbordaje;
use Illuminate\Support\Facades\DB;

class AbordajeService
{
    public function __construct(private readonly BiometriaService $biometriaService)
    {
    }

    public function validarQr(Boleto $boleto, int $operadorId, ?float $latitud = null, ?float $longitud = null): EventoAbordaje
    {
        return $this->aprobar($boleto, $operadorId, 'qr', $latitud, $longitud);
    }

    public function validarHuella(Boleto $boleto, string $plantilla, int $operadorId, ?float $latitud = null, ?float $longitud = null): EventoAbordaje
    {
        if (!$this->biometriaService->verificarHuella((int) $boleto->pasajero_id, $plantilla)) {
            return $this->rechazar($boleto, $operadorId, 'huella', 'rechazado_huella', $latitud, $longitud);
        }

        return $this->aprobar($boleto, $operadorId, 'huella', $latitud, $longitud);
    }

    public function validarQrHuella(Boleto $boleto, string $plantilla, int $operadorId, ?float $latitud = null, ?float $longitud = null): EventoAbordaje
    {
        if (!$this->biometriaService->verificarHuella((int) $boleto->pasajero_id, $plantilla)) {
            return $this->rechazar($boleto, $operadorId, 'qr_huella', 'rechazado_huella', $latitud, $longitud);
        }

        return $this->aprobar($boleto, $operadorId, 'qr_huella', $latitud, $longitud);
    }

    private function aprobar(Boleto $boleto, int $operadorId, string $tipoValidacion, ?float $latitud, ?float $longitud): EventoAbordaje
    {
        return DB::transaction(function () use ($boleto, $operadorId, $tipoValidacion, $latitud, $longitud): EventoAbordaje {
            $boleto = Boleto::with(['viaje', 'asiento', 'pasajero'])
                ->whereKey($boleto->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($boleto->viaje->estado !== 'abordando') {
                return $this->rechazar($boleto, $operadorId, $tipoValidacion, 'rechazado_qr', $latitud, $longitud);
            }

            if ($boleto->estado !== 'pagado') {
                return $this->rechazar($boleto, $operadorId, $tipoValidacion, 'rechazado_qr', $latitud, $longitud);
            }

            if (EventoAbordaje::where('boleto_id', $boleto->id)->where('resultado', 'aprobado')->exists()) {
                return $this->rechazar($boleto, $operadorId, $tipoValidacion, 'rechazado_duplicado', $latitud, $longitud);
            }

            if ($boleto->es_menor && !$this->adultoPresente($boleto)) {
                return $this->rechazar($boleto, $operadorId, $tipoValidacion, 'rechazado_menor_sin_adulto', $latitud, $longitud);
            }

            $boleto->update(['estado' => 'abordado']);
            $boleto->asiento?->update(['estado' => 'ocupado']);
            event(new AsientoActualizado((int) $boleto->viaje_id, (int) $boleto->asiento->numero, 'ocupado'));

            $totalBoletos = Boleto::where('viaje_id', $boleto->viaje_id)->whereIn('estado', ['pagado', 'abordado'])->count();
            $totalAbordados = Boleto::where('viaje_id', $boleto->viaje_id)->where('estado', 'abordado')->count();
            event(new PasajeroAbordado(
                (int) $boleto->viaje_id,
                trim(($boleto->pasajero->nombres ?? '') . ' ' . ($boleto->pasajero->apellidos ?? '')),
                (int) $boleto->asiento->numero,
                $totalAbordados,
                $totalBoletos
            ));

            return EventoAbordaje::create([
                'boleto_id' => $boleto->id,
                'viaje_id' => $boleto->viaje_id,
                'pasajero_id' => $boleto->pasajero_id,
                'operador_id' => $operadorId,
                'tipo_validacion' => $tipoValidacion,
                'resultado' => 'aprobado',
                'ip_dispositivo' => request()->ip() ?: '127.0.0.1',
                'latitud' => $latitud,
                'longitud' => $longitud,
            ]);
        });
    }

    private function rechazar(Boleto $boleto, int $operadorId, string $tipoValidacion, string $resultado, ?float $latitud, ?float $longitud): EventoAbordaje
    {
        return EventoAbordaje::create([
            'boleto_id' => $boleto->id,
            'viaje_id' => $boleto->viaje_id,
            'pasajero_id' => $boleto->pasajero_id,
            'operador_id' => $operadorId,
            'tipo_validacion' => $tipoValidacion,
            'resultado' => $resultado,
            'ip_dispositivo' => request()->ip() ?: '127.0.0.1',
            'latitud' => $latitud,
            'longitud' => $longitud,
        ]);
    }

    private function adultoPresente(Boleto $boleto): bool
    {
        if (!$boleto->adulto_resp_id) {
            return false;
        }

        return Boleto::where('viaje_id', $boleto->viaje_id)
            ->where('pasajero_id', $boleto->adulto_resp_id)
            ->whereIn('estado', ['pagado', 'abordado'])
            ->exists();
    }
}