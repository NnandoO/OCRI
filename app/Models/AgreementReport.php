<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgreementReport extends Model
{
    protected $fillable = [
        'agreement_id',
        'title',
        'date',
        'oficio_path',
        'oficio_original_name',
        'respuesta_path',
        'respuesta_original_name',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
