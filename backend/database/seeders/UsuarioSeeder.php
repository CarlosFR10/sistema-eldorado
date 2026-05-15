<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['nombre' => 'Admin Sistema', 'email' => 'admin@eldorado.bo', 'rol' => 'administrador', 'turno' => null],
            ['nombre' => 'Carlos Supervisor', 'email' => 'supervisor@eldorado.bo', 'rol' => 'supervisor', 'turno' => 'manana'],
            ['nombre' => 'Maria Vendedora', 'email' => 'vendedor1@eldorado.bo', 'rol' => 'vendedor', 'turno' => 'manana'],
            ['nombre' => 'Juan Auxiliar', 'email' => 'auxiliar1@eldorado.bo', 'rol' => 'auxiliar', 'turno' => 'tarde'],
            ['nombre' => 'Roberto Conductor', 'email' => 'conductor1@eldorado.bo', 'rol' => 'conductor', 'turno' => 'noche'],
            ['nombre' => 'Luis Conductor', 'email' => 'conductor2@eldorado.bo', 'rol' => 'conductor', 'turno' => 'manana'],
            ['nombre' => 'ATT Autoridad', 'email' => 'autoridad@att.gob.bo', 'rol' => 'autoridad', 'turno' => null],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('usuarios')->updateOrInsert(
                ['email' => $usuario['email']],
                [
                    'nombre' => $usuario['nombre'],
                    'password' => Hash::make('Eldorado2026!'),
                    'rol' => $usuario['rol'],
                    'turno' => $usuario['turno'],
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
