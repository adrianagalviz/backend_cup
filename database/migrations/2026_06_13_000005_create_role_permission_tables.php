<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rol DROP CONSTRAINT IF EXISTS rol_nombre_check');

        Schema::create('permiso', function (Blueprint $table): void {
            $table->id();
            $table->string('codigo', 80)->unique();
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->string('categoria', 80);
            $table->boolean('activo')->default(true);
        });

        Schema::create('rol_permiso', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rol_id')->constrained('rol')->cascadeOnDelete();
            $table->foreignId('permiso_id')->constrained('permiso')->cascadeOnDelete();
            $table->timestamp('creado_en')->useCurrent();
            $table->unique(['rol_id', 'permiso_id']);
        });

        $this->seedPermissions();
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('permiso');

        DB::statement("ALTER TABLE rol ADD CONSTRAINT rol_nombre_check CHECK (nombre IN ('administrador', 'docente', 'alumno'))");
    }

    private function seedPermissions(): void
    {
        $permissions = [
            ['codigo' => 'dashboard.ver', 'nombre' => 'Ver dashboard', 'categoria' => 'Inicio', 'descripcion' => 'Acceso al panel principal del rol.'],
            ['codigo' => 'perfil.ver', 'nombre' => 'Ver perfil', 'categoria' => 'Inicio', 'descripcion' => 'Acceso al perfil autenticado.'],
            ['codigo' => 'usuarios.gestionar', 'nombre' => 'Gestionar usuarios', 'categoria' => 'Administracion', 'descripcion' => 'Listado y mantenimiento de usuarios administradores.'],
            ['codigo' => 'roles.gestionar', 'nombre' => 'Gestionar roles y permisos', 'categoria' => 'Administracion', 'descripcion' => 'Creacion de roles y modificacion de permisos.'],
            ['codigo' => 'postulantes.gestionar', 'nombre' => 'Gestionar postulantes', 'categoria' => 'Administracion', 'descripcion' => 'Registro, revision y edicion de postulantes.'],
            ['codigo' => 'requisitos.gestionar', 'nombre' => 'Gestionar requisitos', 'categoria' => 'Administracion', 'descripcion' => 'Revision de requisitos documentales.'],
            ['codigo' => 'pagos.gestionar', 'nombre' => 'Gestionar pagos', 'categoria' => 'Administracion', 'descripcion' => 'Consulta y validacion administrativa de pagos.'],
            ['codigo' => 'alumnos.ver', 'nombre' => 'Ver alumnos', 'categoria' => 'Administracion', 'descripcion' => 'Consulta de alumnos y detalle academico.'],
            ['codigo' => 'gestion_academica.gestionar', 'nombre' => 'Gestionar gestion academica', 'categoria' => 'Academico', 'descripcion' => 'Gestiones, carreras y cupos.'],
            ['codigo' => 'docentes.gestionar', 'nombre' => 'Gestionar docentes', 'categoria' => 'Academico', 'descripcion' => 'Registro y mantenimiento de docentes.'],
            ['codigo' => 'horarios.gestionar', 'nombre' => 'Gestionar horarios', 'categoria' => 'Academico', 'descripcion' => 'Catalogos, aulas, turnos, grupos y horarios.'],
            ['codigo' => 'horarios.ver_propios', 'nombre' => 'Ver horarios propios', 'categoria' => 'Academico', 'descripcion' => 'Consulta de horarios propios de docente o alumno.'],
            ['codigo' => 'asignaciones.gestionar', 'nombre' => 'Gestionar asignaciones', 'categoria' => 'Academico', 'descripcion' => 'Asignacion de docentes a materias y grupos.'],
            ['codigo' => 'asistencia_docente.gestionar', 'nombre' => 'Gestionar asistencia docente', 'categoria' => 'Seguimiento', 'descripcion' => 'Revision administrativa de asistencia docente.'],
            ['codigo' => 'asistencia_docente.marcar', 'nombre' => 'Marcar asistencia docente', 'categoria' => 'Seguimiento', 'descripcion' => 'Registro de entrada y salida docente.'],
            ['codigo' => 'asistencia_alumnos.gestionar', 'nombre' => 'Gestionar asistencia de alumnos', 'categoria' => 'Seguimiento', 'descripcion' => 'Revision administrativa de asistencia de alumnos.'],
            ['codigo' => 'asistencia_alumnos.marcar', 'nombre' => 'Marcar asistencia de alumno', 'categoria' => 'Seguimiento', 'descripcion' => 'Marcado de asistencia por alumno.'],
            ['codigo' => 'asistencia_alumnos.registrar_docente', 'nombre' => 'Registrar asistencia como docente', 'categoria' => 'Seguimiento', 'descripcion' => 'Registro de asistencia de alumnos por docente.'],
            ['codigo' => 'examenes.gestionar', 'nombre' => 'Gestionar examenes', 'categoria' => 'Seguimiento', 'descripcion' => 'Creacion y habilitacion de examenes.'],
            ['codigo' => 'examenes.resolver', 'nombre' => 'Resolver examenes', 'categoria' => 'Seguimiento', 'descripcion' => 'Acceso del alumno a examenes habilitados.'],
            ['codigo' => 'notas.gestionar', 'nombre' => 'Gestionar notas', 'categoria' => 'Seguimiento', 'descripcion' => 'Calculo y consulta administrativa de notas.'],
            ['codigo' => 'notas.ver_propias', 'nombre' => 'Ver notas propias', 'categoria' => 'Seguimiento', 'descripcion' => 'Consulta de notas del alumno autenticado.'],
            ['codigo' => 'admision.gestionar', 'nombre' => 'Gestionar admision final', 'categoria' => 'Seguimiento', 'descripcion' => 'Asignacion final de carreras.'],
            ['codigo' => 'reportes.gestionar', 'nombre' => 'Gestionar reportes', 'categoria' => 'Reportes', 'descripcion' => 'Consulta, comando de voz y exportacion de reportes.'],
            ['codigo' => 'carga_masiva.gestionar', 'nombre' => 'Gestionar carga masiva', 'categoria' => 'Reportes', 'descripcion' => 'Importacion de datos por CSV o Excel.'],
        ];

        DB::table('permiso')->upsert($permissions, ['codigo'], ['nombre', 'descripcion', 'categoria', 'activo']);

        $adminRoleId = DB::table('rol')->where('nombre', 'administrador')->value('id');
        $teacherRoleId = DB::table('rol')->where('nombre', 'docente')->value('id');
        $studentRoleId = DB::table('rol')->where('nombre', 'alumno')->value('id');

        $this->assignPermissions($adminRoleId, array_column($permissions, 'codigo'));
        $this->assignPermissions($teacherRoleId, [
            'dashboard.ver',
            'perfil.ver',
            'horarios.ver_propios',
            'asistencia_docente.marcar',
            'asistencia_alumnos.registrar_docente',
        ]);
        $this->assignPermissions($studentRoleId, [
            'dashboard.ver',
            'perfil.ver',
            'horarios.ver_propios',
            'asistencia_alumnos.marcar',
            'examenes.resolver',
            'notas.ver_propias',
        ]);
    }

    private function assignPermissions(?int $roleId, array $codes): void
    {
        if (!$roleId) {
            return;
        }

        $permissionIds = DB::table('permiso')->whereIn('codigo', $codes)->pluck('id');
        $rows = $permissionIds->map(fn (int $permissionId): array => [
            'rol_id' => $roleId,
            'permiso_id' => $permissionId,
        ])->all();

        DB::table('rol_permiso')->insertOrIgnore($rows);
    }
};
