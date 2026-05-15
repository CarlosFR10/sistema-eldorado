<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ubicaciones_gps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('bus_id');
            $table->unsignedBigInteger('viaje_id')->nullable();
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->decimal('velocidad', 5, 1)->nullable()->comment('km/h');
            $table->decimal('rumbo', 5, 1)->nullable()->comment('Grados 0-359');
            $table->decimal('altitud', 8, 1)->nullable()->comment('Metros sobre el nivel del mar');
            $table->decimal('precision_m', 5, 1)->nullable()->comment('Margen de error en metros');
            $table->timestamp('timestamp')->useCurrent();
            $table->foreign('bus_id')->references('id')->on('buses');
            $table->foreign('viaje_id')->references('id')->on('viajes');
            $table->index(['bus_id', 'timestamp'], 'idx_bus_time');
            $table->index('viaje_id', 'idx_ubicaciones_viaje');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_gps');
    }
};
