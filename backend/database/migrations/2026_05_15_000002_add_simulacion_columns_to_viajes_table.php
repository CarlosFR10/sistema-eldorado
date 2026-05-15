<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->unsignedTinyInteger('simulacion_llamada_actual')->default(0)->after('observaciones');
            $table->unsignedTinyInteger('simulacion_llamadas_totales')->default(30)->after('simulacion_llamada_actual');
            $table->float('simulacion_progreso')->default(0)->after('simulacion_llamadas_totales');
            $table->json('simulacion_waypoints')->nullable()->after('simulacion_progreso');
            $table->timestamp('simulacion_inicio')->nullable()->after('simulacion_waypoints');
        });
    }

    public function down(): void
    {
        Schema::table('viajes', function (Blueprint $table) {
            $table->dropColumn([
                'simulacion_llamada_actual',
                'simulacion_llamadas_totales',
                'simulacion_progreso',
                'simulacion_waypoints',
                'simulacion_inicio',
            ]);
        });
    }
};