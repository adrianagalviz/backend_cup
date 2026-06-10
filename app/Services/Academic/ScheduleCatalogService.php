<?php

namespace App\Services\Academic;

use App\Models\DiaModel;
use App\Models\PeriodoModel;
use App\Models\TurnoModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScheduleCatalogService
{
    private const SHIFT_MINUTES = 360;
    private const PERIOD_MINUTES = 90;

    public function listDays(): Collection
    {
        $this->ensureBaseDays();

        return DiaModel::query()
            ->where('activo', true)
            ->orderBy('orden')
            ->get();
    }

    public function createShift(array $data): TurnoModel
    {
        $this->ensureStartBeforeEnd($data['hora_inicio'], $data['hora_fin']);
        $this->ensureSixHourShift($data['hora_inicio'], $data['hora_fin']);

        $id = DB::transaction(function () use ($data): int {
            $shiftId = DB::table('turno')->insertGetId([
                'nombre' => $data['nombre'],
                'hora_inicio' => $data['hora_inicio'],
                'hora_fin' => $data['hora_fin'],
                'activo' => (bool) ($data['activo'] ?? true),
            ]);

            $this->generatePeriodsForShift($shiftId, $data['hora_inicio'], $data['hora_fin']);

            return $shiftId;
        });

        return $this->findShift($id);
    }

    public function listShifts(array $filters): LengthAwarePaginator
    {
        return TurnoModel::query()
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('hora_inicio')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function updateShift(int $id, array $data): TurnoModel
    {
        $shift = $this->findShift($id);
        $shiftData = array_intersect_key($data, array_flip([
            'nombre',
            'hora_inicio',
            'hora_fin',
            'activo',
        ]));

        $start = $shiftData['hora_inicio'] ?? $shift->hora_inicio;
        $end = $shiftData['hora_fin'] ?? $shift->hora_fin;

        if (array_key_exists('hora_inicio', $shiftData) || array_key_exists('hora_fin', $shiftData)) {
            $this->ensureStartBeforeEnd($start, $end);
            $this->ensureSixHourShift($start, $end);
            $this->ensureExistingPeriodsInsideShift($shift->id, $start, $end);
        }

        if ($shiftData !== []) {
            DB::transaction(function () use ($shift, $shiftData, $start, $end): void {
                DB::table('turno')->where('id', $shift->id)->update($shiftData);

                if (array_key_exists('hora_inicio', $shiftData) || array_key_exists('hora_fin', $shiftData)) {
                    $this->ensureShiftHasNoSchedules($shift->id);
                    $this->generatePeriodsForShift($shift->id, $start, $end);
                }
            });
        }

        return $this->findShift($id);
    }

    public function deleteShift(int $id): void
    {
        $shift = $this->findShift($id);

        $this->ensureShiftHasNoSchedules($shift->id, 'No se puede eliminar un turno que ya tiene horarios asignados.');

        DB::transaction(function () use ($shift): void {
            DB::table('periodo')->where('turno_id', $shift->id)->delete();
            DB::table('turno')->where('id', $shift->id)->delete();
        });
    }

    public function createPeriod(array $data): PeriodoModel
    {
        $shift = $this->findShift((int) $data['turno_id']);
        $this->ensureNinetyMinutes($data['hora_inicio'], $data['hora_fin']);
        $this->ensurePeriodInsideShift($shift, $data['hora_inicio'], $data['hora_fin']);

        $id = DB::table('periodo')->insertGetId([
            'turno_id' => $shift->id,
            'numero_periodo' => $data['numero_periodo'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'duracion_minutos' => self::PERIOD_MINUTES,
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

        return $this->findPeriod($id);
    }

    public function listPeriods(array $filters): LengthAwarePaginator
    {
        return PeriodoModel::query()
            ->with('turno')
            ->when($filters['turno_id'] ?? null, function (Builder $query, int|string $shiftId): void {
                $query->where('turno_id', (int) $shiftId);
            })
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('turno_id')
            ->orderBy('numero_periodo')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findShift(int $id): TurnoModel
    {
        return TurnoModel::query()->findOrFail($id);
    }

    public function findPeriod(int $id): PeriodoModel
    {
        return PeriodoModel::query()
            ->with('turno')
            ->findOrFail($id);
    }

    public function formatDay(DiaModel $day): array
    {
        return [
            'id' => $day->id,
            'nombre' => $day->nombre,
            'orden' => $day->orden,
            'activo' => $day->activo,
        ];
    }

    public function formatShift(TurnoModel $shift): array
    {
        return [
            'id' => $shift->id,
            'nombre' => $shift->nombre,
            'hora_inicio' => $shift->hora_inicio,
            'hora_fin' => $shift->hora_fin,
            'activo' => $shift->activo,
        ];
    }

    public function formatPeriod(PeriodoModel $period): array
    {
        return [
            'id' => $period->id,
            'numero_periodo' => $period->numero_periodo,
            'hora_inicio' => $period->hora_inicio,
            'hora_fin' => $period->hora_fin,
            'duracion_minutos' => $period->duracion_minutos,
            'activo' => $period->activo,
            'turno' => [
                'id' => $period->turno?->id,
                'nombre' => $period->turno?->nombre,
                'hora_inicio' => $period->turno?->hora_inicio,
                'hora_fin' => $period->turno?->hora_fin,
            ],
        ];
    }

    private function ensureBaseDays(): void
    {
        DB::table('dia')->where('orden', '>', 6)->update(['activo' => false]);
        DB::table('dia')->where('nombre', 'Domingo')->update(['activo' => false]);

        foreach ($this->baseDays() as $day) {
            DB::table('dia')->updateOrInsert(
                ['nombre' => $day['nombre']],
                [
                    'orden' => $day['orden'],
                    'activo' => true,
                ]
            );
        }
    }

    private function ensureShiftHasNoSchedules(int $shiftId, string $message = 'No se puede regenerar periodos de un turno que ya tiene horarios asignados.'): void
    {
        $hasSchedules = DB::table('horario_clase')->where('turno_id', $shiftId)->exists();

        if ($hasSchedules) {
            throw new RuntimeException($message);
        }
    }

    private function baseDays(): array
    {
        return [
            ['nombre' => 'Lunes', 'orden' => 1],
            ['nombre' => 'Martes', 'orden' => 2],
            ['nombre' => 'Miercoles', 'orden' => 3],
            ['nombre' => 'Jueves', 'orden' => 4],
            ['nombre' => 'Viernes', 'orden' => 5],
            ['nombre' => 'Sabado', 'orden' => 6],
        ];
    }

    private function generatePeriodsForShift(int $shiftId, string $start, string $end): void
    {
        DB::table('periodo')->where('turno_id', $shiftId)->delete();

        $current = $this->time($start);
        $limit = $this->time($end);
        $number = 1;

        while ($current->copy()->addMinutes(self::PERIOD_MINUTES)->lessThanOrEqualTo($limit)) {
            $periodStart = $current->format('H:i');
            $periodEnd = $current->copy()->addMinutes(self::PERIOD_MINUTES)->format('H:i');

            DB::table('periodo')->insert([
                'turno_id' => $shiftId,
                'numero_periodo' => $number,
                'hora_inicio' => $periodStart,
                'hora_fin' => $periodEnd,
                'duracion_minutos' => self::PERIOD_MINUTES,
                'activo' => true,
            ]);

            $current->addMinutes(self::PERIOD_MINUTES);
            $number++;
        }
    }

    private function ensureStartBeforeEnd(string $start, string $end): void
    {
        if ($this->time($start)->greaterThanOrEqualTo($this->time($end))) {
            throw new RuntimeException('La hora de inicio debe ser menor que la hora de fin.');
        }
    }

    private function ensureNinetyMinutes(string $start, string $end): void
    {
        $minutes = $this->diffInMinutes($start, $end);

        if ($minutes !== self::PERIOD_MINUTES) {
            throw new RuntimeException('Cada periodo debe durar exactamente 90 minutos.');
        }
    }

    private function ensureSixHourShift(string $start, string $end): void
    {
        $minutes = $this->diffInMinutes($start, $end);

        if ($minutes !== self::SHIFT_MINUTES) {
            throw new RuntimeException('El turno debe durar exactamente 6 horas y dividirse en 4 periodos de 90 minutos.');
        }
    }

    private function diffInMinutes(string $start, string $end): int
    {
        return (int) $this->time($start)->diffInMinutes($this->time($end), false);
    }

    private function ensurePeriodInsideShift(TurnoModel $shift, string $start, string $end): void
    {
        $periodStart = $this->time($start);
        $periodEnd = $this->time($end);
        $shiftStart = $this->time($shift->hora_inicio);
        $shiftEnd = $this->time($shift->hora_fin);

        if ($periodStart->lessThan($shiftStart) || $periodEnd->greaterThan($shiftEnd)) {
            throw new RuntimeException('El periodo debe estar dentro del horario del turno.');
        }
    }

    private function ensureExistingPeriodsInsideShift(int $shiftId, string $start, string $end): void
    {
        $shiftStart = $this->time($start);
        $shiftEnd = $this->time($end);

        $periodsOutside = PeriodoModel::query()
            ->where('turno_id', $shiftId)
            ->get()
            ->contains(function (PeriodoModel $period) use ($shiftStart, $shiftEnd): bool {
                $periodStart = $this->time($period->hora_inicio);
                $periodEnd = $this->time($period->hora_fin);

                return $periodStart->lessThan($shiftStart) || $periodEnd->greaterThan($shiftEnd);
            });

        if ($periodsOutside) {
            throw new RuntimeException('No se puede ajustar el turno porque existen periodos fuera del nuevo rango.');
        }
    }

    private function time(string $value): Carbon
    {
        return Carbon::createFromFormat('H:i', substr($value, 0, 5));
    }
}
