<?php
require '/var/www/backend/vendor/autoload.php';
$app = require '/var/www/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Laravel now: " . now()->toDateTimeString() . PHP_EOL;
echo "UTC now: " . \Carbon\Carbon::now('UTC')->toDateTimeString() . PHP_EOL;
echo "Timezone: " . config('app.timezone') . PHP_EOL;