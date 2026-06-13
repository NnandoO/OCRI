<?php

namespace App\Http\Controllers;

use App\Models\Institution; // Importante para cargar los datos
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
public function index(Request $request)
{
    // Iniciamos la consulta cargando el conteo de convenios (para que tus badges no fallen)
    $query = Institution::query()->withCount('agreements');

    // Motor de búsqueda
    if ($request->filled('search')) {
        $term = '%' . trim($request->search) . '%';
        
        $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', $term)
              ->orWhere('country', 'LIKE', $term)
              ->orWhere('type', 'LIKE', $term); // También permite buscar por tipo (Pública/Privada)
        });
    }

    $institutions = $query->latest()->paginate(12)->withQueryString();

    return view('institutions.index', compact('institutions'));
}

public function create()
{
    // Cargas las instituciones y los tipos como ya lo hacias...
    $institutions = \App\Models\Institution::all();
    $types = \App\Models\AgreementType::all();
    
    // Y agregas la lista de países para el modal
    $countries = \App\Models\Institution::select('country')
        ->distinct()
        ->whereNotNull('country')
        ->where('country', '!=', '')
        ->orderBy('country', 'asc')
        ->pluck('country');

    return view('agreements.create', compact('institutions', 'types', 'countries'));
}
public function store(Request $request)
{
    // 1. Limpiar y pasar a mayúsculas el nombre para una comparación limpia
    $nameUpper = strtoupper(trim($request->name));

    // 2. INTERCEPCIÓN AJAX: Si ya existe en la BD, la recuperamos y se la devolvemos al modal
    if ($request->wantsJson() || $request->ajax()) {
        $existingInstitution = \App\Models\Institution::where('name', $nameUpper)->first();
        
        if ($existingInstitution) {
            // Se la enviamos con un estado 200 (OK). El JS la recibirá y la seleccionará de una
            return response()->json($existingInstitution, 200); 
        }
    }

    // 3. Validación normal (si no es duplicado o si viene del formulario clásico)
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'country' => 'required|string|max:100',
        'type' => 'required|string',
    ]);

    // 4. Crear el nuevo registro si pasó limpio
    $institution = \App\Models\Institution::create([
        'name' => $nameUpper,
        'country' => strtoupper($validated['country']),
        'type' => $validated['type'],
    ]);

    if ($request->wantsJson() || $request->ajax()) {
        return response()->json($institution, 201);
    }

    return redirect()->route('institutions.index')->with('status', 'Institución creada.');
}
public function show(Institution $institution)
{
    // Cargamos la institución junto con sus convenios relacionados
    $institution->load('agreements.type'); 

    return view('institutions.show', compact('institution'));
}
public function destroy(Institution $institution)
{
    // Opcional: Verificar si tiene convenios antes de borrar
    if ($institution->agreements()->count() > 0) {
        return back()->withErrors(['error' => 'No se puede eliminar una institución que tiene convenios activos.']);
    }

    $institution->delete();

    return redirect()->route('institutions.index')
        ->with('status', 'Institución eliminada correctamente.');
}
}