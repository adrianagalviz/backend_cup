<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComandoVozReporteModel extends Model
{
    protected $table = 'comando_voz_reporte';

    public $timestamps = false;

    protected $casts = [
        'creado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function reporteGenerado(): BelongsTo
    {
        return $this->belongsTo(ReporteGeneradoModel::class, 'reporte_generado_id');
    }
}
