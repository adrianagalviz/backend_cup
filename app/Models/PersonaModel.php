<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PersonaModel extends Model
{
    protected $table = 'persona';

    public $timestamps = false;

    protected $fillable = [
        'cedula_identidad',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'sexo',
        'direccion',
        'telefono',
        'celular',
        'correo',
        'ciudad',
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(UsuarioModel::class, 'persona_id');
    }
}
