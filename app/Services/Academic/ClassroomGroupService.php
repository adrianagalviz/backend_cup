<?php

namespace App\Services\Academic;

use App\Models\AlumnoModel;
use App\Models\AulaModel;
use App\Models\GrupoAlumnoModel;
use App\Models\GrupoModel;
use App\Models\MateriaModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClassroomGroupService
{
    public function listSubjects(): Collection
    {
        $this->ensureBaseSubjects();

        return MateriaModel::query()
            ->orderBy('nombre')
            ->get();
    }

    public function createGroup(array $data): GrupoModel
    {
        $id = DB::table('grupo')->insertGetId([
            'gestion_academica_id' => $data['gestion_academica_id'],
            'nombre' => $data['nombre'],
            'cupo_maximo' => $data['cupo_maximo'] ?? 70,
            'activo' => (bool) ($data['activo'] ?? true),
            'creado_en' => now(),
        ]);

        return $this->findGroup($id);
    }

    public function listGroups(array $filters): LengthAwarePaginator
    {
        return GrupoModel::query()
            ->with('gestionAcademica')
            ->when($filters['gestion_academica_id'] ?? null, function (Builder $query, int|string $gestionId): void {
                $query->where('gestion_academica_id', (int) $gestionId);
            })
            ->when(array_key_exists('activo', $filters), function (Builder $query) use ($filters): void {
                $query->where('activo', filter_var($filters['activo'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderByDesc('gestion_academica_id')
            ->orderBy('nombre')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findGroup(int $id): GrupoModel
    {
        return GrupoModel::query()
            ->with('gestionAcademica')
            ->findOrFail($id);
    }

    public function groupStudents(int $groupId): Collection
    {
        $this->findGroup($groupId);

        return AlumnoModel::query()
            ->with(['persona', 'usuario', 'gestionAcademica'])
            ->whereHas('grupos', function (Builder $query) use ($groupId): void {
                $query->where('grupo_id', $groupId)->where('activo', true);
            })
            ->orderBy('id')
            ->get();
    }

    public function calculateRequiredGroups(int $gestionAcademicaId): array
    {
        $totalStudents = AlumnoModel::query()
            ->where('gestion_academica_id', $gestionAcademicaId)
            ->count();

        return [
            'gestion_academica_id' => $gestionAcademicaId,
            'total_inscritos' => $totalStudents,
            'cupo_maximo_por_grupo' => 70,
            'grupos_necesarios' => (int) ceil($totalStudents / 70),
        ];
    }

    public function assignStudentsToGroup(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $group = $this->findGroup((int) $data['grupo_id']);

            if ((int) $group->gestion_academica_id !== (int) $data['gestion_academica_id']) {
                throw new RuntimeException('El grupo no corresponde a la gestion academica indicada.');
            }

            $currentCount = GrupoAlumnoModel::query()
                ->where('grupo_id', $group->id)
                ->where('activo', true)
                ->count();

            $availableSlots = (int) $group->cupo_maximo - $currentCount;

            if ($availableSlots <= 0) {
                throw new RuntimeException('El grupo ya alcanzo su cupo maximo.');
            }

            $students = $this->availableStudents((int) $data['gestion_academica_id'], $data['alumno_ids'] ?? null)
                ->take($availableSlots);

            $assigned = [];

            foreach ($students as $student) {
                DB::table('grupo_alumno')->updateOrInsert(
                    [
                        'grupo_id' => $group->id,
                        'alumno_id' => $student->id,
                    ],
                    [
                        'fecha_asignacion' => now(),
                        'activo' => true,
                    ]
                );

                $assigned[] = $student->id;
            }

            return [
                'grupo' => $this->formatGroup($this->findGroup($group->id)),
                'alumnos_asignados' => $assigned,
                'cantidad_asignada' => count($assigned),
                'cupos_disponibles' => max(0, $availableSlots - count($assigned)),
            ];
        });
    }

    public function createClassroom(array $data): AulaModel
    {
        $location = $this->classroomLocation($data['aula']);
        $this->ensureUniqueClassroom($location);

        $id = DB::table('aula')->insertGetId([
            'ubicacion' => $location,
            'activa' => (bool) ($data['activa'] ?? true),
            'creado_en' => now(),
        ]);

        return $this->findClassroom($id);
    }

    public function updateClassroom(int $id, array $data): AulaModel
    {
        $classroom = $this->findClassroom($id);
        $classroomData = [];

        if (array_key_exists('aula', $data)) {
            $location = $this->classroomLocation($data['aula']);
            $this->ensureUniqueClassroom($location, $classroom->id);
            $classroomData['ubicacion'] = $location;
        }

        if (array_key_exists('activa', $data)) {
            $classroomData['activa'] = (bool) $data['activa'];
        }

        if ($classroomData !== []) {
            DB::table('aula')->where('id', $classroom->id)->update($classroomData);
        }

        return $this->findClassroom($id);
    }

    public function listClassrooms(array $filters): LengthAwarePaginator
    {
        return AulaModel::query()
            ->when(array_key_exists('activa', $filters), function (Builder $query) use ($filters): void {
                $query->where('activa', filter_var($filters['activa'], FILTER_VALIDATE_BOOLEAN));
            })
            ->orderBy('ubicacion')
            ->paginate((int) ($filters['por_pagina'] ?? 15));
    }

    public function findClassroom(int $id): AulaModel
    {
        return AulaModel::query()->findOrFail($id);
    }

    public function formatSubject(MateriaModel $subject): array
    {
        return [
            'id' => $subject->id,
            'nombre' => $subject->nombre,
            'activa' => $subject->activa,
            'creado_en' => $subject->creado_en,
        ];
    }

    public function formatGroup(GrupoModel $group): array
    {
        $occupied = GrupoAlumnoModel::query()
            ->where('grupo_id', $group->id)
            ->where('activo', true)
            ->count();

        return [
            'id' => $group->id,
            'nombre' => $group->nombre,
            'cupo_maximo' => $group->cupo_maximo,
            'cupos_ocupados' => $occupied,
            'cupos_disponibles' => max(0, (int) $group->cupo_maximo - $occupied),
            'activo' => $group->activo,
            'gestion_academica' => [
                'id' => $group->gestionAcademica?->id,
                'anio' => $group->gestionAcademica?->anio,
                'numero_gestion' => $group->gestionAcademica?->numero_gestion,
                'nombre' => $group->gestionAcademica?->nombre,
            ],
            'creado_en' => $group->creado_en,
        ];
    }

    public function formatStudent(AlumnoModel $student): array
    {
        return [
            'id' => $student->id,
            'codigo_alumno' => $student->codigo_alumno,
            'estado_academico' => $student->estado_academico,
            'persona' => [
                'id' => $student->persona?->id,
                'cedula_identidad' => $student->persona?->cedula_identidad,
                'nombres' => $student->persona?->nombres,
                'apellido_paterno' => $student->persona?->apellido_paterno,
                'apellido_materno' => $student->persona?->apellido_materno,
            ],
        ];
    }

    public function formatClassroom(AulaModel $classroom): array
    {
        return [
            'id' => $classroom->id,
            'ubicacion' => $classroom->ubicacion,
            'modulo' => 'Modulo 236',
            'aula' => trim(str_replace('Modulo 236, Aula ', '', $classroom->ubicacion)),
            'activa' => $classroom->activa,
            'creado_en' => $classroom->creado_en,
        ];
    }

    private function ensureBaseSubjects(): void
    {
        foreach ($this->requiredSubjects() as $subject) {
            DB::table('materia')->updateOrInsert(
                ['nombre' => $subject],
                ['activa' => true]
            );
        }
    }

    private function requiredSubjects(): array
    {
        return [
            'F'."\u{00ED}".'sica',
            'Matem'."\u{00E1}".'ticas',
            'Computaci'."\u{00F3}".'n',
            'Ingl'."\u{00E9}".'s',
        ];
    }

    private function availableStudents(int $gestionAcademicaId, ?array $studentIds): Collection
    {
        return AlumnoModel::query()
            ->with('persona')
            ->where('gestion_academica_id', $gestionAcademicaId)
            ->where('estado_academico', 'activo')
            ->when($studentIds, fn (Builder $query) => $query->whereIn('id', $studentIds))
            ->whereDoesntHave('grupos', fn (Builder $query) => $query->where('activo', true))
            ->orderBy('id')
            ->get();
    }

    private function classroomLocation(string|int $classroom): string
    {
        $classroom = trim((string) $classroom);
        $classroom = trim(str_ireplace(['Modulo 236, Aula', 'Modulo 236', 'Aula'], '', $classroom));

        if ($classroom === '') {
            throw new RuntimeException('El aula es obligatoria.');
        }

        return 'Modulo 236, Aula '.$classroom;
    }

    private function ensureUniqueClassroom(string $location, ?int $ignoreId = null): void
    {
        $exists = DB::table('aula')
            ->where('ubicacion', $location)
            ->when($ignoreId, fn ($query) => $query->where('id', '<>', $ignoreId))
            ->exists();

        if ($exists) {
            throw new RuntimeException('El aula indicada ya esta registrada en el Modulo 236.');
        }
    }
}
