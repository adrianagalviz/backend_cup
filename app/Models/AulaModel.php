<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AulaModel extends Model
{
    protected $table = 'aula';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
        'creado_en' => 'datetime',
    ];
}
