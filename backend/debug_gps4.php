<?php
require '/var/www/backend/vendor/autoload.php';
$app = require '/var/www/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$viaje = \App\Models\Viaje::find(228);

echo "raw simulacion_inicio: " . var_export($viaje->getRawOriginal('simulacion_inicio'), true) . PHP_EOL;
echo "simulacion_inicio class: " . get_class($viaje->simulacion_inicio) . PHP_EOL;
echo "simulacion_inicio: " . $viaje->simulacion_inicio . PHP_EOL;
echo "simulacion_inicio timezone: " . $viaje->simulacion_inicio->timezone . PHP_EOL;