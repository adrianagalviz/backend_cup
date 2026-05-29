<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoModel extends Model
{
    protected $table = 'grupo';

    public $timestamps = false;

    protected $casts = [
        'cupo_maximo' => 'integer',
        'activo' => 'boolean',
        'creado_en' => 'datetime',
    ];

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }

    public function alumnos(): HasMany
    {
        return $this->hasMany(GrupoAlumnoModel::class, 'grupo_id');
    }
}
