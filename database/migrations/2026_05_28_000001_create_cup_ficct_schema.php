<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rol', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 30)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('persona', function (Blueprint $table): void {
            $table->id();
            $table->string('cedula_identidad', 20)->unique();
            $table->string('nombres', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 20)->nullable();
            $table->text('direccion')->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('correo', 150)->unique();
            $table->string('ciudad', 100)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('usuario', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->cascadeOnDelete();
            $table->foreignId('rol_id')->constrained('rol')->restrictOnDelete();
            $table->string('nombre_usuario', 100)->unique();
            $table->string('codigo_acceso', 30)->nullable()->unique();
            $table->boolean('correo_verificado')->default(false);
            $table->string('firebase_uid', 150)->nullable()->unique();
            $table->text('password_hash')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('ultimo_inicio_sesion')->nullable();
            $table->foreignId('creado_por_usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('administrador', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->cascadeOnDelete();
            $table->foreignId('usuario_id')->unique()->constrained('usuario')->cascadeOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('docente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->cascadeOnDelete();
            $table->foreignId('usuario_id')->nullable()->unique()->constrained('usuario')->nullOnDelete();
            $table->boolean('es_profesional_area')->default(false);
            $table->boolean('tiene_maestria')->default(false);
            $table->boolean('tiene_diplomado_educacion_superior')->default(false);
            $table->boolean('contratado')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('gestion_academica', function (Blueprint $table): void {
            $table->id();
            $table->integer('anio');
            $table->integer('numero_gestion');
            $table->string('nombre', 100);
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['anio', 'numero_gestion']);
        });

        Schema::create('carrera', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->text('descripcion')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('cupo_carrera', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carrera')->restrictOnDelete();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->integer('cantidad_cupos');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['carrera_id', 'gestion_academica_id']);
        });

        Schema::create('postulante', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->cascadeOnDelete();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->string('colegio_procedencia', 150);
            $table->string('estado_requisitos', 30)->default('pendiente');
            $table->string('estado_pago', 30)->default('pendiente');
            $table->string('estado_postulante', 30)->default('registrado');
            $table->text('observacion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
        });

        Schema::create('documento_postulante', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('postulante_id')->constrained('postulante')->cascadeOnDelete();
            $table->string('tipo_documento', 50);
            $table->string('cloudinary_public_id', 200);
            $table->text('cloudinary_url');
            $table->string('formato_archivo', 30)->nullable();
            $table->timestamp('subido_en')->useCurrent();
            $table->string('estado_revision', 30)->default('pendiente');
            $table->text('observacion')->nullable();
        });

        Schema::create('pago_stripe', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('postulante_id')->unique()->constrained('postulante')->cascadeOnDelete();
            $table->string('stripe_payment_intent_id', 200)->nullable()->unique();
            $table->string('stripe_checkout_session_id', 200)->nullable()->unique();
            $table->decimal('monto', 10, 2);
            $table->string('moneda', 10)->default('BOB');
            $table->string('estado_pago', 30)->default('pendiente');
            $table->timestamp('fecha_pago')->nullable();
            $table->jsonb('respuesta_stripe')->nullable();
            $table->foreignId('validado_por_usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->timestamp('validado_en')->nullable();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('postulacion', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('postulante_id')->unique()->constrained('postulante')->cascadeOnDelete();
            $table->foreignId('primera_carrera_id')->constrained('carrera')->restrictOnDelete();
            $table->foreignId('segunda_carrera_id')->constrained('carrera')->restrictOnDelete();
            $table->foreignId('carrera_asignada_id')->nullable()->constrained('carrera')->nullOnDelete();
            $table->string('motivo_asignacion', 100)->nullable();
            $table->decimal('promedio_final', 5, 2)->nullable();
            $table->string('estado_final', 30)->nullable();
            $table->integer('orden_prioridad')->nullable();
            $table->timestamp('asignado_en')->nullable();
        });

        Schema::create('alumno', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->cascadeOnDelete();
            $table->foreignId('usuario_id')->unique()->constrained('usuario')->cascadeOnDelete();
            $table->foreignId('postulante_id')->unique()->constrained('postulante')->restrictOnDelete();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->string('codigo_alumno', 30)->unique();
            $table->string('estado_academico', 30)->default('activo');
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('materia', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('activa')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('grupo', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->string('nombre', 100);
            $table->integer('cupo_maximo')->default(70);
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['gestion_academica_id', 'nombre']);
        });

        Schema::create('grupo_alumno', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('grupo_id')->constrained('grupo')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumno')->cascadeOnDelete();
            $table->timestamp('fecha_asignacion')->useCurrent();
            $table->boolean('activo')->default(true);
            $table->unique(['grupo_id', 'alumno_id']);
        });

        Schema::create('aula', function (Blueprint $table): void {
            $table->id();
            $table->string('ubicacion', 200)->unique();
            $table->boolean('activa')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('dia', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 30)->unique();
            $table->integer('orden')->unique();
            $table->boolean('activo')->default(true);
        });

        Schema::create('turno', function (Blueprint $table): void {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
        });

        Schema::create('periodo', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('turno_id')->constrained('turno')->cascadeOnDelete();
            $table->integer('numero_periodo');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('duracion_minutos')->default(45);
            $table->boolean('activo')->default(true);
            $table->unique(['turno_id', 'numero_periodo']);
        });

        Schema::create('horario_clase', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->foreignId('grupo_id')->constrained('grupo')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materia')->restrictOnDelete();
            $table->foreignId('aula_id')->constrained('aula')->restrictOnDelete();
            $table->foreignId('dia_id')->constrained('dia')->restrictOnDelete();
            $table->foreignId('turno_id')->constrained('turno')->restrictOnDelete();
            $table->foreignId('periodo_id')->constrained('periodo')->restrictOnDelete();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('asignacion_docente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('docente_id')->constrained('docente')->restrictOnDelete();
            $table->foreignId('materia_id')->constrained('materia')->restrictOnDelete();
            $table->foreignId('grupo_id')->constrained('grupo')->restrictOnDelete();
            $table->foreignId('horario_clase_id')->constrained('horario_clase')->restrictOnDelete();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->boolean('activo')->default(true);
            $table->timestamp('asignado_en')->useCurrent();
            $table->unique(['docente_id', 'materia_id', 'grupo_id', 'horario_clase_id']);
        });

        Schema::create('asistencia_docente', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('docente_id')->constrained('docente')->restrictOnDelete();
            $table->foreignId('horario_clase_id')->constrained('horario_clase')->restrictOnDelete();
            $table->date('fecha');
            $table->timestamp('hora_entrada')->nullable();
            $table->timestamp('hora_salida')->nullable();
            $table->string('estado_entrada', 30)->default('pendiente');
            $table->string('estado_salida', 30)->nullable();
            $table->foreignId('marcado_por_usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['docente_id', 'horario_clase_id', 'fecha']);
        });

        Schema::create('asistencia_alumno', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('horario_clase_id')->constrained('horario_clase')->restrictOnDelete();
            $table->foreignId('docente_id')->nullable()->constrained('docente')->nullOnDelete();
            $table->date('fecha');
            $table->timestamp('hora_marcada')->nullable();
            $table->string('estado_asistencia', 30)->default('pendiente');
            $table->foreignId('registrado_por_usuario_id')->nullable()->constrained('usuario')->nullOnDelete();
            $table->text('observacion')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['alumno_id', 'horario_clase_id', 'fecha']);
        });

        Schema::create('examen', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->integer('numero_parcial');
            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();
            $table->boolean('habilitado')->default(false);
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->foreignId('creado_por_usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->nullable();
            $table->unique(['gestion_academica_id', 'numero_parcial']);
        });

        Schema::create('examen_materia_porcentaje', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('examen_id')->constrained('examen')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materia')->restrictOnDelete();
            $table->decimal('porcentaje', 5, 2);
            $table->unique(['examen_id', 'materia_id']);
        });

        Schema::create('pregunta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('examen_id')->constrained('examen')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materia')->restrictOnDelete();
            $table->text('enunciado');
            $table->string('tipo_pregunta', 50)->default('seleccion_multiple');
            $table->decimal('puntaje', 5, 2)->default(1);
            $table->boolean('activa')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('opcion_pregunta', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pregunta_id')->constrained('pregunta')->cascadeOnDelete();
            $table->text('texto_opcion');
            $table->boolean('es_correcta')->default(false);
            $table->integer('orden');
            $table->unique(['pregunta_id', 'orden']);
        });

        Schema::create('intento_examen', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('examen_id')->constrained('examen')->restrictOnDelete();
            $table->timestamp('fecha_inicio')->nullable();
            $table->timestamp('fecha_fin')->nullable();
            $table->string('estado', 30)->default('pendiente');
            $table->decimal('nota_total', 5, 2)->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['alumno_id', 'examen_id']);
        });

        Schema::create('respuesta_alumno', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('intento_examen_id')->constrained('intento_examen')->cascadeOnDelete();
            $table->foreignId('pregunta_id')->constrained('pregunta')->restrictOnDelete();
            $table->foreignId('opcion_pregunta_id')->constrained('opcion_pregunta')->restrictOnDelete();
            $table->boolean('es_correcta')->nullable();
            $table->timestamp('respondido_en')->useCurrent();
            $table->unique(['intento_examen_id', 'pregunta_id']);
        });

        Schema::create('nota_examen_materia', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('intento_examen_id')->constrained('intento_examen')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materia')->restrictOnDelete();
            $table->decimal('nota', 5, 2);
            $table->decimal('porcentaje_aplicado', 5, 2);
            $table->decimal('nota_ponderada', 5, 2);
            $table->unique(['intento_examen_id', 'materia_id']);
        });

        Schema::create('nota_parcial', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('examen_id')->constrained('examen')->restrictOnDelete();
            $table->foreignId('intento_examen_id')->unique()->constrained('intento_examen')->cascadeOnDelete();
            $table->integer('numero_parcial');
            $table->decimal('nota', 5, 2);
            $table->timestamp('registrado_en')->useCurrent();
            $table->unique(['alumno_id', 'numero_parcial', 'examen_id']);
        });

        Schema::create('promedio_final', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumno')->restrictOnDelete();
            $table->foreignId('gestion_academica_id')->constrained('gestion_academica')->restrictOnDelete();
            $table->decimal('parcial_1', 5, 2)->nullable();
            $table->decimal('parcial_2', 5, 2)->nullable();
            $table->decimal('parcial_3', 5, 2)->nullable();
            $table->decimal('promedio', 5, 2)->nullable();
            $table->string('estado_final', 30)->nullable();
            $table->timestamp('calculado_en')->nullable();
            $table->unique(['alumno_id', 'gestion_academica_id']);
        });

        Schema::create('reporte_generado', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->string('tipo_reporte', 100);
            $table->string('formato_exportacion', 20)->nullable();
            $table->jsonb('parametros')->nullable();
            $table->text('archivo_url')->nullable();
            $table->timestamp('generado_en')->useCurrent();
        });

        Schema::create('comando_voz_reporte', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->text('texto_detectado');
            $table->string('intencion_detectada', 100)->nullable();
            $table->foreignId('reporte_generado_id')->nullable()->constrained('reporte_generado')->nullOnDelete();
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('carga_masiva', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuario')->restrictOnDelete();
            $table->string('tipo_carga', 50);
            $table->string('nombre_archivo', 200);
            $table->string('formato_archivo', 20);
            $table->integer('total_registros')->default(0);
            $table->integer('registros_exitosos')->default(0);
            $table->integer('registros_error')->default(0);
            $table->string('estado', 30)->default('procesando');
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('finalizado_en')->nullable();
        });

        Schema::create('detalle_carga_masiva', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('carga_masiva_id')->constrained('carga_masiva')->cascadeOnDelete();
            $table->integer('numero_fila');
            $table->string('estado', 30);
            $table->text('mensaje_error')->nullable();
            $table->jsonb('datos_fila')->nullable();
        });

        $this->addCheckConstraints();
        $this->addIndexes();
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_carga_masiva');
        Schema::dropIfExists('carga_masiva');
        Schema::dropIfExists('comando_voz_reporte');
        Schema::dropIfExists('reporte_generado');
        Schema::dropIfExists('promedio_final');
        Schema::dropIfExists('nota_parcial');
        Schema::dropIfExists('nota_examen_materia');
        Schema::dropIfExists('respuesta_alumno');
        Schema::dropIfExists('intento_examen');
        Schema::dropIfExists('opcion_pregunta');
        Schema::dropIfExists('pregunta');
        Schema::dropIfExists('examen_materia_porcentaje');
        Schema::dropIfExists('examen');
        Schema::dropIfExists('asistencia_alumno');
        Schema::dropIfExists('asistencia_docente');
        Schema::dropIfExists('asignacion_docente');
        Schema::dropIfExists('horario_clase');
        Schema::dropIfExists('periodo');
        Schema::dropIfExists('turno');
        Schema::dropIfExists('dia');
        Schema::dropIfExists('aula');
        Schema::dropIfExists('grupo_alumno');
        Schema::dropIfExists('grupo');
        Schema::dropIfExists('materia');
        Schema::dropIfExists('alumno');
        Schema::dropIfExists('postulacion');
        Schema::dropIfExists('pago_stripe');
        Schema::dropIfExists('documento_postulante');
        Schema::dropIfExists('postulante');
        Schema::dropIfExists('cupo_carrera');
        Schema::dropIfExists('carrera');
        Schema::dropIfExists('gestion_academica');
        Schema::dropIfExists('docente');
        Schema::dropIfExists('administrador');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('persona');
        Schema::dropIfExists('rol');
    }

    private function addCheckConstraints(): void
    {
        $checks = [
            "ALTER TABLE rol ADD CONSTRAINT rol_nombre_check CHECK (nombre IN ('administrador', 'docente', 'alumno'))",
            'ALTER TABLE gestion_academica ADD CONSTRAINT gestion_academica_numero_gestion_check CHECK (numero_gestion IN (1, 2))',
            'ALTER TABLE cupo_carrera ADD CONSTRAINT cupo_carrera_cantidad_check CHECK (cantidad_cupos >= 0)',
            "ALTER TABLE postulante ADD CONSTRAINT postulante_estado_requisitos_check CHECK (estado_requisitos IN ('pendiente', 'aprobado', 'rechazado'))",
            "ALTER TABLE postulante ADD CONSTRAINT postulante_estado_pago_check CHECK (estado_pago IN ('pendiente', 'pagado', 'rechazado'))",
            "ALTER TABLE postulante ADD CONSTRAINT postulante_estado_check CHECK (estado_postulante IN ('registrado', 'pendiente_pago', 'pagado', 'habilitado_alumno', 'rechazado'))",
            "ALTER TABLE documento_postulante ADD CONSTRAINT documento_postulante_tipo_check CHECK (tipo_documento IN ('titulo_bachiller'))",
            "ALTER TABLE documento_postulante ADD CONSTRAINT documento_postulante_estado_revision_check CHECK (estado_revision IN ('pendiente', 'aprobado', 'rechazado'))",
            'ALTER TABLE pago_stripe ADD CONSTRAINT pago_stripe_monto_check CHECK (monto >= 0)',
            "ALTER TABLE pago_stripe ADD CONSTRAINT pago_stripe_estado_pago_check CHECK (estado_pago IN ('pendiente', 'pagado', 'rechazado', 'fallido'))",
            'ALTER TABLE postulacion ADD CONSTRAINT postulacion_carreras_distintas_check CHECK (primera_carrera_id <> segunda_carrera_id)',
            'ALTER TABLE postulacion ADD CONSTRAINT postulacion_promedio_final_check CHECK (promedio_final IS NULL OR (promedio_final >= 0 AND promedio_final <= 100))',
            "ALTER TABLE postulacion ADD CONSTRAINT postulacion_estado_final_check CHECK (estado_final IS NULL OR estado_final IN ('aprobado', 'reprobado'))",
            "ALTER TABLE postulacion ADD CONSTRAINT postulacion_motivo_check CHECK (motivo_asignacion IS NULL OR motivo_asignacion IN ('primera_opcion', 'segunda_opcion', 'carrera_con_menos_personas'))",
            "ALTER TABLE alumno ADD CONSTRAINT alumno_estado_academico_check CHECK (estado_academico IN ('activo', 'aprobado', 'reprobado'))",
            'ALTER TABLE grupo ADD CONSTRAINT grupo_cupo_maximo_check CHECK (cupo_maximo > 0 AND cupo_maximo <= 70)',
            'ALTER TABLE periodo ADD CONSTRAINT periodo_duracion_check CHECK (duracion_minutos = 45)',
            "ALTER TABLE asistencia_docente ADD CONSTRAINT asistencia_docente_estado_entrada_check CHECK (estado_entrada IN ('pendiente', 'presente', 'retraso', 'falta'))",
            "ALTER TABLE asistencia_docente ADD CONSTRAINT asistencia_docente_estado_salida_check CHECK (estado_salida IS NULL OR estado_salida IN ('pendiente', 'finalizado'))",
            "ALTER TABLE asistencia_alumno ADD CONSTRAINT asistencia_alumno_estado_check CHECK (estado_asistencia IN ('pendiente', 'presente', 'retraso', 'falta'))",
            'ALTER TABLE examen ADD CONSTRAINT examen_numero_parcial_check CHECK (numero_parcial IN (1, 2, 3))',
            'ALTER TABLE examen_materia_porcentaje ADD CONSTRAINT examen_materia_porcentaje_check CHECK (porcentaje >= 0 AND porcentaje <= 100)',
            "ALTER TABLE pregunta ADD CONSTRAINT pregunta_tipo_check CHECK (tipo_pregunta = 'seleccion_multiple')",
            'ALTER TABLE pregunta ADD CONSTRAINT pregunta_puntaje_check CHECK (puntaje > 0)',
            "ALTER TABLE intento_examen ADD CONSTRAINT intento_examen_estado_check CHECK (estado IN ('pendiente', 'en_progreso', 'finalizado', 'anulado'))",
            'ALTER TABLE intento_examen ADD CONSTRAINT intento_examen_nota_total_check CHECK (nota_total IS NULL OR (nota_total >= 0 AND nota_total <= 100))',
            'ALTER TABLE nota_examen_materia ADD CONSTRAINT nota_examen_materia_nota_check CHECK (nota >= 0 AND nota <= 100)',
            'ALTER TABLE nota_examen_materia ADD CONSTRAINT nota_examen_materia_porcentaje_check CHECK (porcentaje_aplicado >= 0 AND porcentaje_aplicado <= 100)',
            'ALTER TABLE nota_examen_materia ADD CONSTRAINT nota_examen_materia_ponderada_check CHECK (nota_ponderada >= 0 AND nota_ponderada <= 100)',
            'ALTER TABLE nota_parcial ADD CONSTRAINT nota_parcial_numero_check CHECK (numero_parcial IN (1, 2, 3))',
            'ALTER TABLE nota_parcial ADD CONSTRAINT nota_parcial_nota_check CHECK (nota >= 0 AND nota <= 100)',
            'ALTER TABLE promedio_final ADD CONSTRAINT promedio_final_parcial_1_check CHECK (parcial_1 IS NULL OR (parcial_1 >= 0 AND parcial_1 <= 100))',
            'ALTER TABLE promedio_final ADD CONSTRAINT promedio_final_parcial_2_check CHECK (parcial_2 IS NULL OR (parcial_2 >= 0 AND parcial_2 <= 100))',
            'ALTER TABLE promedio_final ADD CONSTRAINT promedio_final_parcial_3_check CHECK (parcial_3 IS NULL OR (parcial_3 >= 0 AND parcial_3 <= 100))',
            'ALTER TABLE promedio_final ADD CONSTRAINT promedio_final_promedio_check CHECK (promedio IS NULL OR (promedio >= 0 AND promedio <= 100))',
            "ALTER TABLE promedio_final ADD CONSTRAINT promedio_final_estado_check CHECK (estado_final IS NULL OR estado_final IN ('aprobado', 'reprobado'))",
            "ALTER TABLE reporte_generado ADD CONSTRAINT reporte_generado_formato_check CHECK (formato_exportacion IS NULL OR formato_exportacion IN ('pdf', 'excel'))",
            "ALTER TABLE carga_masiva ADD CONSTRAINT carga_masiva_formato_check CHECK (formato_archivo IN ('excel', 'csv'))",
            "ALTER TABLE carga_masiva ADD CONSTRAINT carga_masiva_estado_check CHECK (estado IN ('procesando', 'finalizado', 'con_errores', 'fallido'))",
            "ALTER TABLE detalle_carga_masiva ADD CONSTRAINT detalle_carga_masiva_estado_check CHECK (estado IN ('exitoso', 'error'))",
        ];

        foreach ($checks as $sql) {
            DB::statement($sql);
        }
    }

    private function addIndexes(): void
    {
        DB::statement('CREATE INDEX persona_cedula_identidad_idx ON persona (cedula_identidad)');
        DB::statement('CREATE INDEX persona_correo_idx ON persona (correo)');
        DB::statement('CREATE INDEX alumno_codigo_alumno_idx ON alumno (codigo_alumno)');
        DB::statement('CREATE INDEX postulante_estado_postulante_idx ON postulante (estado_postulante)');
        DB::statement('CREATE INDEX postulante_gestion_idx ON postulante (gestion_academica_id)');
        DB::statement('CREATE INDEX pago_stripe_estado_pago_idx ON pago_stripe (estado_pago)');
        DB::statement('CREATE INDEX asistencia_docente_fecha_idx ON asistencia_docente (fecha)');
        DB::statement('CREATE INDEX asistencia_alumno_fecha_idx ON asistencia_alumno (fecha)');
        DB::statement('CREATE INDEX reporte_generado_tipo_idx ON reporte_generado (tipo_reporte)');
        DB::statement('CREATE INDEX examen_gestion_idx ON examen (gestion_academica_id)');
    }
};
