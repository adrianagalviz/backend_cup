<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCargaMasivaModel extends Model
{
    protected $table = 'detalle_carga_masiva';

    public $timestamps = false;

    protected $fillable = [
        'carga_masiva_id',
        'numero_fila',
        'estado',
        'mensaje_error',
        'datos_fila',
    ];

    protected $casts = [
        'datos_fila' => 'array',
    ];

    public function cargaMasiva(): BelongsTo
    {
        return $this->belongsTo(CargaMasivaModel::class, 'carga_masiva_id');
    }
}
