<?php

namespace App\Exports;

use App\Models\Practicante;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PracticanteExport implements FromCollection, WithHeadings, WithMapping
{
    protected $practicante;

    public function __construct(Practicante $practicante)
    {
        $this->practicante = $practicante;
    }

    public function collection()
    {
        return $this->practicante->asistencias()->orderByDesc('fecha')->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Hora de Entrada',
            'Hora de Salida',
            'Total Horas del Día',
        ];
    }

    public function map($asistencia): array
    {
        $totalDia = '---';
        if ($asistencia->hora_entrada && $asistencia->hora_salida) {
            $mins = $asistencia->hora_entrada->diffInMinutes($asistencia->hora_salida);
            $h = floor($mins / 60);
            $m = $mins % 60;
            $totalDia = "{$h}h {$m}m";
        }

        return [
            \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y'),
            $asistencia->hora_entrada ? $asistencia->hora_entrada->format('H:i') : '---',
            $asistencia->hora_salida ? $asistencia->hora_salida->format('H:i') : 'Pendiente',
            $totalDia,
        ];
    }
}
