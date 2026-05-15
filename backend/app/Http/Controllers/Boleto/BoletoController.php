<?php

declare(strict_types=1);

namespace App\Http\Controllers\Boleto;

use App\Http\Controllers\Controller;
use App\Models\Asiento;
use App\Models\Boleto;
use App\Models\MenorAdultoResponsable;
use App\Models\Pasajero;
use App\Models\Viaje;
use App\Services\BoletoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BoletoController extends Controller
{
    public function __construct(private readonly BoletoService $boletoService)
    {
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'viaje_id' => ['required', 'integer', 'exists:viajes,id'],
            'asiento_id' => ['required', 'integer', 'exists:asientos,id'],
            'pasajero_id' => ['required', 'integer', 'exists:pasajeros,id'],
            'adulto_resp_id' => ['nullable', 'integer', 'exists:pasajeros,id'],
            'metodo_pago' => ['required', Rule::in(['efectivo', 'qr_bancario', 'tarjeta'])],
        ]);

        try {
            $boleto = $this->boletoService->emitir(
                Viaje::findOrFail($data['viaje_id']),
                Asiento::findOrFail($data['asiento_id']),
                Pasajero::findOrFail($data['pasajero_id']),
                $data['metodo_pago'],
                $data['adulto_resp_id'] ?? null
            );

            return $this->success($boleto, 'Boleto emitido correctamente.', 201);
        } catch (\RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 'BOLETO_NO_EMITIDO', 409);
        }
    }

    public function publicReservar(Request $request)
    {
        $data = $request->validate([
            'viaje_id' => ['required', 'integer', 'exists:viajes,id'],
            'metodo_pago' => ['required', Rule::in(['efectivo', 'qr_bancario', 'tarjeta'])],
            'pasajes' => ['required', 'array', 'min:1', 'max:10'],
            'pasajes.*.pasajero_id' => ['required', 'integer', 'exists:pasajeros,id'],
            'pasajes.*.asiento_id' => ['required', 'integer', 'exists:asientos,id'],
            'pasajes.*.adulto_resp_id' => ['nullable', 'integer', 'exists:pasajeros,id'],
        ]);

        try {
            $boletos = DB::transaction(function () use ($data): array {
                $viaje = Viaje::findOrFail($data['viaje_id']);
                $emitidos = [];

                foreach ($data['pasajes'] as $item) {
                    $pasajero = Pasajero::findOrFail($item['pasajero_id']);
                    $adultoResponsableId = $item['adulto_resp_id'] ?? null;

                    if ($pasajero->edad < 18 && $adultoResponsableId) {
                        MenorAdultoResponsable::firstOrCreate(
                            [
                                'menor_id' => $pasajero->id,
                                'adulto_responsable_id' => $adultoResponsableId,
                            ],
                            [
                                'tipo_relacion' => 'acompanante_autorizado',
                                'verificado_manualmente' => false,
                                'observaciones' => 'Vinculo declarado en compra publica.',
                            ]
                        );
                    }

                    $boleto = $this->boletoService->emitir(
                        $viaje,
                        Asiento::findOrFail($item['asiento_id']),
                        $pasajero,
                        $data['metodo_pago'],
                        $adultoResponsableId
                    );

                    if (!$pasajero->tiene_huella) {
                        $boleto->update(['estado' => 'pendiente_verificacion']);
                    }

                    $emitidos[] = $boleto->fresh(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento', 'adultoResponsable']);
                }

                return $emitidos;
            });

            return $this->success([
                'boletos' => $boletos,
                'requiere_verificacion_huella' => collect($boletos)->contains(fn (Boleto $boleto) => $boleto->estado === 'pendiente_verificacion'),
            ], 'Reserva generada correctamente.', 201);
        } catch (\RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 'RESERVA_NO_GENERADA', 409);
        }
    }

    public function show(int $id)
    {
        return $this->success(
            Boleto::with(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento', 'adultoResponsable'])->findOrFail($id),
            'Detalle del boleto.'
        );
    }

    public function showByCodigo(string $codigo)
    {
        $boleto = Boleto::with(['viaje.ruta', 'viaje.bus', 'pasajero', 'asiento', 'adultoResponsable'])
            ->where('codigo_boleto', $codigo)
            ->firstOrFail();

        return $this->success($boleto, 'Boleto encontrado.');
    }

    public function cancelar(int $id)
    {
        $boleto = Boleto::findOrFail($id);

        try {
            return $this->success($this->boletoService->cancelar($boleto), 'Boleto cancelado correctamente.');
        } catch (\RuntimeException $exception) {
            return $this->error($exception->getMessage(), [], 'BOLETO_NO_CANCELABLE', 409);
        }
    }

    public function qr(int $id)
    {
        $boleto = Boleto::findOrFail($id);

        return $this->success([
            'codigo_boleto' => $boleto->codigo_boleto,
            'qr_payload' => $boleto->qr_payload,
            'qr_imagen' => $boleto->qr_imagen,
        ], 'QR del boleto.');
    }

    public function reemitir(int $id)
    {
        return $this->success(
            $this->boletoService->reemitirQr(Boleto::with(['viaje', 'pasajero', 'asiento'])->findOrFail($id)),
            'QR reemitido correctamente.'
        );
    }
}
