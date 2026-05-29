<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaParcialModel extends Model
{
    protected $table = 'nota_parcial';

    public $timestamps = false;

    protected $casts = [
        'nota' => 'decimal:2',
        'registrado_en' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id');
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(ExamenModel::class, 'examen_id');
    }

    public function intentoExamen(): BelongsTo
    {
        return $this->belongsTo(IntentoExamenModel::class, 'intento_examen_id');
    }
}
