<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\AgreementType;
use App\Models\Institution;
use Illuminate\Http\Request;
// Importante: Asegúrate de tener importada tu clase Export y la fachada Excel
// use Maatwebsite\Excel\Facades\Excel; 
// use App\Exports\AgreementsExport; 

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Obtenemos los datos para los SELECTS
        $classifications = \App\Models\Institution::distinct()->pluck('type')->filter();
        $types = \App\Models\AgreementType::all();
        $countries = \App\Models\Institution::distinct()->pluck('country')->filter();

        // 2. Iniciamos la consulta de Convenios
        $query = \App\Models\Agreement::query()->with('institution');

        // 3. Lógica del Motor de Búsqueda Inteligente
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('title', 'LIKE', "%{$term}%")
                  ->orWhere('resolution_number', 'LIKE', "%{$term}%")
                  ->orWhereHas('institution', function ($inst) use ($term) {
                      $inst->where('name', 'LIKE', "%{$term}%");
                  });
            });
        }

        // 4. Aplicación de Filtros (Clasificación, Tipo, País, Año)
        if ($request->filled('classification')) {
            $query->whereHas('institution', fn($q) => $q->where('type', $request->classification));
        }

        if ($request->filled('type_id')) {
            $query->where('agreement_type_id', $request->type_id);
        }

        if ($request->filled('country')) {
            $query->whereHas('institution', fn($q) => $q->where('country', $request->country));
        }

        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

if ($request->has('export') && $request->export == '1') {
            
            $agreementsFiltered = $query->latest()->get();
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\AgreementsExport($agreementsFiltered), 
                'reporte_convenios_ocri.xlsx'
            );
        }
        // ------------------------------

        // 5. Retornamos la vista con TODAS las variables
        return view('reports.index', [
            'agreements' => $query->latest()->paginate(20)->withQueryString(),
            'classifications' => $classifications,
            'types' => $types,
            'countries' => $countries,
        ]);
    }
}