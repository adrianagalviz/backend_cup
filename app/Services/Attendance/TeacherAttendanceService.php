<?php

namespace App\Services\Attendance;

use App\Models\AsistenciaDocenteModel;
use App\Models\DocenteModel;
use App\Models\HorarioClaseModel;
use App\Models\UsuarioModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TeacherAttendanceService
{
    public function detectActiveSchedule(UsuarioModel $user): array
    {
        $teacher = $this->authenticatedTeacher($user);
        $now = $this->now();
        $schedule = $this->candidateSchedule($teacher->id, $now);

        if (! $schedule) {
            return [
                'puede_marcar' => false,
                'mensaje' => 'No existe horario activo o proximo para el docente.',
                'horario' => null,
                'asistencia' => null,
                'fecha' => $now->toDateString(),
                'hora_actual' => $now->format('H:i:s'),
            ];
        }

        $attendance = $this->attendanceFor($teacher->id, $schedule->id, $now);

        return [
            'puede_marcar' => true,
            'mensaje' => 'Existe horario activo o proximo para el docente.',
            'horario' => $this->formatSchedule($schedule),
            'asistencia' => $attendance ? $this->formatAttendance($attendance) : null,
            'fecha' => $now->toDateString(),
            'hora_actual' => $now->format('H:i:s'),
        ];
    }

    public function markEntry(UsuarioModel $user): AsistenciaDocenteModel
    {
        return DB::transaction(function () use ($user): AsistenciaDocenteModel {
            $teacher = $this->authenticatedTeacher($user);
            $now = $this->now();
            $schedule = $this->candidateSchedule($teacher->id, $now);

            if (! $schedule) {
                throw new RuntimeException('No existe horario activo o proximo para marcar entrada.');
            }

            $existing = $this->attendanceFor($teacher->id, $schedule->id, $now);

            if ($existing?->hora_entrada) {
                throw new RuntimeException('La entrada ya fue marcada para este horario.');
            }

            $state = $this->entryState($schedule, $now);
            $automaticExit = $this->dateTimeFor($now, $schedule->hora_fin);

            if ($existing) {
                DB::table('asistencia_docente')
                    ->where('id', $existing->id)
                    ->update([
                        'hora_entrada' => $now,
                        'hora_salida' => $automaticExit,
                        'estado_entrada' => $state,
                        'estado_salida' => 'finalizado',
                        'marcado_por_usuario_id' => $user->id,
                        'actualizado_en' => now(),
                    ]);

                return $this->findAttendance($existing->id);
            }

            $attendanceId = DB::table('asistencia_docente')->insertGetId([
                'docente_id' => $teacher->id,
                'horario_clase_id' => $schedule->id,
                'fecha' => $now->toDateString(),
                'hora_entrada' => $now,
                'hora_salida' => $automaticExit,
                'estado_entrada' => $state,
                'estado_salida' => 'finalizado',
                'marcado_por_usuario_id' => $user->id,
                'creado_en' => now(),
            ]);

            return $this->findAttendance($attendanceId);
        });
    }

    public function markExit(UsuarioModel $user): AsistenciaDocenteModel
    {
        return DB::transaction(function () use ($user): AsistenciaDocenteModel {
            $teacher = $this->authenticatedTeacher($user);
            $now = $this->now();
            $schedule = $this->candidateSchedule($teacher->id, $now);

            if (! $schedule) {
                $schedule = $this->lastScheduleWithOpenAttendance($teacher->id, $now);
            }

            if (! $schedule) {
                throw new RuntimeException('No existe horario activo con entrada marcada para registrar salida.');
            }

            $attendance = $this->attendanceFor($teacher->id, $schedule->id, $now);

            if (! $attendance || ! $attendance->hora_entrada) {
                throw new RuntimeException('No existe entrada previa para registrar salida.');
            }

            if ($attendance->hora_salida) {
                throw new RuntimeException('La salida ya fue marcada para este horario.');
            }

            DB::table('asistencia_docente')
                ->where('id', $attendance->id)
                ->update([
                    'hora_salida' => $now,
                    'estado_salida' => 'finalizado',
                    'marcado_por_usuario_id' => $user->id,
                    'actualizado_en' => now(),
                ]);

            return $this->findAttendance($attendance->id);
        });
    }

    public function generateAutomaticAbsences(?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date, config('app.timezone')) : $this->now();
        $now = $this->now();
        $generated = [];

        $assignments = DB::table('asignacion_docente')
            ->join('horario_clase', 'horario_clase.id', '=', 'asignacion_docente.horario_clase_id')
            ->join('dia', 'dia.id', '=', 'horario_clase.dia_id')
            ->where('asignacion_docente.activo', true)
            ->where('horario_clase.activo', true)
            ->where('dia.orden', (int) $targetDate->isoWeekday())
            ->select([
                'asignacion_docente.docente_id',
                'asignacion_docente.horario_clase_id',
                'horario_clase.hora_fin',
            ])
            ->get();

        foreach ($assignments as $assignment) {
            $scheduleEnd = $this->dateTimeFor($targetDate, $assignment->hora_fin);

            if ($targetDate->isSameDay($now) && $now->lessThanOrEqualTo($scheduleEnd)) {
                continue;
            }

            $attendance = AsistenciaDocenteModel::query()
                ->where('docente_id', $assignment->docente_id)
                ->where('horario_clase_id', $assignment->horario_clase_id)
                ->where('fecha', $targetDate->toDateString())
                ->first();

            if ($attendance?->hora_entrada) {
                continue;
            }

            if ($attendance) {
                DB::table('asistencia_docente')
                    ->where('id', $attendance->id)
                    ->update([
                        'estado_entrada' => 'falta',
                        'estado_salida' => null,
                        'observacion' => 'Falta automatica generada por horario vencido.',
                        'actualizado_en' => now(),
                    ]);

                $generated[] = $this->findAttendance($attendance->id);
                continue;
            }

            $attendanceId = DB::table('asistencia_docente')->insertGetId([
                'docente_id' => $assignment->docente_id,
                'horario_clase_id' => $assignment->horario_clase_id,
                'fecha' => $targetDate->toDateString(),
                'estado_entrada' => 'falta',
                'estado_salida' => null,
                'observacion' => 'Falta automatica generada por horario vencido.',
                'creado_en' => now(),
            ]);

            $generated[] = $this->findAttendance($attendanceId);
        }

        return [
            'fecha' => $targetDate->toDateString(),
            'cantidad_generada' => count($generated),
            'asistencias' => collect($generated)->map(fn (AsistenciaDocenteModel $attendance) => $this->formatAttendance($attendance))->values(),
        ];
    }

    public function listAttendance(array $filters): LengthAwarePaginator
    {
        return AsistenciaDocenteModel::query()
            ->with(['docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->when($filters['docente_id'] ?? null, fn (Builder $query, int|string $id) => $query->where('docente_id', (int) $id))
            ->when($filters['fecha'] ?? null, fn (Builder $query, string $date) => $query->where('fecha', $date))
            ->when($filters['grupo_id'] ?? null, function (Builder $query, int|string $id): void {
                $query->whereHas('horarioClase', fn (Builder $schedule) => $schedule->where('grupo_id', (int) $id));
            })
            ->when($filters['materia_id'] ?? null, function (Builder $query, int|string $id): void {
                $query->whereHas('horarioClase', fn (Builder $schedule) => $schedule->where('materia_id', (int) $id));
            })
            ->when($filters['estado'] ?? null, fn (Builder $query, string $state) => $query->where('estado_entrada', $state))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findAttendance(int $id): AsistenciaDocenteModel
    {
        return AsistenciaDocenteModel::query()
            ->with(['docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->findOrFail($id);
    }

    public function formatAttendance(AsistenciaDocenteModel $attendance): array
    {
        return [
            'id' => $attendance->id,
            'fecha' => $attendance->fecha?->toDateString(),
            'hora_entrada' => $attendance->hora_entrada,
            'hora_salida' => $attendance->hora_salida,
            'estado_entrada' => $attendance->estado_entrada,
            'estado_salida' => $attendance->estado_salida,
            'observacion' => $attendance->observacion,
            'docente' => [
                'id' => $attendance->docente?->id,
                'nombres' => $attendance->docente?->persona?->nombres,
                'apellido_paterno' => $attendance->docente?->persona?->apellido_paterno,
            ],
            'horario' => $attendance->horarioClase ? $this->formatSchedule($attendance->horarioClase) : null,
        ];
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

    private function candidateSchedule(int $teacherId, Carbon $now): ?HorarioClaseModel
    {
        $dayOrder = (int) $now->isoWeekday();

        return HorarioClaseModel::query()
            ->with(['dia', 'turno', 'periodo', 'materia', 'grupo', 'aula'])
            ->where('activo', true)
            ->whereHas('dia', fn (Builder $query) => $query->where('orden', $dayOrder)->where('activo', true))
            ->whereHas('asignacionesDocentes', fn (Builder $query) => $query
                ->where('docente_id', $teacherId)
                ->where('activo', true))
            ->get()
            ->first(fn (HorarioClaseModel $schedule): bool => $this->isWithinAttendanceWindow($schedule, $now));
    }

    private function lastScheduleWithOpenAttendance(int $teacherId, Carbon $now): ?HorarioClaseModel
    {
        $attendance = AsistenciaDocenteModel::query()
            ->where('docente_id', $teacherId)
            ->where('fecha', $now->toDateString())
            ->whereNotNull('hora_entrada')
            ->whereNull('hora_salida')
            ->orderByDesc('id')
            ->first();

        return $attendance ? HorarioClaseModel::query()->with(['dia', 'turno', 'periodo', 'materia', 'grupo', 'aula'])->find($attendance->horario_clase_id) : null;
    }

    private function attendanceFor(int $teacherId, int $scheduleId, Carbon $now): ?AsistenciaDocenteModel
    {
        return AsistenciaDocenteModel::query()
            ->with(['docente.persona', 'horarioClase.dia', 'horarioClase.turno', 'horarioClase.periodo', 'horarioClase.materia', 'horarioClase.grupo', 'horarioClase.aula'])
            ->where('docente_id', $teacherId)
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

    private function entryState(HorarioClaseModel $schedule, Carbon $now): string
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
