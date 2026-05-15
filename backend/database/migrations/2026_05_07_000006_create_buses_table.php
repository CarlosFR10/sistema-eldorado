<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buses', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('placa', 10)->unique()->comment('Placa del bus');
            $table->string('marca', 50)->comment('Marca del fabricante');
            $table->string('modelo', 50)->comment('Modelo comercial');
            $table->year('anio')->comment('Anio de fabricacion del bus');
            $table->tinyInteger('capacidad')->comment('Numero total de asientos');
            $table->enum('tipo_bus', ['semicama', 'cama_completa', 'ejecutivo', 'doble_piso'])->comment('Tipo operativo del bus');
            $table->json('config_asientos')->comment('Layout del croquis: filas, columnas, pasillo y especiales');
            $table->string('gps_imei', 20)->nullable()->comment('IMEI del dispositivo GPS instalado');
            $table->boolean('activo')->default(true)->comment('Indica si el bus esta habilitado');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
