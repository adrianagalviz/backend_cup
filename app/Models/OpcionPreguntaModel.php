<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpcionPreguntaModel extends Model
{
    protected $table = 'opcion_pregunta';

    public $timestamps = false;

    protected $casts = [
        'es_correcta' => 'boolean',
    ];

    public function pregunta(): BelongsTo
    {
        return $this->belongsTo(PreguntaModel::class, 'pregunta_id');
    }
}
