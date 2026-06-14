<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraSistemaModel extends Model
{
    protected $table = 'bitacora_sistema';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'modulo',
        'accion',
        'metodo_http',
        'ruta',
        'estado_http',
        'direccion_ip',
        'agente_usuario',
        'datos',
        'creado_en',
    ];

    protected $casts = [
        'datos' => 'array',
        'creado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
