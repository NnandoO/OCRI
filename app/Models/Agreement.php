<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// IMPORTANTE: Esta es la clase que faltaba importar correctamente
use Illuminate\Database\Eloquent\Casts\Attribute;

class Agreement extends Model
{
    protected $fillable = [
        'title', 
        'name', 
        'resolution_number', 
        'institution_id', 
        'agreement_type_id', 
        'start_date', 
        'end_date', 
        'status',
        'situation', // <-- Campo agregado para permitir el guardado de notas
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * MUTATORS: Transformación automática a MAYÚSCULAS
     * Se ejecutan justo antes de guardar en la base de datos.
     */

    protected function title(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value, 'UTF-8'),
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value, 'UTF-8'),
        );
    }

    protected function resolutionNumber(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => mb_strtoupper($value, 'UTF-8'),
        );
    }

    // RELACIONES
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AgreementType::class, 'agreement_type_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function roadmapItems(): HasMany
    {
        return $this->hasMany(RoadmapItem::class)->orderBy('order');
    }
}