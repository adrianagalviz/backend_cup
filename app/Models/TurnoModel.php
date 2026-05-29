<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoModel extends Model
{
    protected $table = 'turno';

    public $timestamps = false;

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function periodos(): HasMany
    {
        return $this->hasMany(PeriodoModel::class, 'turno_id');
    }
}
