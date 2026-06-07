<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('carrera', function (Blueprint $table): void {
            $table->string('codigo', 30)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('carrera', function (Blueprint $table): void {
            $table->dropUnique(['codigo']);
            $table->dropColumn('codigo');
        });
    }
};
