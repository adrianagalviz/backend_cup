<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CargaMasivaModel extends Model
{
    protected $table = 'carga_masiva';

    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'tipo_carga',
        'nombre_archivo',
        'formato_archivo',
        'total_registros',
        'registros_exitosos',
        'registros_error',
        'estado',
        'finalizado_en',
    ];

    protected $casts = [
        'creado_en' => 'datetime',
        'finalizado_en' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'usuario_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCargaMasivaModel::class, 'carga_masiva_id');
    }
}
