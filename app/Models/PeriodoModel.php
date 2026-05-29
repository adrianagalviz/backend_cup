<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoModel extends Model
{
    protected $table = 'periodo';

    public $timestamps = false;

    protected $casts = [
        'numero_periodo' => 'integer',
        'duracion_minutos' => 'integer',
        'activo' => 'boolean',
    ];

    public function turno(): BelongsTo
    {
        return $this->belongsTo(TurnoModel::class, 'turno_id');
    }
}
