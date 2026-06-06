<?php
require '/var/www/backend/vendor/autoload.php';
$app = require '/var/www/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viaje = \App\Models\Viaje::with('ruta')->find(228);
$inicio = \Carbon\Carbon::parse($viaje->simulacion_inicio, 'UTC');
$now = \Carbon\Carbon::now('UTC');
$duracionMinutos = (int)($viaje->ruta->duracion_horas * 60);
$elapsed = (int) $inicio->diffInSeconds($now);
$totalSeconds = $duracionMinutos * 60;
$progreso = min(1.0, $elapsed / $totalSeconds) * 100;

echo "simulacion_inicio (raw): " . $viaje->simulacion_inicio . PHP_EOL;
echo "inicio (parsed UTC): " . $inicio->toDateTimeString() . PHP_EOL;
echo "now (UTC): " . $now->toDateTimeString() . PHP_EOL;
echo "duracionMinutos: " . $duracionMinutos . PHP_EOL;
echo "elapsed: " . $elapsed . PHP_EOL;
echo "totalSeconds: " . $totalSeconds . PHP_EOL;
echo "progreso: " . $progreso . PHP_EOL;