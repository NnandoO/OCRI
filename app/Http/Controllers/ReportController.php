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
        // 1. Obtenemos las 10 categorías exactas directamente de la BD
        $classifications = Institution::distinct()->pluck('type')->filter()->sort();
        $types = AgreementType::all();
        $countries = Institution::distinct()->pluck('country')->filter()->sort();

        // 2. Iniciamos la consulta con la relación cargada
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

        // 4. Filtro por Categoría (Ahora directo a la columna 'type')
        if ($request->filled('classification')) {
            $query->whereHas('institution', fn($q) => $q->where('type', $request->classification));
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

        $perPage = $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 25, 50, 100])) {
            $perPage = 20;
        }

        return view('reports.index', [
            'agreements' => $query->latest()->paginate($perPage)->withQueryString(),
            'classifications' => $classifications,
            'types' => $types,
            'countries' => $countries,
            'perPage' => $perPage,
        ]);
    }
}