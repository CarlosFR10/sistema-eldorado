<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pasajeros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombres', 100);
            $table->string('apellidos', 100);
            $table->string('numero_ci', 20)->unique()->comment('Cédula de identidad boliviana');
            $table->string('complemento_ci', 5)->nullable()->comment('Complemento CI (ej: 1A)');
            $table->char('expedido_en', 2)->comment('Departamento emisor del CI: LP, CB, SC, etc.');
            $table->date('fecha_nacimiento');
            $table->boolean('es_menor')->default(false)->comment('Indicador persistido; se recalcula desde fecha_nacimiento en la aplicación');
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('tiene_huella')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('numero_ci', 'idx_ci');
            $table->index(['apellidos', 'nombres'], 'idx_nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasajeros');
    }
};
