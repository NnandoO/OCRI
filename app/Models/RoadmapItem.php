<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'agreement_id',
        'area_name',
        'is_completed',
        'order',
        'envio_tipo',
        'numero_expediente',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
    
    public function documents()
    {
        return $this->hasMany(RoadmapDocument::class);
    }
}