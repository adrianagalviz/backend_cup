<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('usuario', function (Blueprint $table): void {
            $table->string('paleta_visual', 30)->default('azul')->after('activo');
            $table->string('modo_visual', 20)->default('claro')->after('paleta_visual');
        });
    }

    public function down(): void
    {
        Schema::table('usuario', function (Blueprint $table): void {
            $table->dropColumn(['paleta_visual', 'modo_visual']);
        });
    }
};
