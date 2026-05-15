<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('viajes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_viaje', 20)->unique()->comment('Ej: VJ-20260507-001 generado automáticamente');
            $table->unsignedBigInteger('ruta_id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('conductor_id');
            $table->unsignedBigInteger('vendedor_id')->comment('Usuario que creó el viaje');
            $table->dateTime('fecha_salida');
            $table->dateTime('fecha_llegada_est')->comment('Llegada estimada');
            $table->dateTime('fecha_llegada_real')->nullable();
            $table->decimal('precio_final', 10, 2);
            $table->enum('estado', ['programado','en_venta','abordando','en_ruta','completado','cancelado'])->default('programado');
            $table->text('observaciones')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('ruta_id')->references('id')->on('rutas');
            $table->foreign('bus_id')->references('id')->on('buses');
            $table->foreign('conductor_id')->references('id')->on('conductores');
            $table->foreign('vendedor_id')->references('id')->on('usuarios');
            $table->index('fecha_salida', 'idx_viajes_fecha');
            $table->index('estado', 'idx_viajes_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viajes');
    }
};
