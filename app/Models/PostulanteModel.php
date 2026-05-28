<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostulanteModel extends Model
{
    protected $table = 'postulante';

    public $timestamps = false;

    protected $casts = [
        'creado_en' => 'datetime',
        'actualizado_en' => 'datetime',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonaModel::class, 'persona_id');
    }

    public function gestionAcademica(): BelongsTo
    {
        return $this->belongsTo(GestionAcademicaModel::class, 'gestion_academica_id');
    }

    public function postulacion(): HasOne
    {
        return $this->hasOne(PostulacionModel::class, 'postulante_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentoPostulanteModel::class, 'postulante_id');
    }

    public function pagoStripe(): HasOne
    {
        return $this->hasOne(PagoStripeModel::class, 'postulante_id');
    }
}
