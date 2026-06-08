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
        'order'
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
    
    // 👇 ESTA ES LA FUNCIÓN QUE TE FALTA Y ESTÁ CAUSANDO EL ERROR 👇
    public function documents()
    {
        return $this->hasMany(RoadmapDocument::class);
    }
}