<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgreementType extends Model
{
    protected $fillable = ['name'];

    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }
}
