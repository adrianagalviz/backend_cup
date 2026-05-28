<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocenteModel extends Model
{
    protected $table = 'docente';

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
        'contratado' => 'boolean',
        'es_profesional_area' => 'boolean',
        'tiene_maestria' => 'boolean',
        'tiene_diplomado_educacion_superior' => 'boolean',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
