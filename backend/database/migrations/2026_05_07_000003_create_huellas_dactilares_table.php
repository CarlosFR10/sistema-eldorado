<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('huellas_dactilares', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pasajero_id');
            $table->longText('plantilla')->comment('Template biométrico en Base64 AES-256 cifrado');
            $table->enum('dedo', ['pulgar_der','indice_der','medio_der','pulgar_izq','indice_izq'])->default('indice_der');
            $table->tinyInteger('calidad')->comment('Score 0-100 de calidad de captura');
            $table->unsignedBigInteger('registrado_por')->comment('ID usuario vendedor que registró');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('pasajero_id')->references('id')->on('pasajeros')->onDelete('cascade');
            $table->foreign('registrado_por')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('huellas_dactilares');
    }
};
