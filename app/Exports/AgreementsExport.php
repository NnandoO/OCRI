<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AgreementsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $agreements;

    public function __construct($agreements) {
        $this->agreements = $agreements;
    }

    public function collection() {
        return $this->agreements;
    }

    public function headings(): array {
        return ['Título', 'Nombre Oficial', 'Institución', 'País', 'Tipo', 'Estado', 'Inicio', 'Fin'];
    }

    public function map($agreement): array {
        return [
            $agreement->title,
            $agreement->name,
            $agreement->institution->name,
            $agreement->institution->country,
            $agreement->type->name,
            $agreement->status,
            $agreement->start_date?->format('d/m/Y'),
            $agreement->end_date?->format('d/m/Y'),
        ];
    }
}
