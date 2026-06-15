<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            if (! Schema::hasColumn('docente', 'cv_pdf_cloudinary_public_id')) {
                $table->string('cv_pdf_cloudinary_public_id', 255)->nullable();
            }

            if (! Schema::hasColumn('docente', 'cv_pdf_cloudinary_url')) {
                $table->text('cv_pdf_cloudinary_url')->nullable();
            }
        });

        Schema::table('docente', function (Blueprint $table): void {
            if (Schema::hasColumn('docente', 'cv_pdf_path')) {
                $table->dropColumn('cv_pdf_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('docente', function (Blueprint $table): void {
            if (! Schema::hasColumn('docente', 'cv_pdf_path')) {
                $table->text('cv_pdf_path')->nullable();
            }
        });

        Schema::table('docente', function (Blueprint $table): void {
            if (Schema::hasColumn('docente', 'cv_pdf_cloudinary_public_id')) {
                $table->dropColumn('cv_pdf_cloudinary_public_id');
            }

            if (Schema::hasColumn('docente', 'cv_pdf_cloudinary_url')) {
                $table->dropColumn('cv_pdf_cloudinary_url');
            }
        });
    }
};
