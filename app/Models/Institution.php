<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    // Campos que permites que se guarden desde el formulario
    protected $fillable = ['name', 'country', 'type'];

    // Relación: Una institución tiene muchos convenios
    public function agreements(): HasMany
    {
        return $this->hasMany(Agreement::class);
    }
}
