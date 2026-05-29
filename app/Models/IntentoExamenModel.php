<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class IntentoExamenModel extends Model
{
    protected $table = 'intento_examen';

    public $timestamps = false;

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'nota_total' => 'decimal:2',
        'creado_en' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id');
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(ExamenModel::class, 'examen_id');
    }

    public function respuestas(): HasMany
    {
        return $this->hasMany(RespuestaAlumnoModel::class, 'intento_examen_id');
    }

    public function notasMateria(): HasMany
    {
        return $this->hasMany(NotaExamenMateriaModel::class, 'intento_examen_id');
    }

    public function notaParcial(): HasOne
    {
        return $this->hasOne(NotaParcialModel::class, 'intento_examen_id');
    }
}
