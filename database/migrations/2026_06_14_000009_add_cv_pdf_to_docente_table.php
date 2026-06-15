<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            $table->string('cv_pdf_cloudinary_public_id', 255)->nullable();
            $table->text('cv_pdf_cloudinary_url')->nullable();
            $table->string('cv_pdf_nombre_original', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            $table->dropColumn(['cv_pdf_cloudinary_public_id', 'cv_pdf_cloudinary_url', 'cv_pdf_nombre_original']);
        });
    }
};
