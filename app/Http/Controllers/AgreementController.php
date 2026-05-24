<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgreementController extends Controller
{
public function index(Request $request)
{
    $query = Agreement::query()->with('institution');

    // Motor de Búsqueda Global
    if ($request->filled('search')) {
        $term = '%' . trim($request->search) . '%';
        
        $query->where(function ($q) use ($term) {
            $q->where('title', 'LIKE', $term)               // Busca en Título Corto
              ->orWhere('name', 'LIKE', $term)              // Busca en Nombre Oficial
              ->orWhere('resolution_number', 'LIKE', $term) // Busca en N° Resolución
              ->orWhereHas('institution', function ($inst) use ($term) {
                  $inst->where('name', 'LIKE', $term);      // Busca en el Nombre de la Institución
              });
        });
    }

    // Retornamos con paginación manteniendo los filtros en la URL
    $agreements = $query->latest()->paginate(15)->withQueryString();

    return view('agreements.index', compact('agreements'));
}

/**
 * Muestra el formulario para editar un convenio existente.
 */
public function edit(Agreement $agreement)
{
    // Necesitamos cargar las instituciones y tipos para los select del formulario
    $institutions = Institution::orderBy('name')->get();
    $types = AgreementType::all();

    return view('agreements.edit', compact('agreement', 'institutions', 'types'));
}

/**
 * Actualiza el convenio en la base de datos.
 */
public function update(Request $request, Agreement $agreement)
{
    $validated = $request->validate([
        'name' => 'required|string|max:500',
        'title' => 'required|string|max:255',
        'resolution_number' => 'nullable|string|max:100',
        'institution_id' => 'required|exists:institutions,id',
        'agreement_type_id' => 'required|exists:agreement_types,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ]);

    $agreement->update($validated);

    return redirect()->route('agreements.index')
        ->with('success', 'Convenio actualizado correctamente.');
}
    public function create()
    {
        $institutions = Institution::all();
        $types = AgreementType::all();
        return view('agreements.create', compact('institutions', 'types'));
    }

    public function store(Request $request)
{
    $rules = [
        'title' => 'required|string|max:255',
        'name' => 'required|string', // Nombre Oficial
        'resolution_number' => 'nullable|string|unique:agreements,resolution_number',
        'institution_id' => 'required|exists:institutions,id',
        'agreement_type_id' => 'required|exists:agreement_types,id',
        'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
    ];

    if ($request->hasFile('document')) {
        $rules['start_date'] = 'required|date';
        $rules['end_date'] = 'required|date|after:start_date';
    }

    $validated = $request->validate($rules);

    // Definir estado inicial
    $validated['status'] = $request->hasFile('document') ? 'Vigente' : 'En Proceso';

    // Crear el registro usando todos los datos validados (incluye 'name')
    $agreement = Agreement::create($validated);

    if ($request->hasFile('document')) {
        $file = $request->file('document');
        $path = $file->store('resoluciones', 'public');
        
        $agreement->documents()->create([
            'name' => 'Doc - ' . ($agreement->resolution_number ?? $agreement->title),
            'file_path' => $path,
            'extension' => $file->getClientOriginalExtension(),
        ]);
    }

    return redirect()->route('agreements.index')->with('status', 'Convenio registrado correctamente.');
}

public function activate(Request $request, $id)
{
    $data = $request->validate([
        'resolution_number' => 'required|string',
        'signature_date' => 'required|date',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
    ]);

    $agreement = Agreement::findOrFail($id);

    $agreement->update([
        'resolution_number' => $data['resolution_number'],
        'start_date' => $data['start_date'],
        'end_date' => $data['end_date'],
        'status' => 'Vigente',
    ]);

    return redirect()->route('agreements.show', $agreement->id)
        ->with('status', 'El convenio ahora está oficialmente VIGENTE.');
}


    public function updateStatus(Request $request, Agreement $agreement)
    {
        $agreement->update(['status' => $request->status]);
        return back()->with('status', 'Estado actualizado correctamente.');
    }

    public function show(Agreement $agreement)
{
    // ES VITAL cargar 'roadmapItems' aquí
    $agreement->load(['institution', 'type', 'documents', 'roadmapItems']);
    
    return view('agreements.show', compact('agreement'));
}
    public function storeRoadmap(Request $request, Agreement $agreement)
{
    $areas = $request->input('areas', []);
    
    // Procesar áreas extra si las hay
    if ($request->extra_areas) {
        $extra = explode(',', $request->extra_areas);
        foreach($extra as $e) {
            $areas[] = trim($e);
        }
    }

    foreach ($areas as $index => $area) {
        $agreement->roadmapItems()->create([
            'area_name' => $area,
            'order' => $index,
            'is_completed' => false
        ]);
    }

    return back()->with('status', 'Hoja de ruta generada con éxito.');
}

public function checkRoadmapItem($itemId)
{
    $item = \App\Models\RoadmapItem::findOrFail($itemId);
    $item->update(['is_completed' => !$item->is_completed]);

    $agreement = $item->agreement;
    
    // Contar cuántos faltan
    $pendingItems = $agreement->roadmapItems()->where('is_completed', false)->count();

    if ($pendingItems === 0) {
        // Si todos están listos, pasamos a un estado previo a Vigente o notificamos
        // No lo ponemos Vigente automáticamente porque faltan las fechas.
        return back()->with('all_completed', true)
                     ->with('status', '¡Hoja de ruta finalizada! Por favor, ingrese los datos de vigencia.');
    }

    return back();
}
public function dashboard()
{
    $now = \Carbon\Carbon::now();
    $inNinetyDays = \Carbon\Carbon::now()->addDays(90);

    // 1. Calculamos las estadísticas reales
    $stats = [
        'vigentes' => \App\Models\Agreement::where('status', 'Vigente')
                        ->where('end_date', '>', $now)
                        ->count(),
        'por_vencer' => \App\Models\Agreement::where('status', 'Vigente')
                        ->whereBetween('end_date', [$now, $inNinetyDays])
                        ->count(),
        'vencidos' => \App\Models\Agreement::where('status', 'Vigente')
                        ->where('end_date', '<', $now)
                        ->count(),
    ];

    // 2. Traemos los últimos 5 convenios para la tabla
    $recentAgreements = \App\Models\Agreement::with(['institution'])
                        ->latest()
                        ->take(5)
                        ->get();

    // 3. PASAR LAS VARIABLES A LA VISTA (Aquí estaba el fallo)
    return view('dashboard', compact('stats', 'recentAgreements'));
}

/**
 * Activa el convenio y lo pone en vigencia oficial.
 */

}