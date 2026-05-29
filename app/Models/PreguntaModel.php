<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PreguntaModel extends Model
{
    protected $table = 'pregunta';

    public $timestamps = false;

    protected $casts = [
        'puntaje' => 'decimal:2',
        'activa' => 'boolean',
        'creado_en' => 'datetime',
    ];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(ExamenModel::class, 'examen_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id');
    }

    public function opciones(): HasMany
    {
        return $this->hasMany(OpcionPreguntaModel::class, 'pregunta_id');
    }
}
