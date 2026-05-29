<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiaModel extends Model
{
    protected $table = 'dia';

    public $timestamps = false;

    protected $casts = [
        'orden' => 'integer',
        'activo' => 'boolean',
    ];
}
