<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AlumnoModel extends Model
{
    protected $table = 'alumno';

    public $timestamps = false;

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(PostulanteModel::class, 'postulante_id');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoAlumnoModel::class, 'alumno_id');
    }
}
