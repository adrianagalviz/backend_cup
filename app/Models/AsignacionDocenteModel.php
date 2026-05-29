<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionDocenteModel extends Model
{
    protected $table = 'asignacion_docente';

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
        'asignado_en' => 'datetime',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(DocenteModel::class, 'docente_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoModel::class, 'grupo_id');
    }

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClaseModel::class, 'horario_clase_id');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }
}
