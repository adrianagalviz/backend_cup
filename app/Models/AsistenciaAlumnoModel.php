<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaAlumnoModel extends Model
{
    protected $table = 'asistencia_alumno';

    public $timestamps = false;

    protected $casts = [
        'fecha' => 'date',
        'hora_marcada' => 'datetime',
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id');
    }

    public function horarioClase(): BelongsTo
    {
        return $this->belongsTo(HorarioClaseModel::class, 'horario_clase_id');
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(DocenteModel::class, 'docente_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'registrado_por_usuario_id');
    }
}
