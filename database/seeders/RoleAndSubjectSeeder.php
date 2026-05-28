<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleAndSubjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('rol')->upsert([
            ['id' => 1, 'nombre' => 'administrador', 'descripcion' => 'Administrador con acceso completo al sistema.', 'activo' => true],
            ['id' => 2, 'nombre' => 'docente', 'descripcion' => 'Docente con acceso limitado a su carga horaria y asistencias.', 'activo' => true],
            ['id' => 3, 'nombre' => 'alumno', 'descripcion' => 'Alumno con acceso a perfil, horarios, asistencia y examenes habilitados.', 'activo' => true],
        ], ['id'], ['nombre', 'descripcion', 'activo']);

        DB::statement("SELECT setval(pg_get_serial_sequence('rol', 'id'), (SELECT MAX(id) FROM rol))");

        DB::table('materia')->upsert([
            ['nombre' => 'F'."\u{00ED}".'sica', 'activa' => true],
            ['nombre' => 'Matem'."\u{00E1}".'ticas', 'activa' => true],
            ['nombre' => 'Computaci'."\u{00F3}".'n', 'activa' => true],
            ['nombre' => 'Ingl'."\u{00E9}".'s', 'activa' => true],
        ], ['nombre'], ['activa']);
    }
}
