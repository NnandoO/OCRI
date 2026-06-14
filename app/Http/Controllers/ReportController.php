<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementType;
use App\Models\Institution;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgreementsExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Definición de grupos para el nuevo filtro simplificado
        $groupedClassifications = [
            'Universidades' => ['Universidad Nacional', 'Universidad Privada', 'Universidad Internacional'],
            'Comunidades' => ['Comunidad Campesina', 'Comunidad Nativa'],
            'Empresas' => ['Empresa Nacional', 'Empresa Internacional'],
            'Sector Público' => ['Municipalidad', 'Gobierno Regional', 'Salud'],
            'Educación' => ['Institución Educativa', 'Centro de Estudios'],
            'Otros/Asociaciones' => ['Asociación', 'Otros'],
        ];

        // Obtenemos los datos para los otros SELECTS
        $types = AgreementType::all();
        $countries = Institution::distinct()->pluck('country')->filter();

        // 2. Iniciamos la consulta de Convenios
        $query = Agreement::query()->with('institution');

        // 3. Motor de Búsqueda
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('title', 'LIKE', "%{$term}%")
                  ->orWhere('resolution_number', 'LIKE', "%{$term}%")
                  ->orWhereHas('institution', fn($inst) => $inst->where('name', 'LIKE', "%{$term}%"));
            });
        }

        // 4. Filtro Agrupado (Reemplaza a 'classification')
        if ($request->filled('classification_group')) {
            $group = $request->classification_group;
            if (isset($groupedClassifications[$group])) {
                $query->whereHas('institution', fn($q) => $q->whereIn('type', $groupedClassifications[$group]));
            }
        }

        // Otros filtros
        if ($request->filled('type_id')) {
            $query->where('agreement_type_id', $request->type_id);
        }

        if ($request->filled('country')) {
            $query->whereHas('institution', fn($q) => $q->where('country', $request->country));
        }

        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // 5. Exportación Excel
        if ($request->has('export') && $request->export == '1') {
            return Excel::download(new AgreementsExport($query->latest()->get()), 'reporte_convenios_ocri.xlsx');
        }

        return view('reports.index', [
            'agreements' => $query->latest()->paginate(20)->withQueryString(),
            'groupedClassifications' => $groupedClassifications, // Pasamos el mapa a la vista
            'types' => $types,
            'countries' => $countries,
        ]);
    }
}