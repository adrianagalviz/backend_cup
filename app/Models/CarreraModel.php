<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarreraModel extends Model
{
    protected $table = 'carrera';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
    ];
}
