<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    protected $fillable = [
        'title', 'resolution_number', 'institution_id', 
        'agreement_type_id', 'start_date', 'end_date', 'status'
    ];

    // Relación: El convenio pertenece a una institución
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    // Relación: El convenio tiene un tipo
    public function type(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class, 'agreement_type_id');
    }

    // Relación: El convenio puede tener varios archivos PDF
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}