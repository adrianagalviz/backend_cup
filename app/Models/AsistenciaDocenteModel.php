<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaDocenteModel extends Model
{
    protected $table = 'asistencia_docente';

    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(DocenteModel::class, 'docente_id');
    }

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClaseModel::class, 'horario_clase_id');
    }

    public function marcadoPor(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'marcado_por_usuario_id');
    }
}
