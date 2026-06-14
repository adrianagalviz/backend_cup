<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bitacora_sistema', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->string('modulo', 80);
            $table->string('accion', 80);
            $table->string('metodo_http', 10);
            $table->string('ruta', 255);
            $table->integer('estado_http')->nullable();
            $table->string('direccion_ip', 60)->nullable();
            $table->text('agente_usuario')->nullable();
            $table->jsonb('datos')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        DB::statement('CREATE INDEX bitacora_sistema_usuario_idx ON bitacora_sistema (usuario_id)');
        DB::statement('CREATE INDEX bitacora_sistema_modulo_idx ON bitacora_sistema (modulo)');
        DB::statement('CREATE INDEX bitacora_sistema_creado_en_idx ON bitacora_sistema (creado_en)');
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_sistema');
    }
};
