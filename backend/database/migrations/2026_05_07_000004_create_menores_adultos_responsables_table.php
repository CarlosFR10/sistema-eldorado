<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menores_adultos_responsables', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('menor_id')->comment('Pasajero menor de edad');
            $table->unsignedBigInteger('adulto_responsable_id')->comment('Pasajero adulto responsable');
            $table->enum('tipo_relacion', ['padre', 'madre', 'tutor_legal', 'acompanante_autorizado'])->comment('Relacion declarada');
            $table->string('numero_permiso_dna', 50)->nullable()->comment('Nro. permiso Defensoria Ninez y Adolescencia');
            $table->date('fecha_permiso')->nullable()->comment('Fecha del permiso DNA');
            $table->boolean('verificado_manualmente')->default(false)->comment('Verificacion documental manual');
            $table->unsignedBigInteger('verificado_por')->nullable()->comment('Usuario que verifico');
            $table->text('observaciones')->nullable()->comment('Observaciones de la verificacion');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('menor_id')->references('id')->on('pasajeros');
            $table->foreign('adulto_responsable_id')->references('id')->on('pasajeros');
            $table->foreign('verificado_por')->references('id')->on('usuarios');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menores_adultos_responsables');
    }
};
