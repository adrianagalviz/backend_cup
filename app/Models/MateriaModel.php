<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaModel extends Model
{
    protected $table = 'materia';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
        'creado_en' => 'datetime',
    ];
}
