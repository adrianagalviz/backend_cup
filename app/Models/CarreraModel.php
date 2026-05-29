<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarreraModel extends Model
{
    protected $table = 'carrera';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
        'creado_en' => 'datetime',
    ];

    public function cupos(): HasMany
    {
        return $this->hasMany(CupoCarreraModel::class, 'carrera_id');
    }
}
