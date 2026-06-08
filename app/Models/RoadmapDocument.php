<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoadmapDocument extends Model
{
    use HasFactory;

    // Los campos que se pueden llenar masivamente
    protected $fillable = [
        'roadmap_item_id', 
        'file_path', 
        'original_name'
    ];

    // Relación: Este documento pertenece a una sola área de la hoja de ruta
    public function roadmapItem()
    {
        return $this->belongsTo(RoadmapItem::class);
    }
}