<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialAdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('rol')->where('nombre', 'administrador')->value('id');

        $personaId = DB::table('persona')->where('cedula_identidad', env('ADMIN_INITIAL_CI', '0000001'))->value('id');

        if (!$personaId) {
            $personaId = DB::table('persona')->insertGetId([
                'cedula_identidad' => env('ADMIN_INITIAL_CI', '0000001'),
                'nombres' => env('ADMIN_INITIAL_NAMES', 'Administrador'),
                'apellido_paterno' => env('ADMIN_INITIAL_LASTNAME', 'Inicial'),
                'apellido_materno' => env('ADMIN_INITIAL_SECOND_LASTNAME'),
                'correo' => env('ADMIN_INITIAL_EMAIL', 'admin@cupficct.local'),
                'celular' => env('ADMIN_INITIAL_PHONE'),
                'ciudad' => env('ADMIN_INITIAL_CITY', 'Santa Cruz'),
                'creado_en' => now(),
            ]);
        }

        $usuarioId = DB::table('usuario')->where('nombre_usuario', env('ADMIN_INITIAL_USERNAME', 'admin'))->value('id');

        if (!$usuarioId) {
            $usuarioId = DB::table('usuario')->insertGetId([
                'persona_id' => $personaId,
                'rol_id' => $adminRoleId,
                'nombre_usuario' => env('ADMIN_INITIAL_USERNAME', 'admin'),
                'correo_verificado' => true,
                'password_hash' => password_hash(env('ADMIN_INITIAL_PASSWORD', 'admin12345'), PASSWORD_DEFAULT),
                'activo' => true,
                'creado_en' => now(),
            ]);
        }

        DB::table('administrador')->updateOrInsert(
            ['usuario_id' => $usuarioId],
            [
                'persona_id' => $personaId,
                'activo' => true,
                'creado_en' => now(),
            ]
        );
    }
}
