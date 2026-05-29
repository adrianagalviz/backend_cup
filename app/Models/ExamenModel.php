<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamenModel extends Model
{
    protected $table = 'examen';

    public $timestamps = false;

    protected $casts = [
        'habilitado' => 'boolean',
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }

    public function materiasPorcentaje(): HasMany
    {
        return $this->hasMany(ExamenMateriaPorcentajeModel::class, 'examen_id');
    }

    public function preguntas(): HasMany
    {
        return $this->hasMany(PreguntaModel::class, 'examen_id');
    }

    public function intentos(): HasMany
    {
        return $this->hasMany(IntentoExamenModel::class, 'examen_id');
    }
}
