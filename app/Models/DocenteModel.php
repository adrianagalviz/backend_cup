<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(AsignacionDocenteModel::class, 'docente_id');
    }
}
