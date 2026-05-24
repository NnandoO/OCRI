<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'name', 
        'file_path', 
        'extension', 
        'agreement_id'
    ];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}