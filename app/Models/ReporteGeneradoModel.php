<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReporteGeneradoModel extends Model
{
    protected $table = 'reporte_generado';

    public $timestamps = false;

    protected $casts = [
        'parametros' => 'array',
        'generado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }
}
