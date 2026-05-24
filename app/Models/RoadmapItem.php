<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapItem extends Model
{
    use HasFactory;

    // ESTA ES LA LÍNEA QUE FALTA:
    protected $fillable = [
        'agreement_id',
        'area_name',
        'is_completed',
        'order'
    ];

    // También es buena idea definir la relación inversa
    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}