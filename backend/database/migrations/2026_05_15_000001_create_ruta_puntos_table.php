<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ruta_puntos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ruta_id');
            $table->unsignedInteger('orden');
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->string('nombre', 100)->nullable();
            $table->foreign('ruta_id')->references('id')->on('rutas')->onDelete('cascade');
            $table->index(['ruta_id', 'orden']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruta_puntos');
    }
};