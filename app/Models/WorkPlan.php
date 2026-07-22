<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkPlan extends Model
{
    protected $fillable = [
        'agreement_id',
        'file_path',
        'original_name',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
