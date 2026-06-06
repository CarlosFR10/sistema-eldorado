<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\AsientoActualizado;
use App\Models\Asiento;
use App\Models\Boleto;
use App\Models\MenorAdultoResponsable;
use App\Models\Pasajero;
use App\Models\Viaje;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BoletoService
{
    public function __construct(private readonly QrService $qrService)
    {
    }

    public function emitirBoleto(int $viajeId, int $asientoId, int $pasajeroId, string $metodoPago = 'efectivo', ?int $adultoResponsableId = null): Boleto
    {
        return $this->emitir(
            Viaje::findOrFail($viajeId),
            Asiento::findOrFail($asientoId),
            Pasajero::findOrFail($pasajeroId),
            $metodoPago,
            $adultoResponsableId
        );
    }

    public function obtenerBoleto(int|string $id): ?Boleto
    {
        return Boleto::with(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento', 'adultoResponsable'])
            ->where('id', $id)
            ->orWhere('codigo_boleto', $id)
            ->first();
    }

    public function emitir(Viaje $viaje, Asiento $asiento, Pasajero $pasajero, string $metodoPago, ?int $adultoResponsableId = null): Boleto
    {
        return DB::transaction(function () use ($viaje, $asiento, $pasajero, $metodoPago, $adultoResponsableId): Boleto {
            $viaje = Viaje::whereKey($viaje->id)->lockForUpdate()->firstOrFail();
            $asiento = Asiento::whereKey($asiento->id)->lockForUpdate()->firstOrFail();

            if ($viaje->estado !== 'en_venta') {
                throw new \RuntimeException('Solo se pueden emitir boletos para viajes en venta.');
            }

            if ((int) $asiento->viaje_id !== (int) $viaje->id) {
                throw new \RuntimeException('El asiento no pertenece al viaje seleccionado.');
            }

            if (!in_array($asiento->estado, ['disponible', 'bloqueado'], true)) {
                throw new \RuntimeException('No se puede emitir el boleto. El asiento ya fue vendido.');
            }

            $edad = $pasajero->edad;
            $esMenor = $edad < 18;

            if ($esMenor) {
                $this->validarAdultoResponsable($pasajero, $adultoResponsableId);
            }

            $descuento = $edad >= 60 ? 20.00 : 0.00;
            $precio = (float) $viaje->precio_final;
            $precioFinal = round($precio * (1 - ($descuento / 100)), 2);
            $codigo = $this->generarCodigo();

            $boleto = Boleto::create([
                'viaje_id' => $viaje->id,
                'asiento_id' => $asiento->id,
                'pasajero_id' => $pasajero->id,
                'vendedor_id' => auth()->id() ?: $viaje->vendedor_id,
                'precio' => $precio,
                'descuento' => $descuento,
                'precio_final' => $precioFinal,
                'metodo_pago' => $metodoPago,
                'estado' => 'pagado',
                'codigo_boleto' => $codigo,
                'fecha_emision' => now(),
                'fecha_vencimiento' => $viaje->fecha_salida?->copy()->addHours(2) ?: now()->addHours(2),
                'qr_payload' => '',
                'qr_imagen' => null,
                'es_menor' => $esMenor,
                'adulto_resp_id' => $adultoResponsableId,
            ]);

            $qr = $this->qrService->generarQrBoleto([
                'boleto_id' => $boleto->id,
                'codigo_boleto' => $boleto->codigo_boleto,
                'codigo_viaje' => $viaje->codigo_viaje,
                'viaje_id' => $viaje->id,
                'asiento' => $asiento->numero,
                'pasajero_ci' => $pasajero->numero_ci,
                'fecha_salida' => optional($viaje->fecha_salida)->toIso8601String(),
            ]);

            $boleto->update([
                'qr_payload' => $qr['payload'],
                'qr_imagen' => $qr['imagen'],
            ]);

            $asiento->update([
                'estado' => 'reservado',
                'bloqueado_hasta' => null,
            ]);

            Cache::put("asiento:{$viaje->id}:{$asiento->numero}", 'reservado', 3600);
            event(new AsientoActualizado((int) $viaje->id, (int) $asiento->numero, 'reservado'));

            return $boleto->fresh(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento', 'adultoResponsable']);
        });
    }

    public function cancelar(Boleto $boleto): Boleto
    {
        return DB::transaction(function () use ($boleto): Boleto {
            $boleto = Boleto::whereKey($boleto->id)->lockForUpdate()->firstOrFail();

            if (in_array($boleto->estado, ['abordado', 'cancelado', 'reembolsado'], true)) {
                throw new \RuntimeException('El boleto no puede cancelarse desde su estado actual.');
            }

            $boleto->update(['estado' => 'cancelado']);
            $boleto->asiento?->update([
                'estado' => 'disponible',
                'bloqueado_hasta' => null,
            ]);

            if ($boleto->asiento) {
                Cache::forget("asiento:{$boleto->viaje_id}:{$boleto->asiento->numero}");
                event(new AsientoActualizado((int) $boleto->viaje_id, (int) $boleto->asiento->numero, 'disponible'));
            }

            return $boleto->fresh(['viaje', 'pasajero', 'asiento']);
        });
    }

    public function reemitirQr(Boleto $boleto): Boleto
    {
        $qr = $this->qrService->generarQrBoleto([
            'boleto_id' => $boleto->id,
            'codigo_boleto' => $boleto->codigo_boleto,
            'viaje_id' => $boleto->viaje_id,
            'asiento' => $boleto->asiento?->numero,
            'pasajero_ci' => $boleto->pasajero?->numero_ci,
            'fecha_salida' => optional($boleto->viaje?->fecha_salida)->toIso8601String(),
        ]);

        $boleto->update([
            'qr_payload' => $qr['payload'],
            'qr_imagen' => $qr['imagen'],
        ]);

        return $boleto->fresh(['viaje', 'pasajero', 'asiento']);
    }

    private function validarAdultoResponsable(Pasajero $menor, ?int $adultoResponsableId): void
    {
        if ($adultoResponsableId === null) {
            throw new \RuntimeException('Menor requiere adulto responsable registrado (Ley 548 Bolivia)');
        }

        $existeRelacion = MenorAdultoResponsable::where('menor_id', $menor->id)
            ->where('adulto_responsable_id', $adultoResponsableId)
            ->exists();

        if (!$existeRelacion) {
            throw new \RuntimeException('Menor requiere adulto responsable registrado (Ley 548 Bolivia)');
        }
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = 'BLT-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4)) . '-' . Str::upper(Str::random(4));
        } while (Boleto::where('codigo_boleto', $codigo)->exists());

        return $codigo;
    }
}