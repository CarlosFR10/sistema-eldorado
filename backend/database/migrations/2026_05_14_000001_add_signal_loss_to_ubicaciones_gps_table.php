<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ubicaciones_gps', function (Blueprint $table) {
            $table->boolean('signal_loss')->default(false)->after('precision_m');
        });
    }

    public function down(): void
    {
        Schema::table('ubicaciones_gps', function (Blueprint $table) {
            $table->dropColumn('signal_loss');
        });
    }
};