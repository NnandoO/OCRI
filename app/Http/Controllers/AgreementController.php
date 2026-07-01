<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use App\Models\Document;
use App\Models\RoadmapItem;
use App\Models\RoadmapDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = Agreement::query()->with(['institution', 'roadmapItems.documents']);

        // Motor de Búsqueda Global
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            
            $query->where(function ($q) use ($term) {
                $q->where('title', 'LIKE', $term)               
                  ->orWhere('name', 'LIKE', $term)              
                  ->orWhere('resolution_number', 'LIKE', $term) 
                  ->orWhereHas('institution', function ($inst) use ($term) {
                      $inst->where('name', 'LIKE', $term)
                           ->orWhere('country', 'LIKE', $term);
                  });
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $statusFilter = $request->status;
            
            if ($statusFilter === 'En Proceso') {
                $query->where('status', 'En Proceso');
            } elseif ($statusFilter === 'Vigente') {
                $query->where('status', 'Vigente')
                      ->where(function ($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      });
            } elseif ($statusFilter === 'Por Vencer') {
                $query->where('status', 'Vigente')
                      ->whereNotNull('end_date')
                      ->where('end_date', '>=', now())
                      ->where('end_date', '<=', now()->addDays(90));
            } elseif ($statusFilter === 'Vencido') {
                $query->where(function ($q) {
                    $q->where('status', 'Vencido')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'Vigente')
                             ->whereNotNull('end_date')
                             ->where('end_date', '<', now());
                      });
                });
            }
        }

        $perPage = $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 25, 50, 100])) {
            $perPage = 15;
        }

        $agreements = $query->latest()->paginate($perPage)->withQueryString();

        $currentStatus = $request->status;

        return view('agreements.index', compact('agreements', 'currentStatus', 'perPage'));
    }

    public function edit(Agreement $agreement)
    {
        $institutions = Institution::orderBy('name')->get();
        $types = AgreementType::all();

        return view('agreements.edit', compact('agreement', 'institutions', 'types'));
    }

    public function update(Request $request, Agreement $agreement)
    {
        // Caso 1: Actualización rápida de la nota/situación (viene de la vista show)
        if ($request->has('situation') && !$request->has('name')) {
            $request->validate(['situation' => 'nullable|string']);
            
            $agreement->update(['situation' => $request->situation]);
            
            return back()->with('status', 'Nota guardada correctamente.');
        }

        // Caso 2: Actualización completa del convenio (viene de la vista edit)
        $validated = $request->validate([
            'name' => 'required|string|max:500',
            'title' => 'required|string|max:255',
            'resolution_number' => 'nullable|string|max:100|unique:agreements,resolution_number,' . $agreement->id,
            'institution_id' => 'required|exists:institutions,id',
            'agreement_type_id' => 'required|exists:agreement_types,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'document' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $agreement->update([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'resolution_number' => $validated['resolution_number'],
            'institution_id' => $validated['institution_id'],
            'agreement_type_id' => $validated['agreement_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            // Todo apunta a resoluciones
            $path = $file->store('resoluciones', 'public');
            
            $agreement->documents()->create([
                'name' => 'Expediente Consolidado Final',
                'file_path' => $path,
                'extension' => $file->getClientOriginalExtension(),
            ]);
        }

        return redirect()->route('agreements.index')
            ->with('success', 'Convenio actualizado correctamente.');
    }

    public function create()
    {
        $institutions = \App\Models\Institution::all();
        $types = \App\Models\AgreementType::all();
        
        $countries = \App\Models\Institution::select('country')
            ->distinct()
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->orderBy('country', 'asc')
            ->pluck('country');

        $currentYear = date('Y');
        $lastAgreement = \App\Models\Agreement::where('resolution_number', 'LIKE', "%-{$currentYear}")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastAgreement && $lastAgreement->resolution_number) {
            $parts = explode('-', $lastAgreement->resolution_number);
            $lastNum = is_numeric($parts[0]) ? (int)$parts[0] : 0;
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        $nextResolutionNumber = str_pad($nextNum, 3, '0', STR_PAD_LEFT) . '-' . $currentYear;

        return view('agreements.create', compact('institutions', 'types', 'countries', 'nextResolutionNumber'));
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'name' => 'required|string|max:500',
            'resolution_number' => 'nullable|string|max:100|unique:agreements,resolution_number',
            'institution_id' => 'required|exists:institutions,id',
            'agreement_type_id' => 'required|exists:agreement_types,id',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'dictamen' => 'nullable|file|mimes:pdf|max:10240',
        ];

        if ($request->hasFile('document')) {
            $rules['start_date'] = 'required|date';
            $rules['end_date'] = 'required|date|after:start_date';
        }

        $validated = $request->validate($rules);
        $validated['status'] = $request->hasFile('document') ? 'Vigente' : 'En Proceso';

        if ($request->hasFile('dictamen')) {
            $file = $request->file('dictamen');
            $path = $file->store('resoluciones', 'public');
            $validated['dictamen_path'] = $path;
            $validated['dictamen_original_name'] = $file->getClientOriginalName();
        }

        $agreement = Agreement::create($validated);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $path = $file->store('resoluciones', 'public');
            
            $agreement->documents()->create([
                'name' => 'Doc - ' . ($agreement->resolution_number ?? $agreement->title),
                'file_path' => $path,
                'extension' => $file->getClientOriginalExtension(),
            ]);

            $defaultAreas = ['Rectorado', 'Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Asesoría Legal'];
            foreach ($defaultAreas as $index => $area) {
                $agreement->roadmapItems()->create([
                    'area_name' => $area,
                    'order' => $index,
                    'is_completed' => true
                ]);
            }
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
            'final_document' => 'nullable|file|mimes:pdf|max:10240', // <-- NUEVO: Validar el PDF
        ]);

        $agreement = Agreement::findOrFail($id);

        $agreement->update([
            'resolution_number' => $data['resolution_number'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'Vigente',
        ]);

        // <-- NUEVO: Si se sube el convenio final, lo guardamos en el acervo
        if ($request->hasFile('final_document')) {
            $file = $request->file('final_document');
            $path = $file->store('resoluciones', 'public');
            
            $agreement->documents()->create([
                'name' => 'Convenio Firmado',
                'file_path' => $path,
                'extension' => $file->getClientOriginalExtension(),
            ]);
        }

        return redirect()->route('agreements.show', $agreement->id)
            ->with('status', 'El convenio ahora está oficialmente VIGENTE.');
    }

    public function updateStatus(Request $request, Agreement $agreement)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:En Proceso,Vigente,Vencido,Formulación',
        ]);
        $agreement->update($validated);
        return back()->with('status', 'Estado actualizado correctamente.');
    }

    public function show(Agreement $agreement)
    {
        $agreement->load(['institution', 'type', 'documents', 'roadmapItems.documents', 'oficios']);

        $currentYear = date('Y');
        $lastOficio = \App\Models\Oficio::whereYear('created_at', $currentYear)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOficio) {
            $parts = explode('-', $lastOficio->oficio_number);
            $lastNum = is_numeric($parts[0]) ? (int)$parts[0] : 0;
            $nextNum = $lastNum + 1;
        } else {
            $lastAgreement = Agreement::where('resolution_number', 'LIKE', "%-{$currentYear}")
                ->orderBy('id', 'desc')
                ->first();
            if ($lastAgreement && $lastAgreement->resolution_number) {
                $parts = explode('-', $lastAgreement->resolution_number);
                $lastNum = is_numeric($parts[0]) ? (int)$parts[0] : 0;
                $nextNum = $lastNum + 1;
            } else {
                $nextNum = 1;
            }
        }

        $nextOficioNumber = str_pad($nextNum, 3, '0', STR_PAD_LEFT) . '-' . $currentYear;

        return view('agreements.show', compact('agreement', 'nextOficioNumber'));
    }

    public function storeRoadmap(Request $request, Agreement $agreement)
    {
        $areas = $request->input('areas', []);
        
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
        $pendingItems = $agreement->roadmapItems()->where('is_completed', false)->count();

        if ($pendingItems === 0) {
            return back()->with('all_completed', true)
                         ->with('status', '¡Hoja de ruta finalizada! Por favor, ingrese los datos de vigencia.');
        }

        return back();
    }

    public function dashboard()
    {
        $now = \Carbon\Carbon::now();
        $inNinetyDays = \Carbon\Carbon::now()->addDays(90);

        $stats = [
            'vigentes' => \App\Models\Agreement::where('status', 'Vigente')
                            ->where(function ($q) use ($now) {
                                $q->whereNull('end_date')->orWhere('end_date', '>', $now);
                            })->count(),
            'por_vencer' => \App\Models\Agreement::where('status', 'Vigente')
                            ->whereNotNull('end_date')
                            ->whereBetween('end_date', [$now, $inNinetyDays])
                            ->count(),
            'vencidos' => \App\Models\Agreement::where(function ($q) use ($now) {
                            $q->where('status', 'Vencido')
                              ->orWhere(function ($q2) use ($now) {
                                  $q2->where('status', 'Vigente')
                                     ->whereNotNull('end_date')
                                     ->where('end_date', '<', $now);
                              });
                          })->count(),
        ];

        $recentAgreements = \App\Models\Agreement::with(['institution'])
                            ->latest()
                            ->take(10)
                            ->get();

        return view('dashboard', compact('stats', 'recentAgreements'));
    }

    // ==========================================
    // FUNCIONES PARA LOS DOCUMENTOS (PDF)
    // ==========================================

    public function uploadDocument(Request $request, RoadmapItem $item)
    {
        $files = $request->file('documents', []);
        if (!is_array($files)) {
            $files = [$files];
        }

        if (empty($files) || (count($files) === 1 && !$files[0])) {
            return redirect()->route('agreements.show', $item->agreement_id)
                ->withErrors(['documents' => 'No se recibió ningún archivo.']);
        }

        $request->validate(['documents.*' => 'required|mimes:pdf|max:10240']); 

        $type = $request->input('type', 'entrada');
        if (!in_array($type, ['entrada', 'salida'])) {
            $type = 'entrada';
        }

        $count = 0;
        foreach ($files as $file) {
            $path = $file->store('resoluciones', 'public');
            
            $item->documents()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'type' => $type,
            ]);
            $count++;
        }

        return redirect()->route('agreements.show', $item->agreement_id)
                         ->with('status', "{$count} documento(s) subido(s) correctamente al área.");
    }

    public function uploadMainDocument(Request $request, Agreement $agreement)
    {
        $request->validate([
            'document' => 'required|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            // Guardamos directamente en la carpeta resoluciones como todo lo demás
            $path = $file->store('resoluciones', 'public');
            
            $agreement->documents()->create([
                'name' => 'Convenio Firmado',
                'file_path' => $path,
                'extension' => $file->getClientOriginalExtension(),
            ]);
        }

        return back()->with('status', 'Documento del convenio subido correctamente.');
    }

    public function destroyMainDocument($id)
    {

        $document = \App\Models\Document::findOrFail($id);

        // Borrar archivo físico del almacenamiento público
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($document->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        }

        // Borrar el registro de la base de datos
        $document->delete();

        return back()->with('status', 'Archivo eliminado correctamente del acervo.');
    }

    public function deleteDocument($id)
    {
        $document = RoadmapDocument::findOrFail($id);
        $agreementId = $document->roadmapItem->agreement_id;

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->route('agreements.show', $agreementId)
            ->with('status', 'Archivo eliminado correctamente.');
    }

    public function updateEnvio(Request $request, RoadmapItem $item)
    {
        $validated = $request->validate([
            'envio_tipo' => 'nullable|in:adesa,correo',
            'numero_expediente' => 'nullable|string|max:100',
        ]);

        if ($validated['envio_tipo'] === 'correo') {
            $validated['numero_expediente'] = null;
        }

        $item->update($validated);

        return redirect()->route('agreements.show', $item->agreement_id)
            ->with('status', 'Información de envío actualizada.');
    }

    public function destroy(Agreement $agreement)
    {
        foreach ($agreement->documents as $doc) {
            if (Storage::disk('public')->exists($doc->file_path)) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->delete();
        }

        if ($agreement->roadmapItems()) {
            $agreement->roadmapItems()->delete();
        }

        $agreement->delete();

        return redirect()->route('agreements.index')
            ->with('status', 'Convenio y todo su historial eliminados correctamente.');
    }
}