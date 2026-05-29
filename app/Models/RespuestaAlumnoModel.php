<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespuestaAlumnoModel extends Model
{
    protected $table = 'respuesta_alumno';

    public $timestamps = false;

    protected $casts = [
        'es_correcta' => 'boolean',
        'respondido_en' => 'datetime',
    ];

    public function intentoExamen(): BelongsTo
    {
        return $this->belongsTo(IntentoExamenModel::class, 'intento_examen_id');
    }

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(PreguntaModel::class, 'pregunta_id');
    }

    public function opcionPregunta(): BelongsTo
    {
        return $this->belongsTo(OpcionPreguntaModel::class, 'opcion_pregunta_id');
    }
}
