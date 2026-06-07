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
    public function listDays(): Collection
    {
        $this->ensureBaseDays();

        return DiaModel::query()
            ->orderBy('orden')
            ->get();
    }

    public function createShift(array $data): TurnoModel
    {
        $this->ensureStartBeforeEnd($data['hora_inicio'], $data['hora_fin']);

        $id = DB::table('turno')->insertGetId([
            'nombre' => $data['nombre'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'activo' => (bool) ($data['activo'] ?? true),
        ]);

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
            $this->ensureExistingPeriodsInsideShift($shift->id, $start, $end);
        }

        if ($shiftData !== []) {
            DB::table('turno')->where('id', $shift->id)->update($shiftData);
        }

        return $this->findShift($id);
    }

    public function createPeriod(array $data): PeriodoModel
    {
        $shift = $this->findShift((int) $data['turno_id']);
        $this->ensureFortyFiveMinutes($data['hora_inicio'], $data['hora_fin']);
        $this->ensurePeriodInsideShift($shift, $data['hora_inicio'], $data['hora_fin']);

        $id = DB::table('periodo')->insertGetId([
            'turno_id' => $shift->id,
            'numero_periodo' => $data['numero_periodo'],
            'hora_inicio' => $data['hora_inicio'],
            'hora_fin' => $data['hora_fin'],
            'duracion_minutos' => 45,
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

    private function baseDays(): array
    {
        return [
            ['nombre' => 'Lunes', 'orden' => 1],
            ['nombre' => 'Martes', 'orden' => 2],
            ['nombre' => 'Miercoles', 'orden' => 3],
            ['nombre' => 'Jueves', 'orden' => 4],
            ['nombre' => 'Viernes', 'orden' => 5],
            ['nombre' => 'Sabado', 'orden' => 6],
            ['nombre' => 'Domingo', 'orden' => 7],
        ];
    }

    private function ensureStartBeforeEnd(string $start, string $end): void
    {
        if ($this->time($start)->greaterThanOrEqualTo($this->time($end))) {
            throw new RuntimeException('La hora de inicio debe ser menor que la hora de fin.');
        }
    }

    private function ensureFortyFiveMinutes(string $start, string $end): void
    {
        $minutes = $this->time($start)->diffInMinutes($this->time($end), false);

        if ($minutes !== 45) {
            throw new RuntimeException('Cada periodo debe durar exactamente 45 minutos.');
        }
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
