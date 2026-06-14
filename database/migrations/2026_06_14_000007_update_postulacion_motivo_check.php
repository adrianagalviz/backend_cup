<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('postulacion')
            ->where('motivo_asignacion', 'carrera_con_menos_personas')
            ->update([
                'carrera_asignada_id' => null,
                'motivo_asignacion' => null,
                'orden_prioridad' => null,
                'asignado_en' => null,
            ]);

        DB::statement('ALTER TABLE postulacion DROP CONSTRAINT IF EXISTS postulacion_motivo_check');
        DB::statement("ALTER TABLE postulacion ADD CONSTRAINT postulacion_motivo_check CHECK (motivo_asignacion IS NULL OR motivo_asignacion IN ('primera_opcion', 'segunda_opcion'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE postulacion DROP CONSTRAINT IF EXISTS postulacion_motivo_check');
        DB::statement("ALTER TABLE postulacion ADD CONSTRAINT postulacion_motivo_check CHECK (motivo_asignacion IS NULL OR motivo_asignacion IN ('primera_opcion', 'segunda_opcion', 'carrera_con_menos_personas'))");
    }
};
