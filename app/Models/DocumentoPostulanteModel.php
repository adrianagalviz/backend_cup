<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoPostulanteModel extends Model
{
    protected $table = 'documento_postulante';

    public $timestamps = false;

    protected $casts = [
        'subido_en' => 'datetime',
    ];

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(PostulanteModel::class, 'postulante_id');
    }
}
