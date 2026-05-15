<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->string('nombre', 100)->comment('Nombre completo del operador');
            $table->string('email', 150)->unique()->comment('Correo de acceso');
            $table->string('password', 255)->comment('Bcrypt hash, nunca texto plano');
            $table->enum('rol', ['administrador', 'supervisor', 'vendedor', 'auxiliar', 'conductor', 'autoridad'])->comment('Rol RBAC del usuario');
            $table->enum('turno', ['manana', 'tarde', 'noche'])->nullable()->comment('Turno operativo');
            $table->boolean('activo')->default(true)->comment('Usuario habilitado');
            $table->timestamp('ultimo_acceso')->nullable()->comment('Ultimo inicio de sesion');
            $table->string('token_2fa', 6)->nullable()->comment('OTP para autenticacion de dos factores');
            $table->timestamp('expires_2fa')->nullable()->comment('Fecha de expiracion OTP');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
