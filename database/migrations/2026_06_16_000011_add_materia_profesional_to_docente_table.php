<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            $table->foreignId('materia_profesional_id')
                ->nullable()
                ->after('es_profesional_area')
                ->constrained('materia')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('materia_profesional_id');
        });
    }
};
