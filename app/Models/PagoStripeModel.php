<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoStripeModel extends Model
{
    protected $table = 'pago_stripe';

    public $timestamps = false;

    protected $casts = [
        'monto' => 'decimal:2',
        'respuesta_stripe' => 'array',
        'fecha_pago' => 'datetime',
        'validado_en' => 'datetime',
        'creado_en' => 'datetime',
    ];

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(PostulanteModel::class, 'postulante_id');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(UsuarioModel::class, 'validado_por_usuario_id');
    }
}
