<?php
require '/var/www/backend/vendor/autoload.php';
$app = require '/var/www/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viaje = \App\Models\Viaje::find(228);
$inicioLocal = \Carbon\Carbon::parse($viaje->simulacion_inicio);
$inicio = $inicioLocal->copy()->addHours(4);
$now = \Carbon\Carbon::now('UTC');

echo "inicioLocal: " . $inicioLocal->format('Y-m-d H:i:s T') . PHP_EOL;
echo "inicio (UTC): " . $inicio->format('Y-m-d H:i:s T') . PHP_EOL;
echo "now (UTC): " . $now->format('Y-m-d H:i:s T') . PHP_EOL;
echo "inicio gt now: " . ($inicio->gt($now) ? 'yes' : 'no') . PHP_EOL;
echo "diffInSeconds: " . $inicio->diffInSeconds($now) . PHP_EOL;