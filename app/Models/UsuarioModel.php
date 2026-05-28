<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UsuarioModel extends Model
{
    protected $table = 'usuario';

    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'rol_id',
        'nombre_usuario',
        'codigo_acceso',
        'correo_verificado',
        'firebase_uid',
        'password_hash',
        'activo',
        'ultimo_inicio_sesion',
        'creado_por_usuario_id',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'correo_verificado' => 'boolean',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'rol_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function administrador(): HasOne
    {
        return $this->hasOne(AdministradorModel::class, 'usuario_id');
    }

    public function docente(): HasOne
    {
        return $this->hasOne(DocenteModel::class, 'usuario_id');
    }

    public function alumno(): HasOne
    {
        return $this->hasOne(AlumnoModel::class, 'usuario_id');
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
