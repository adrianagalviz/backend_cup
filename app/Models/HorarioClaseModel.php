<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioClaseModel extends Model
{
    protected $table = 'horario_clase';

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoModel::class, 'grupo_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id');
    }

    public function aula(): BelongsTo
    {
        return $this->belongsTo(AulaModel::class, 'aula_id');
    }

    public function dia(): BelongsTo
    {
        return $this->belongsTo(DiaModel::class, 'dia_id');
    }

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoModel::class, 'periodo_id');
    }

    public function asignacionesDocentes(): HasMany
    {
        return $this->hasMany(AsignacionDocenteModel::class, 'horario_clase_id');
    }
}
