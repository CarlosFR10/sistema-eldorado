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

        DB::statement("ALTER TABLE boletos MODIFY estado ENUM('pendiente_pago','pendiente_verificacion','pagado','abordado','cancelado','reembolsado') NOT NULL DEFAULT 'pendiente_pago'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE boletos MODIFY estado ENUM('pendiente_pago','pagado','abordado','cancelado','reembolsado') NOT NULL DEFAULT 'pendiente_pago'");
    }
};
