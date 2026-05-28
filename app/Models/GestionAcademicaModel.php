<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GestionAcademicaModel extends Model
{
    protected $table = 'gestion_academica';

    public $timestamps = false;

    protected $casts = [
        'activa' => 'boolean',
    ];
}
