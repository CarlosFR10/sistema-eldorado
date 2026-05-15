<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('usuario_id')->nullable()->comment('NULL si es acción del sistema');
            $table->string('accion', 100)->comment('Ej: boleto.emitido, pasajero.registrado');
            $table->string('tabla_afectada', 50)->nullable();
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('datos_antes')->nullable()->comment('Estado previo para operaciones de UPDATE/DELETE');
            $table->json('datos_despues')->nullable()->comment('Estado nuevo');
            $table->string('ip', 45);
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('usuario_id', 'idx_usuario');
            $table->index('accion', 'idx_accion');
            $table->index('created_at', 'idx_audit_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
