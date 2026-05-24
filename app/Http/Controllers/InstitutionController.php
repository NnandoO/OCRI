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
        return view('institutions.create'); // Si vas a crear la vista de registro
    }
    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'country' => 'required|string|max:100',
        'type' => 'required|string',
    ]);

    \App\Models\Institution::create($validated);

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