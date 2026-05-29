<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CupoCarreraModel extends Model
{
    protected $table = 'cupo_carrera';

    public $timestamps = false;

    protected $casts = [
        'cantidad_cupos' => 'integer',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(CarreraModel::class, 'carrera_id');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }
}
