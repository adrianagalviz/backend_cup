<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        foreach ($this->indexes() as $sql) {
            DB::statement($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (array_reverse($this->indexNames()) as $indexName) {
            DB::statement("DROP INDEX IF EXISTS {$indexName}");
        }
    }

    /**
     * @return array<int, string>
     */
    private function indexes(): array
    {
        return [
            'CREATE INDEX IF NOT EXISTS idx_horario_gestion_dia_grupo_horas_activo ON horario_clase (gestion_academica_id, dia_id, grupo_id, hora_inicio, hora_fin) WHERE activo = true',
            'CREATE INDEX IF NOT EXISTS idx_horario_gestion_dia_aula_horas_activo ON horario_clase (gestion_academica_id, dia_id, aula_id, hora_inicio, hora_fin) WHERE activo = true',
            'CREATE INDEX IF NOT EXISTS idx_horario_grupo_gestion_dia_periodo_activo ON horario_clase (grupo_id, gestion_academica_id, dia_id, periodo_id) WHERE activo = true',
            'CREATE INDEX IF NOT EXISTS idx_horario_materia ON horario_clase (materia_id)',
            'CREATE INDEX IF NOT EXISTS idx_horario_turno ON horario_clase (turno_id)',
            'CREATE INDEX IF NOT EXISTS idx_horario_periodo ON horario_clase (periodo_id)',

            'CREATE INDEX IF NOT EXISTS idx_asignacion_docente_activo ON asignacion_docente (docente_id, activo)',
            'CREATE INDEX IF NOT EXISTS idx_asignacion_horario ON asignacion_docente (horario_clase_id)',
            'CREATE INDEX IF NOT EXISTS idx_asignacion_gestion_activo_docente ON asignacion_docente (gestion_academica_id, activo, docente_id)',
            'CREATE INDEX IF NOT EXISTS idx_asignacion_grupo_activo ON asignacion_docente (grupo_id, activo)',

            'CREATE INDEX IF NOT EXISTS idx_asistencia_alumno_fecha_id_desc ON asistencia_alumno (fecha DESC, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_alumno_alumno_fecha_desc ON asistencia_alumno (alumno_id, fecha DESC)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_alumno_docente_fecha_desc ON asistencia_alumno (docente_id, fecha DESC)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_alumno_horario_fecha ON asistencia_alumno (horario_clase_id, fecha)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_alumno_estado_fecha ON asistencia_alumno (estado_asistencia, fecha)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_docente_fecha_id_desc ON asistencia_docente (fecha DESC, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_docente_docente_fecha_desc ON asistencia_docente (docente_id, fecha DESC)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_docente_horario_fecha ON asistencia_docente (horario_clase_id, fecha)',
            'CREATE INDEX IF NOT EXISTS idx_asistencia_docente_estado_fecha ON asistencia_docente (estado_entrada, fecha)',

            'CREATE INDEX IF NOT EXISTS idx_postulante_gestion_estado_id_desc ON postulante (gestion_academica_id, estado_postulante, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_postulante_gestion_creado ON postulante (gestion_academica_id, creado_en)',
            'CREATE INDEX IF NOT EXISTS idx_postulante_requisitos_pago ON postulante (estado_requisitos, estado_pago)',
            'CREATE INDEX IF NOT EXISTS idx_pago_estado_creado ON pago_stripe (estado_pago, creado_en)',
            'CREATE INDEX IF NOT EXISTS idx_pago_validado_en ON pago_stripe (validado_en)',
            'CREATE INDEX IF NOT EXISTS idx_pago_validado_por ON pago_stripe (validado_por_usuario_id)',
            'CREATE INDEX IF NOT EXISTS idx_postulacion_carrera_asignada_estado ON postulacion (carrera_asignada_id, estado_final)',
            'CREATE INDEX IF NOT EXISTS idx_postulacion_primera_carrera ON postulacion (primera_carrera_id)',
            'CREATE INDEX IF NOT EXISTS idx_postulacion_segunda_carrera ON postulacion (segunda_carrera_id)',

            'CREATE INDEX IF NOT EXISTS idx_alumno_gestion_estado_id_desc ON alumno (gestion_academica_id, estado_academico, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_grupo_gestion_activo_nombre ON grupo (gestion_academica_id, activo, nombre)',
            'CREATE INDEX IF NOT EXISTS idx_grupo_alumno_grupo_alumno_activo ON grupo_alumno (grupo_id, alumno_id) WHERE activo = true',
            'CREATE INDEX IF NOT EXISTS idx_promedio_gestion_estado_promedio_alumno ON promedio_final (gestion_academica_id, estado_final, promedio DESC, alumno_id)',
            'CREATE INDEX IF NOT EXISTS idx_promedio_estado_calculado ON promedio_final (estado_final, calculado_en)',
            'CREATE INDEX IF NOT EXISTS idx_nota_parcial_alumno_numero ON nota_parcial (alumno_id, numero_parcial)',
            'CREATE INDEX IF NOT EXISTS idx_intento_examen_estado_alumno ON intento_examen (examen_id, estado, alumno_id)',
            'CREATE INDEX IF NOT EXISTS idx_intento_examen_creado ON intento_examen (creado_en)',
            'CREATE INDEX IF NOT EXISTS idx_pregunta_examen_materia_activa ON pregunta (examen_id, materia_id, activa)',

            'CREATE INDEX IF NOT EXISTS idx_reporte_tipo_generado_desc ON reporte_generado (tipo_reporte, generado_en DESC)',
            'CREATE INDEX IF NOT EXISTS idx_reporte_usuario_generado_desc ON reporte_generado (usuario_id, generado_en DESC)',
            'CREATE INDEX IF NOT EXISTS idx_carga_estado_tipo_creado_desc ON carga_masiva (estado, tipo_carga, creado_en DESC)',
            'CREATE INDEX IF NOT EXISTS idx_detalle_carga_fila ON detalle_carga_masiva (carga_masiva_id, numero_fila)',
            'CREATE INDEX IF NOT EXISTS idx_detalle_carga_estado ON detalle_carga_masiva (carga_masiva_id, estado)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_creado_id_desc ON bitacora_sistema (creado_en DESC, id DESC)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_usuario_creado_desc ON bitacora_sistema (usuario_id, creado_en DESC)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_modulo_creado_desc ON bitacora_sistema (modulo, creado_en DESC)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_metodo_creado_desc ON bitacora_sistema (metodo_http, creado_en DESC)',

            'CREATE INDEX IF NOT EXISTS idx_persona_ci_trgm ON persona USING gin (cedula_identidad gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_persona_nombres_trgm ON persona USING gin (nombres gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_persona_ap_paterno_trgm ON persona USING gin (apellido_paterno gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_persona_ap_materno_trgm ON persona USING gin (apellido_materno gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_persona_correo_trgm ON persona USING gin (correo gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_usuario_nombre_trgm ON usuario USING gin (nombre_usuario gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_usuario_codigo_acceso_trgm ON usuario USING gin (codigo_acceso gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_carrera_nombre_trgm ON carrera USING gin (nombre gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_carrera_codigo_trgm ON carrera USING gin (codigo gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_ruta_trgm ON bitacora_sistema USING gin (ruta gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_accion_trgm ON bitacora_sistema USING gin (accion gin_trgm_ops)',
            'CREATE INDEX IF NOT EXISTS idx_bitacora_modulo_trgm ON bitacora_sistema USING gin (modulo gin_trgm_ops)',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function indexNames(): array
    {
        return [
            'idx_horario_gestion_dia_grupo_horas_activo',
            'idx_horario_gestion_dia_aula_horas_activo',
            'idx_horario_grupo_gestion_dia_periodo_activo',
            'idx_horario_materia',
            'idx_horario_turno',
            'idx_horario_periodo',
            'idx_asignacion_docente_activo',
            'idx_asignacion_horario',
            'idx_asignacion_gestion_activo_docente',
            'idx_asignacion_grupo_activo',
            'idx_asistencia_alumno_fecha_id_desc',
            'idx_asistencia_alumno_alumno_fecha_desc',
            'idx_asistencia_alumno_docente_fecha_desc',
            'idx_asistencia_alumno_horario_fecha',
            'idx_asistencia_alumno_estado_fecha',
            'idx_asistencia_docente_fecha_id_desc',
            'idx_asistencia_docente_docente_fecha_desc',
            'idx_asistencia_docente_horario_fecha',
            'idx_asistencia_docente_estado_fecha',
            'idx_postulante_gestion_estado_id_desc',
            'idx_postulante_gestion_creado',
            'idx_postulante_requisitos_pago',
            'idx_pago_estado_creado',
            'idx_pago_validado_en',
            'idx_pago_validado_por',
            'idx_postulacion_carrera_asignada_estado',
            'idx_postulacion_primera_carrera',
            'idx_postulacion_segunda_carrera',
            'idx_alumno_gestion_estado_id_desc',
            'idx_grupo_gestion_activo_nombre',
            'idx_grupo_alumno_grupo_alumno_activo',
            'idx_promedio_gestion_estado_promedio_alumno',
            'idx_promedio_estado_calculado',
            'idx_nota_parcial_alumno_numero',
            'idx_intento_examen_estado_alumno',
            'idx_intento_examen_creado',
            'idx_pregunta_examen_materia_activa',
            'idx_reporte_tipo_generado_desc',
            'idx_reporte_usuario_generado_desc',
            'idx_carga_estado_tipo_creado_desc',
            'idx_detalle_carga_fila',
            'idx_detalle_carga_estado',
            'idx_bitacora_creado_id_desc',
            'idx_bitacora_usuario_creado_desc',
            'idx_bitacora_modulo_creado_desc',
            'idx_bitacora_metodo_creado_desc',
            'idx_persona_ci_trgm',
            'idx_persona_nombres_trgm',
            'idx_persona_ap_paterno_trgm',
            'idx_persona_ap_materno_trgm',
            'idx_persona_correo_trgm',
            'idx_usuario_nombre_trgm',
            'idx_usuario_codigo_acceso_trgm',
            'idx_carrera_nombre_trgm',
            'idx_carrera_codigo_trgm',
            'idx_bitacora_ruta_trgm',
            'idx_bitacora_accion_trgm',
            'idx_bitacora_modulo_trgm',
        ];
    }
};
