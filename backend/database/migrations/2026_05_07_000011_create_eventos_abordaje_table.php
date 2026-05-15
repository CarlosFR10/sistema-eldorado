<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eventos_abordaje', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('boleto_id');
            $table->unsignedBigInteger('viaje_id');
            $table->unsignedBigInteger('pasajero_id');
            $table->unsignedBigInteger('operador_id')->comment('Auxiliar que registró el abordaje');
            $table->enum('tipo_validacion', ['qr','huella','qr_huella','manual']);
            $table->enum('resultado', ['aprobado','rechazado_qr','rechazado_huella','rechazado_duplicado','rechazado_menor_sin_adulto']);
            $table->string('ip_dispositivo', 45);
            $table->decimal('latitud', 10, 7)->nullable()->comment('GPS del punto de abordaje');
            $table->decimal('longitud', 10, 7)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('boleto_id')->references('id')->on('boletos');
            $table->foreign('viaje_id')->references('id')->on('viajes');
            $table->foreign('pasajero_id')->references('id')->on('pasajeros');
            $table->foreign('operador_id')->references('id')->on('usuarios');
            $table->index(['viaje_id', 'resultado'], 'idx_viaje_resultado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos_abordaje');
    }
};
