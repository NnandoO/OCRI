<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = ['agreement_id', 'name', 'file_path'];

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}