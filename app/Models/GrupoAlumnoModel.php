<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrupoAlumnoModel extends Model
{
    protected $table = 'grupo_alumno';

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
        'fecha_asignacion' => 'datetime',
    ];

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(GrupoModel::class, 'grupo_id');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(AlumnoModel::class, 'alumno_id');
    }
}
