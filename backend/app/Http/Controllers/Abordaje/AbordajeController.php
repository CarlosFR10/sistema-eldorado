<?php

declare(strict_types=1);

namespace App\Http\Controllers\Abordaje;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use App\Models\EventoAbordaje;
use App\Services\AbordajeService;
use App\Services\QrService;
use Illuminate\Http\Request;

class AbordajeController extends Controller
{
    public function __construct(
        private readonly AbordajeService $abordajeService,
        private readonly QrService $qrService
    ) {
    }

    public function validarQr(Request $request)
    {
        $data = $request->validate([
            'codigo_boleto' => ['nullable', 'string'],
            'qr_payload' => ['nullable', 'string'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ]);

        if (empty($data['codigo_boleto']) && empty($data['qr_payload'])) {
            return $this->error('Debe enviar codigo de boleto o payload QR.', [], 'QR_REQUERIDO', 422);
        }

        $codigo = $data['codigo_boleto'] ?? $this->qrService->decodificarPayload($data['qr_payload'])['codigo_boleto'];
        $boleto = Boleto::where('codigo_boleto', $codigo)->firstOrFail();
        $evento = $this->abordajeService->validarQr($boleto, auth()->id() ?: 1, $data['latitud'] ?? null, $data['longitud'] ?? null);

        return $this->respuestaEvento($evento);
    }

    public function validarHuella(Request $request)
    {
        $data = $request->validate([
            'boleto_id' => ['required', 'integer', 'exists:boletos,id'],
            'plantilla' => ['required', 'string'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ]);

        $evento = $this->abordajeService->validarHuella(
            Boleto::findOrFail($data['boleto_id']),
            $data['plantilla'],
            auth()->id() ?: 1,
            $data['latitud'] ?? null,
            $data['longitud'] ?? null
        );

        return $this->respuestaEvento($evento);
    }

    public function validarQrHuella(Request $request)
    {
        $data = $request->validate([
            'codigo_boleto' => ['required', 'string'],
            'plantilla' => ['required', 'string'],
            'latitud' => ['nullable', 'numeric'],
            'longitud' => ['nullable', 'numeric'],
        ]);

        $evento = $this->abordajeService->validarQrHuella(
            Boleto::where('codigo_boleto', $data['codigo_boleto'])->firstOrFail(),
            $data['plantilla'],
            auth()->id() ?: 1,
            $data['latitud'] ?? null,
            $data['longitud'] ?? null
        );

        return $this->respuestaEvento($evento);
    }

    public function pendientes(int $id)
    {
        $boletos = Boleto::where('viaje_id', $id)
            ->where('estado', 'pagado')
            ->with(['pasajero', 'asiento'])
            ->orderBy('id')
            ->get();

        return $this->success($boletos, 'Pasajeros pendientes de abordaje.');
    }

    public function abordados(int $id)
    {
        $boletos = Boleto::where('viaje_id', $id)
            ->where('estado', 'abordado')
            ->with(['pasajero', 'asiento'])
            ->orderBy('id')
            ->get();

        return $this->success($boletos, 'Pasajeros abordados.');
    }

    public function eventos(int $viajeId)
    {
        $eventos = EventoAbordaje::where('viaje_id', $viajeId)
            ->with(['pasajero', 'boleto.asiento', 'operador'])
            ->latest('id')
            ->paginate(50);

        return $this->success($eventos, 'Eventos de abordaje.');
    }

    private function respuestaEvento(EventoAbordaje $evento)
    {
        $evento->load(['pasajero', 'boleto.asiento', 'boleto.viaje']);

        if ($evento->resultado !== 'aprobado') {
            return $this->error('Abordaje rechazado.', ['resultado' => [$evento->resultado]], $evento->resultado, 403);
        }

        return $this->success($evento, 'Abordaje aprobado.');
    }
}
