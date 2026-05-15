<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rutas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo', 20)->unique()->comment('Ej: CBB-LPZ');
            $table->string('origen', 100);
            $table->string('destino', 100);
            $table->decimal('distancia_km', 8, 2);
            $table->decimal('duracion_horas', 4, 1);
            $table->decimal('precio_base', 10, 2);
            $table->boolean('activa')->default(true);
            $table->json('paradas')->nullable()->comment('Array de ciudades intermedias: ["Oruro","La Paz"]');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rutas');
    }
};
