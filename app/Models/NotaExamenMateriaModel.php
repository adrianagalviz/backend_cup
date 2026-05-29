<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaExamenMateriaModel extends Model
{
    protected $table = 'nota_examen_materia';

    public $timestamps = false;

    protected $casts = [
        'nota' => 'decimal:2',
        'porcentaje_aplicado' => 'decimal:2',
        'nota_ponderada' => 'decimal:2',
    ];

    public function intentoExamen(): BelongsTo
    {
        return $this->belongsTo(IntentoExamenModel::class, 'intento_examen_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(MateriaModel::class, 'materia_id');
    }
}
