<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codigo_boleto', 30)->unique()->comment('UUID v4 generado: BLT-XXXX-XXXX-XXXX');
            $table->unsignedBigInteger('viaje_id');
            $table->unsignedBigInteger('pasajero_id');
            $table->unsignedBigInteger('asiento_id');
            $table->unsignedBigInteger('vendedor_id');
            $table->decimal('precio', 10, 2);
            $table->decimal('descuento', 5, 2)->default(0.00)->comment('Porcentaje descuento adulto mayor');
            $table->decimal('precio_final', 10, 2);
            $table->enum('metodo_pago', ['efectivo','qr_bancario','tarjeta'])->default('efectivo');
            $table->enum('estado', ['pendiente_pago','pendiente_verificacion','pagado','abordado','cancelado','reembolsado'])->default('pendiente_pago');
            $table->text('qr_payload')->comment('JSON firmado con datos del viaje para QR');
            $table->text('qr_imagen')->nullable()->comment('Base64 del QR generado');
            $table->boolean('es_menor')->default(false);
            $table->unsignedBigInteger('adulto_resp_id')->nullable()->comment('ID del pasajero adulto responsable si es menor');
            $table->timestamp('fecha_emision')->useCurrent();
            $table->timestamp('fecha_vencimiento');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->foreign('viaje_id')->references('id')->on('viajes');
            $table->foreign('pasajero_id')->references('id')->on('pasajeros');
            $table->foreign('asiento_id')->references('id')->on('asientos');
            $table->foreign('vendedor_id')->references('id')->on('usuarios');
            $table->foreign('adulto_resp_id')->references('id')->on('pasajeros');
            $table->index('codigo_boleto', 'idx_boletos_codigo');
            $table->index('viaje_id', 'idx_boletos_viaje');
            $table->index('estado', 'idx_boletos_estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
