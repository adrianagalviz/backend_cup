<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostulacionModel extends Model
{
    protected $table = 'postulacion';

    public $timestamps = false;

    protected $casts = [
        'promedio_final' => 'decimal:2',
        'orden_prioridad' => 'integer',
        'asignado_en' => 'datetime',
    ];

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(PostulanteModel::class, 'postulante_id');
    }

    public function primeraCarrera(): BelongsTo
    {
        return $this->belongsTo(CarreraModel::class, 'primera_carrera_id');
    }

    public function segundaCarrera(): BelongsTo
    {
        return $this->belongsTo(CarreraModel::class, 'segunda_carrera_id');
    }

    public function carreraAsignada(): BelongsTo
    {
        return $this->belongsTo(CarreraModel::class, 'carrera_asignada_id');
    }
}
