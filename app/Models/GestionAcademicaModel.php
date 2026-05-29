<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GestionAcademicaModel extends Model
{
    protected $table = 'gestion_academica';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'creado_en' => 'datetime',
    ];

    public function cupos(): HasMany
    {
        return $this->hasMany(CupoCarreraModel::class, 'gestion_academica_id');
    }
}
