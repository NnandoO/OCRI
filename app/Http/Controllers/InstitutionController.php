<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\AgreementType;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        // Consulta base optimizada con el conteo de relaciones
        $query = Institution::query()->withCount('agreements');

        // Motor de búsqueda inteligente
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', $term)
                  ->orWhere('country', 'LIKE', $term)
                  ->orWhere('type', 'LIKE', $term);
            });
        }

        $institutions = $query->latest()->paginate(12)->withQueryString();

        return view('institutions.index', compact('institutions'));
    }

    public function create()
    {
        $institutions = Institution::all();
        $types = AgreementType::all();
        
        // Extracción limpia de países para los elementos selectores
        $countries = Institution::select('country')
            ->distinct()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->orderBy('country', 'asc')
            ->pluck('country');

        return view('agreements.create', compact('institutions', 'types', 'countries'));
    }

    public function store(Request $request)
    {
        $nameUpper = strtoupper(trim($request->name));

        // Validación de duplicados para llamadas asíncronas (Modales/AJAX)
        if ($request->wantsJson() || $request->ajax()) {
            $existingInstitution = Institution::where('name', $nameUpper)->first();
            
            if ($existingInstitution) {
                return response()->json($existingInstitution, 200); 
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country' => 'required|string|max:100',
            'type' => 'required|string',
        ]);

        // La categorización final la maneja el mutador del modelo automáticamente
        $institution = Institution::create([
            'name' => $nameUpper,
            'country' => trim($validated['country']),
            'type' => $validated['type'],
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($institution, 201);
        }

        return redirect()->route('institutions.index')->with('status', 'Institución creada con éxito.');
    }

    public function show(Institution $institution)
    {
        $institution->load('agreements.agreementType'); 

        return view('institutions.show', compact('institution'));
    }

    public function destroy(Institution $institution)
    {
        if ($institution->agreements()->count() > 0) {
            return back()->withErrors(['error' => 'Restricción de integridad: No se puede eliminar una institución vinculada a convenios vigentes.']);
        }

        $institution->delete();

        return redirect()->route('institutions.index')
            ->with('status', 'Institución removida del sistema correctamente.');
    }
}