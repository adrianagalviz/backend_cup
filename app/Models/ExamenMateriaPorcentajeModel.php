<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamenMateriaPorcentajeModel extends Model
{
    protected $table = 'examen_materia_porcentaje';

    public $timestamps = false;

    protected $casts = [
        'porcentaje' => 'decimal:2',
    ];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(ExamenModel::class, 'examen_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id');
    }
}
