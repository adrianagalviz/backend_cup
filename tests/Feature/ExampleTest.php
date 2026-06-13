<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_api_health_endpoint_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/v1/salud');

        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'mensaje' => 'API REST del sistema CUP FICCT disponible.',
        ]);
    }

    public function test_the_api_returns_json_when_route_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/ruta-inexistente');

        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Ruta API no encontrada.',
            'errores' => [],
        ]);
    }

    public function test_the_api_returns_json_for_missing_post_route(): void
    {
        $response = $this->postJson('/api/v1/ruta-inexistente');

        $response->assertStatus(404);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Ruta API no encontrada.',
            'errores' => [],
        ]);
    }

    public function test_login_requires_user_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_student_login_requires_code(): void
    {
        $response = $this->postJson('/api/v1/auth/alumno/login', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_firebase_login_requires_token(): void
    {
        $response = $this->postJson('/api/v1/auth/firebase', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_profile_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/auth/perfil');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_logout_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_users_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/usuarios');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_create_administrator_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/usuarios/administradores', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_registration_requires_required_fields(): void
    {
        $response = $this->postJson('/api/v1/postulantes', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_applicants_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/postulantes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_update_requires_authentication_token(): void
    {
        $response = $this->putJson('/api/v1/postulantes/1', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_delete_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/postulantes/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_document_upload_requires_image_file(): void
    {
        $response = $this->postJson('/api/v1/postulantes/1/documentos', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_applicant_documents_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/postulantes/1/documentos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_requirements_validation_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/postulantes/1/requisitos/validar', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_stripe_session_creation_requires_required_fields(): void
    {
        $response = $this->postJson('/api/v1/pagos/stripe/crear-sesion', []);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Los datos enviados no son validos.',
        ]);
    }

    public function test_payments_by_applicant_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/pagos/postulante/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_payments_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/pagos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_admin_payment_validation_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/pagos/1/validar-admin');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicant_conversion_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/postulantes/1/convertir-alumno');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_academic_gestions_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/gestiones');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_academic_gestion_activation_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/gestiones/1/activar');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_academic_careers_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/carreras', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_academic_quotas_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/cupos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teachers_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/docentes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_creation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/docentes', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_deactivation_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/docentes/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_students_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/alumnos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_detail_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/alumnos/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_requirements_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/requisitos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_requirement_validation_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/requisitos/1/validar', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_subjects_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/materias');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_groups_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/grupos', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_group_update_requires_authentication_token(): void
    {
        $response = $this->putJson('/api/v1/grupos/1', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_group_deactivation_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/grupos/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_group_student_assignment_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/grupos/asignar-alumnos', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_classrooms_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/aulas');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_days_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/dias');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_shifts_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/turnos', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_shift_update_requires_authentication_token(): void
    {
        $response = $this->putJson('/api/v1/turnos/1', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_periods_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/periodos', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_period_update_requires_authentication_token(): void
    {
        $response = $this->putJson('/api/v1/periodos/1', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_period_delete_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/periodos/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_schedules_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/horarios');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_schedule_update_requires_authentication_token(): void
    {
        $response = $this->putJson('/api/v1/horarios/1', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_schedule_delete_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/horarios/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_schedule_creation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/horarios', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_schedule_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/horarios/docente/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_schedule_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/horarios/alumno/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_assignment_creation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asignaciones/docente-materia-grupo', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_assignments_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asignaciones');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_assignments_by_teacher_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asignaciones/docente/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_assignment_deactivation_requires_authentication_token(): void
    {
        $response = $this->deleteJson('/api/v1/asignaciones/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_active_schedule_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-docente/horario-activo');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_entry_attendance_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-docente/marcar-entrada');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_exit_attendance_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-docente/marcar-salida');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_attendance_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-docente');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_absence_generation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-docente/generar-faltas');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_attendance_by_teacher_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-docente/docente/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_active_schedule_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-alumno/horario-activo');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_attendance_mark_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-alumno/marcar');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_register_student_attendance_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-alumno/docente/registrar', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_automatic_absence_generation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/asistencia-alumno/generar-faltas');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_my_student_attendance_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-alumno/mis-asistencias');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_student_attendance_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-alumno/docente/mis-alumnos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_attendance_admin_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/asistencia-alumno');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exams_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/examenes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exam_creation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/examenes', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exam_subjects_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/examenes/1/materias', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exam_questions_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/examenes/1/preguntas', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_question_options_require_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/preguntas/1/opciones', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exam_enable_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/examenes/1/habilitar');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_exam_disable_requires_authentication_token(): void
    {
        $response = $this->patchJson('/api/v1/examenes/1/deshabilitar');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_enabled_exams_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/alumno/examenes/habilitados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_exam_show_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/alumno/examenes/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_exam_answer_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/alumno/examenes/1/responder', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_exam_result_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/alumno/examenes/1/resultado');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_average_calculation_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/promedios/calcular', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_notes_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/notas/alumno/1');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_averages_list_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/promedios');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_approved_averages_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/promedios/aprobados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_failed_averages_require_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/promedios/reprobados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_career_assignment_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/admisiones/asignar-carreras', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_applicants_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/postulantes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_approved_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/aprobados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_failed_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/reprobados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_averages_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/promedios');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_groups_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/grupos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_subject_statistics_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/estadisticas-materia');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teachers_groups_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/docentes-grupos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_groups_most_approved_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/grupos-mayor-aprobados');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_teacher_attendance_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/asistencia-docentes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_student_attendance_report_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/asistencia-alumnos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_report_export_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/reportes/postulantes/exportar?formato=pdf');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_voice_report_command_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/reportes/comando-voz', [
            'texto' => 'listar postulantes',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_bulk_load_csv_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/cargas/csv', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_bulk_load_excel_requires_authentication_token(): void
    {
        $response = $this->postJson('/api/v1/cargas/excel', []);

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_bulk_load_history_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/cargas');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_bulk_load_detail_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/cargas/1/detalle');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_dashboard_summary_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/dashboard/resumen');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_dashboard_attendance_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/dashboard/asistencia');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_dashboard_quotas_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/dashboard/cupos');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }

    public function test_dashboard_exams_requires_authentication_token(): void
    {
        $response = $this->getJson('/api/v1/dashboard/examenes');

        $response->assertStatus(401);
        $response->assertJson([
            'ok' => false,
            'mensaje' => 'Token de autenticacion requerido.',
        ]);
    }
}
