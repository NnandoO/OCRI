<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    protected $table = 'asistencia';

    protected $fillable = [
        'nombre',
        'fecha',
        'hora_entrada',
        'hora_salida',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora_entrada' => 'datetime:H:i',
            'hora_salida' => 'datetime:H:i',
        ];
    }
}
