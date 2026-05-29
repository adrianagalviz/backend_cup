<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromedioFinalModel extends Model
{
    protected $table = 'promedio_final';

    public $timestamps = false;

    protected $casts = [
        'parcial_1' => 'decimal:2',
        'parcial_2' => 'decimal:2',
        'parcial_3' => 'decimal:2',
        'promedio' => 'decimal:2',
        'calculado_en' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }
}
