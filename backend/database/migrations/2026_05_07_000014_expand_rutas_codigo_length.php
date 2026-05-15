<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE rutas MODIFY codigo VARCHAR(20) NOT NULL COMMENT 'Ej: CBB-LPZ'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE rutas MODIFY codigo VARCHAR(10) NOT NULL COMMENT 'Ej: CBB-LPZ'");
    }
};
