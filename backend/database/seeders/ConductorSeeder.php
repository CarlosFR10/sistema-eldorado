<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConductorSeeder extends Seeder
{
    public function run(): void
    {
        $conductores = [
            ['email' => 'conductor1@eldorado.bo', 'licencia' => 'CBBA-778899', 'categoria' => 'C'],
            ['email' => 'conductor2@eldorado.bo', 'licencia' => 'CBBA-112233', 'categoria' => 'C'],
        ];

        foreach ($conductores as $conductor) {
            $usuarioId = DB::table('usuarios')->where('email', $conductor['email'])->value('id');

            DB::table('conductores')->updateOrInsert(
                ['licencia' => $conductor['licencia']],
                [
                    'usuario_id' => $usuarioId,
                    'categoria' => $conductor['categoria'],
                    'vencimiento_lic' => now()->addYears(3)->toDateString(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
