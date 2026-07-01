<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficio extends Model
{
    protected $fillable = [
        'agreement_id',
        'roadmap_item_id',
        'area_name',
        'directed_to',
        'oficio_number',
        'file_path',
        'file_original_name',
        'type',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function roadmapItem()
    {
        return $this->belongsTo(RoadmapItem::class);
    }
}
