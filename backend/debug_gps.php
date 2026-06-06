<?php
require '/var/www/backend/vendor/autoload.php';

$viaje = \App\Models\Viaje::with('ruta')->find(228);
$inicio = \Carbon\Carbon::parse($viaje->simulacion_inicio);
$now = \Carbon\Carbon::now();
$duracionMinutos = (int)($viaje->ruta->duracion_horas * 60);
$elapsed = $inicio->diffInSeconds($now);
$totalSeconds = $duracionMinutos * 60;
$progreso = min(1.0, $elapsed / $totalSeconds) * 100;

echo "inicio: " . $inicio->toDateTimeString() . PHP_EOL;
echo "now: " . $now->toDateTimeString() . PHP_EOL;
echo "duracionMinutos: " . $duracionMinutos . PHP_EOL;
echo "elapsed: " . $elapsed . PHP_EOL;
echo "totalSeconds: " . $totalSeconds . PHP_EOL;
echo "progreso: " . $progreso . PHP_EOL;