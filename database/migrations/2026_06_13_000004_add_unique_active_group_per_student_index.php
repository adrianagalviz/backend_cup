<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE UNIQUE INDEX grupo_alumno_alumno_activo_unique ON grupo_alumno (alumno_id) WHERE activo = true');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS grupo_alumno_alumno_activo_unique');
    }
};
