<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE periodo DROP CONSTRAINT IF EXISTS periodo_duracion_check');
        DB::statement('ALTER TABLE periodo ALTER COLUMN duracion_minutos SET DEFAULT 90');
        DB::statement('ALTER TABLE periodo ADD CONSTRAINT periodo_duracion_check CHECK (duracion_minutos IN (45, 90))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE periodo DROP CONSTRAINT IF EXISTS periodo_duracion_check');
        DB::statement('ALTER TABLE periodo ALTER COLUMN duracion_minutos SET DEFAULT 45');
        DB::statement('ALTER TABLE periodo ADD CONSTRAINT periodo_duracion_check CHECK (duracion_minutos = 45)');
    }
};
