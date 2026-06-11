<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoblarDbSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $context = require base_path('poblar_db/00_helpers.php');

            foreach ([
                '01_seguridad.php',
                '02_academico.php',
                '03_docentes.php',
                '04_infraestructura.php',
                '05_horarios.php',
                '06_postulantes.php',
                '07_alumnos.php',
                '08_examenes.php',
                '09_resultados.php',
                '10_asistencias_reportes_cargas.php',
            ] as $script) {
                $loader = require base_path('poblar_db/'.$script);
                $loader($context);
            }
        });
    }
}
