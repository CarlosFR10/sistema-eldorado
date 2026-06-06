<?php

declare(strict_types=1);

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrService
{
    public function generarQrBoleto(array $payload): array
    {
        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $firma = hash_hmac('sha256', $jsonPayload, (string) config('app.qr_secret', config('app.key')));
        $qrData = base64_encode(json_encode(['data' => $jsonPayload, 'sig' => $firma], JSON_THROW_ON_ERROR));

        $qrCode = QrCode::create($qrData);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return [
            'payload' => $qrData,
            'imagen' => base64_encode($result->getString()),
        ];
    }

    public function generarQrTexto(string $texto): string
    {
        $qrCode = QrCode::create($texto);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return base64_encode($result->getString());
    }

    public function decodificarPayload(string $qrPayload): array
    {
        $raw = base64_decode($qrPayload, true);

        if ($raw === false) {
            throw new \RuntimeException('El QR no tiene un formato valido.');
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        if (!$this->verificarFirma((string) $decoded['data'], (string) $decoded['sig'])) {
            throw new \RuntimeException('La firma del QR no es valida.');
        }

        return json_decode((string) $decoded['data'], true, 512, JSON_THROW_ON_ERROR);
    }

    public function verificarFirma(string $payload, string $firma): bool
    {
        $esperada = hash_hmac('sha256', $payload, (string) config('app.qr_secret', config('app.key')));

        return hash_equals($esperada, $firma);
    }
}