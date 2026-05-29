<?php

namespace App\Services\Attendance;

use App\Models\AlumnoModel;
use App\Models\AsistenciaAlumnoModel;
use App\Models\DocenteModel;
use App\Models\HorarioClaseModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StudentAttendanceService
{
    public function detectActiveSchedule(UsuarioModel $user): array
    {
        $student = $this->authenticatedStudent($user);
        $now = $this->now();
        $schedule = $this->candidateScheduleForStudent($student->id, $now);

        if (! $schedule) {
            return [
                'puede_marcar' => false,
                'mensaje' => 'No existe horario activo o proximo para el alumno.',
                'horario' => null,
                'asistencia' => null,
                'fecha' => $now->toDateString(),
                'hora_actual' => $now->format('H:i:s'),
            ];
        }

        $attendance = $this->attendanceFor($student->id, $schedule->id, $now);

        return [
            'puede_marcar' => true,
            'mensaje' => 'Existe horario activo o proximo para el alumno.',
            'horario' => $this->formatSchedule($schedule),
            'asistencia' => $attendance ? $this->formatAttendance($attendance) : null,
            'fecha' => $now->toDateString(),
            'hora_actual' => $now->format('H:i:s'),
        ];
    }

    public function markByStudent(UsuarioModel $user): AsistenciaAlumnoModel
    {
        return DB::transaction(function () use ($user): AsistenciaAlumnoModel {
            $student = $this->authenticatedStudent($user);
            $now = $this->now();
            $schedule = $this->candidateScheduleForStudent($student->id, $now);

            if (! $schedule) {
                throw new RuntimeException('No existe horario activo o proximo para marcar asistencia.');
            }

            if ($this->attendanceFor($student->id, $schedule->id, $now)) {
                throw new RuntimeException('La asistencia ya fue registrada para este horario.');
            }

            $attendanceId = DB::table('asistencia_alumno')->insertGetId([
                'alumno_id' => $student->id,
                'horario_clase_id' => $schedule->id,
                'docente_id' => null,
                'fecha' => $now->toDateString(),
                'hora_marcada' => $now,
                'estado_asistencia' => $this->attendanceState($schedule, $now),
                'registrado_por_usuario_id' => $user->id,
                'creado_en' => now(),
            ]);

            return $this->findAttendance($attendanceId);
        });
    }

    public function registerByTeacher(UsuarioModel $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data): array {
            $teacher = $this->authenticatedTeacher($user);
            $now = $this->now();
            $schedule = HorarioClaseModel::query()->with(['dia', 'turno', 'periodo', 'materia', 'grupo', 'aula'])->findOrFail($data['horario_clase_id']);

            $this->validateTeacherCanRegister($teacher, $schedule, $now);

            $registered = [];

            foreach ($data['asistencias'] as $item) {
                $student = AlumnoModel::query()->findOrFail($item['alumno_id']);
                $this->validateStudentBelongsToScheduleGroup($student->id, $schedule->grupo_id);

                $state = $item['estado_asistencia'] ?? $this->attendanceState($schedule, $now);
                $markedAt = $state === 'falta' ? null : $now;

                DB::table('asistencia_alumno')->updateOrInsert(
                    [
                        'alumno_id' => $student->id,
                        'horario_clase_id' => $schedule->id,
                        'fecha' => $now->toDateString(),
                    ],
                    [
                        'docente_id' => $teacher->id,
                        'hora_marcada' => $markedAt,
                        'estado_asistencia' => $state,
                        'registrado_por_usuario_id' => $user->id,
                        'observacion' => $item['observacion'] ?? null,
                        'actualizado_en' => now(),
                        'creado_en' => now(),
                    ]
                );

                $registered[] = $this->attendanceFor($student->id, $schedule->id, $now);
            }

            return [
                'horario' => $this->formatSchedule($schedule),
                'cantidad_registrada' => count($registered),
                'asistencias' => collect($registered)->filter()->map(fn (AsistenciaAlumnoModel $attendance) => $this->formatAttendance($attendance))->values(),
            ];
        });
    }

    public function generateAutomaticAbsences(?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date, config('app.timezone')) : $this->now();
        $now = $this->now();
        $generated = [];

        $schedules = HorarioClaseModel::query()
            ->with('dia')
            ->where('activo', true)
            ->whereHas('dia', fn (Builder $query) => $query->where('orden', (int) $targetDate->isoWeekday()))
            ->get();

        foreach ($schedules as $schedule) {
            $scheduleEnd = $this->dateTimeFor($targetDate, $schedule->hora_fin);

            if ($targetDate->isSameDay($now) && $now->lessThanOrEqualTo($scheduleEnd)) {
                continue;
            }

            $studentIds = DB::table('grupo_alumno')
                ->where('grupo_id', $schedule->grupo_id)
                ->where('activo', true)
                ->pluck('alumno_id');

            foreach ($studentIds as $studentId) {
                $attendance = AsistenciaAlumnoModel::query()
                    ->where('alumno_id', $studentId)
                    ->where('horario_clase_id', $schedule->id)
                    ->where('fecha', $targetDate->toDateString())
                    ->first();

                if ($attendance) {
                    continue;
                }

                $attendanceId = DB::table('asistencia_alumno')->insertGetId([
                    'alumno_id' => $studentId,
                    'horario_clase_id' => $schedule->id,
                    'fecha' => $targetDate->toDateString(),
                    'estado_asistencia' => 'falta',
                    'observacion' => 'Falta automatica generada por horario vencido.',
                    'creado_en' => now(),
                ]);

                $generated[] = $this->findAttendance($attendanceId);
            }
        }

        return [
            'fecha' => $targetDate->toDateString(),
            'cantidad_generada' => count($generated),
            'asistencias' => collect($generated)->map(fn (AsistenciaAlumnoModel $attendance) => $this->formatAttendance($attendance))->values(),
        ];
    }

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->attendanceQuery($filters)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function listForStudent(UsuarioModel $user, array $filters): LengthAwarePaginator
    {
        $student = $this->authenticatedStudent($user);
        $filters['alumno_id'] = $student->id;

        return $this->listForAdmin($filters);
    }

    public function listForTeacherStudents(UsuarioModel $user, array $filters): LengthAwarePaginator
    {
        $teacher = $this->authenticatedTeacher($user);
        $groupIds = DB::table('asignacion_docente')
            ->where('docente_id', $teacher->id)
            ->where('activo', true)
            ->distinct()
            ->pluck('grupo_id');

        return $this->attendanceQuery($filters)
            ->whereHas('horarioClase', fn (Builder $schedule) => $schedule->whereIn('grupo_id', $groupIds))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findAttendance(int $id): AsistenciaAlumnoModel
    {
        return AsistenciaAlumnoModel::query()
            ->with(['alumno.persona', 'docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->findOrFail($id);
    }

    public function formatAttendance(AsistenciaAlumnoModel $attendance): array
    {
        return [
            'id' => $attendance->id,
            'fecha' => $attendance->fecha?->toDateString(),
            'hora_marcada' => $attendance->hora_marcada,
            'estado_asistencia' => $attendance->estado_asistencia,
            'observacion' => $attendance->observacion,
            'alumno' => [
                'id' => $attendance->alumno?->id,
                'codigo_alumno' => $attendance->alumno?->codigo_alumno,
                'nombres' => $attendance->alumno?->persona?->nombres,
                'apellido_paterno' => $attendance->alumno?->persona?->apellido_paterno,
            ],
            'docente' => $attendance->docente ? [
                'id' => $attendance->docente->id,
                'nombres' => $attendance->docente->persona?->nombres,
                'apellido_paterno' => $attendance->docente->persona?->apellido_paterno,
            ] : null,
            'horario' => $attendance->horarioClase ? $this->formatSchedule($attendance->horarioClase) : null,
        ];
    }

    private function attendanceQuery(array $filters): Builder
    {
        return AsistenciaAlumnoModel::query()
            ->with(['alumno.persona', 'docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->when($filters['alumno_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('alumno_id', (int) $id))
            ->when($filters['docente_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('docente_id', (int) $id))
            ->when($filters['fecha'] ?? null, fn (Builder $query, string $date) => $query->where('fecha', $date))
            ->when($filters['estado'] ?? null, fn (Builder $query, string $state) => $query->where('estado_asistencia', $state))
            ->when($filters['grupo_id'] ?? null, fn (Builder $query, int|string $id) => $query->whereHas('horarioClase', fn (Builder $schedule) => $schedule->where('grupo_id', (int) $id)))
            ->when($filters['materia_id'] ?? null, fn (Builder $query, int|string $id) => $query->whereHas('horarioClase', fn (Builder $schedule) => $schedule->where('materia_id', (int) $id)));
    }

    private function authenticatedStudent(UsuarioModel $user): AlumnoModel
    {
        if ($user->rol?->nombre !== 'alumno' || ! $user->alumno) {
            throw new RuntimeException('El usuario autenticado debe ser alumno.');
        }

        return $user->alumno;
    }

    private function authenticatedTeacher(UsuarioModel $user): DocenteModel
    {
        if ($user->rol?->nombre !== 'docente' || ! $user->docente) {
            throw new RuntimeException('El usuario autenticado debe ser docente.');
        }

        if (! $user->docente->activo) {
            throw new RuntimeException('El docente autenticado no esta activo.');
        }

        return $user->docente;
    }

    private function candidateScheduleForStudent(int $studentId, Carbon $now): ?HorarioClaseModel
    {
        $groupIds = DB::table('grupo_alumno')
            ->where('alumno_id', $studentId)
            ->where('activo', true)
            ->pluck('grupo_id');

        return HorarioClaseModel::query()
            ->with(['dia', 'turno', 'periodo', 'materia', 'grupo', 'aula'])
            ->where('activo', true)
            ->whereIn('grupo_id', $groupIds)
            ->whereHas('dia', fn (Builder $query) => $query->where('orden', (int) $now->isoWeekday())->where('activo', true))
            ->get()
            ->first(fn (HorarioClaseModel $schedule): bool => $this->isWithinAttendanceWindow($schedule, $now));
    }

    private function validateTeacherCanRegister(DocenteModel $teacher, HorarioClaseModel $schedule, Carbon $now): void
    {
        $assigned = DB::table('asignacion_docente')
            ->where('docente_id', $teacher->id)
            ->where('grupo_id', $schedule->grupo_id)
            ->where('materia_id', $schedule->materia_id)
            ->where('horario_clase_id', $schedule->id)
            ->where('activo', true)
            ->exists();

        if (! $assigned) {
            throw new RuntimeException('El docente no tiene asignado ese grupo, materia y horario.');
        }

        if (! $this->isWithinAttendanceWindow($schedule, $now)) {
            throw new RuntimeException('El horario no esta activo para registrar asistencia.');
        }
    }

    private function validateStudentBelongsToScheduleGroup(int $studentId, int $groupId): void
    {
        $exists = DB::table('grupo_alumno')
            ->where('alumno_id', $studentId)
            ->where('grupo_id', $groupId)
            ->where('activo', true)
            ->exists();

        if (! $exists) {
            throw new RuntimeException('Uno de los alumnos no pertenece al grupo del horario.');
        }
    }

    private function attendanceFor(int $studentId, int $scheduleId, Carbon $now): ?AsistenciaAlumnoModel
    {
        return AsistenciaAlumnoModel::query()
            ->with(['alumno.persona', 'docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->where('alumno_id', $studentId)
            ->where('horario_clase_id', $scheduleId)
            ->where('fecha', $now->toDateString())
            ->first();
    }

    private function isWithinAttendanceWindow(HorarioClaseModel $schedule, Carbon $now): bool
    {
        $start = $this->dateTimeFor($now, $schedule->hora_inicio);
        $end = $this->dateTimeFor($now, $schedule->hora_fin);

        return $now->betweenIncluded($start->copy()->subMinutes(30), $end);
    }

    private function attendanceState(HorarioClaseModel $schedule, Carbon $now): string
    {
        $start = $this->dateTimeFor($now, $schedule->hora_inicio);

        return $now->greaterThan($start->copy()->addMinutes(30)) ? 'retraso' : 'presente';
    }

    private function formatSchedule(HorarioClaseModel $schedule): array
    {
        return [
            'id' => $schedule->id,
            'hora_inicio' => $schedule->hora_inicio,
            'hora_fin' => $schedule->hora_fin,
            'dia' => $schedule->dia?->nombre,
            'turno' => $schedule->turno?->nombre,
            'periodo' => $schedule->periodo?->numero_periodo,
            'materia' => $schedule->materia?->nombre,
            'grupo' => $schedule->grupo?->nombre,
            'aula' => $schedule->aula?->ubicacion,
        ];
    }

    private function dateTimeFor(Carbon $date, string $time): Carbon
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date->toDateString().' '.substr($time, 0, 5).':00', config('app.timezone'));
    }

    private function now(): Carbon
    {
        return now(config('app.timezone'));
    }
}
