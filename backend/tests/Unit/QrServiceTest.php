<?php

declare(strict_types=1);

use App\Services\QrService;

test('genera y verifica payload de boleto', function (): void {
    $qr = app(QrService::class)->generarQrBoleto(['codigo_boleto' => 'BLT-TEST']);
    $payload = app(QrService::class)->decodificarPayload($qr['payload']);

    expect($payload['codigo_boleto'])->toBe('BLT-TEST')
        ->and($qr['imagen'])->not()->toBeEmpty();
});
