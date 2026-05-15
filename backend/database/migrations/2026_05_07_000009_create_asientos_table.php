<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asientos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('viaje_id');
            $table->tinyInteger('numero')->comment('Número del asiento 1-N');
            $table->tinyInteger('fila');
            $table->tinyInteger('columna');
            $table->tinyInteger('piso')->default(1);
            $table->enum('tipo', ['normal','preferencial','discapacidad'])->default('normal');
            $table->enum('estado', ['disponible','bloqueado','reservado','ocupado'])->default('disponible');
            $table->timestamp('bloqueado_hasta')->nullable()->comment('Se libera si no se paga en 10 min');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('viaje_id')->references('id')->on('viajes')->onDelete('cascade');
            $table->unique(['viaje_id', 'numero'], 'uk_asiento_viaje');
            $table->index('estado', 'idx_asientos_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asientos');
    }
};
