<?php
require '/var/www/backend/vendor/autoload.php';
$app = require '/var/www/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viaje = \App\Models\Viaje::find(228);
$inicioLocalStr = $viaje->getRawOriginal('simulacion_inicio');
echo "Raw: " . $inicioLocalStr . PHP_EOL;
$inicio = \Carbon\Carbon::parse($inicioLocalStr . ' +00:00');
$now = \Carbon\Carbon::now('UTC');

echo "inicio: " . $inicio->format('Y-m-d H:i:s T') . PHP_EOL;
echo "now: " . $now->format('Y-m-d H:i:s T') . PHP_EOL;
echo "inicio gt now: " . ($inicio->gt($now) ? 'yes' : 'no') . PHP_EOL;
echo "diffInSeconds: " . $inicio->diffInSeconds($now) . PHP_EOL;