<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Institution;
use App\Models\AgreementType;
use App\Models\Document;
use App\Models\RoadmapItem;
use App\Models\RoadmapDocument; // NUEVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi; // NUEVO: Para unir los PDFs

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = Agreement::query()->with('institution');

        // Motor de Búsqueda Global
        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';
            
            $query->where(function ($q) use ($term) {
                $q->where('title', 'LIKE', $term)               
                  ->orWhere('name', 'LIKE', $term)              
                  ->orWhere('resolution_number', 'LIKE', $term) 
                  ->orWhereHas('institution', function ($inst) use ($term) {
                      $inst->where('name', 'LIKE', $term);      
                  });
            });
        }

        $agreements = $query->latest()->paginate(15)->withQueryString();

        return view('agreements.index', compact('agreements'));
    }

    public function edit(Agreement $agreement)
    {
        $institutions = Institution::orderBy('name')->get();
        $types = AgreementType::all();

        return view('agreements.edit', compact('agreement', 'institutions', 'types'));
    }

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
        $institutions = \App\Models\Institution::all();
        $types = \App\Models\AgreementType::all();
        
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
        $rules = [
            'title' => 'required|string|max:255',
            'name' => 'required|string', 
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

        $validated['status'] = $request->hasFile('document') ? 'Vigente' : 'En Proceso';

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
        $agreement->load(['institution', 'type', 'documents', 'roadmapItems']);
        return view('agreements.show', compact('agreement'));
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
                            ->where('end_date', '>', $now)
                            ->count(),
            'por_vencer' => \App\Models\Agreement::where('status', 'Vigente')
                            ->whereBetween('end_date', [$now, $inNinetyDays])
                            ->count(),
            'vencidos' => \App\Models\Agreement::where('status', 'Vigente')
                            ->where('end_date', '<', $now)
                            ->count(),
        ];

        $recentAgreements = \App\Models\Agreement::with(['institution'])
                            ->latest()
                            ->take(5)
                            ->get();

        return view('dashboard', compact('stats', 'recentAgreements'));
    }

    // ==========================================
    // NUEVAS FUNCIONES PARA LOS DOCUMENTOS (PDF)
    // ==========================================

public function uploadDocument(Request $request, RoadmapItem $item)
    {
        // 1. Validación manual extra: Previene el error si PHP bloquea el archivo por ser muy pesado
        if (!$request->hasFile('document')) {
            return redirect()->route('agreements.show', $item->agreement_id)
                ->withErrors(['document' => 'No se recibió el archivo. Es posible que el PDF sea demasiado pesado para el servidor.']);
        }

        $request->validate(['document' => 'required|mimes:pdf|max:10240']); 
        
        $path = $request->file('document')->store('roadmap_temp');
        
        $item->documents()->create([
            'file_path' => $path,
            'original_name' => $request->file('document')->getClientOriginalName()
        ]);

        // 2. REDIRECCIÓN EXPLÍCITA: En lugar de back(), forzamos a que vuelva al expediente
        return redirect()->route('agreements.show', $item->agreement_id)
                         ->with('status', 'Documento subido correctamente al área.');
    }

    public function deleteDocument($id)
    {
        $document = RoadmapDocument::findOrFail($id);
        $agreementId = $document->roadmapItem->agreement_id;

        // Eliminar el archivo físico del servidor
        if (Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Eliminar el registro de la base de datos
        $document->delete();

        return redirect()->route('agreements.show', $agreementId)
            ->with('status', 'Archivo eliminado correctamente.');
    }
    public function consolidateExpedient(Agreement $agreement)
    {
        $pdf = new Fpdi();
        $tempFilesToDelete = [];

        // Obtener todos los documentos ordenados del más reciente al más antiguo
        $documents = RoadmapDocument::whereHas('roadmapItem', function($query) use ($agreement) {
            $query->where('agreement_id', $agreement->id);
        })->orderBy('created_at', 'desc')->get();

        if ($documents->isEmpty()) {
            return back()->with('status', 'No hay documentos subidos para consolidar.');
        }

        // Unir cada PDF
        foreach ($documents as $doc) {
            $filePath = storage_path('app/' . $doc->file_path);
            
            if (file_exists($filePath)) {
                $pageCount = $pdf->setSourceFile($filePath);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $templateId = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($templateId);
                    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $pdf->useTemplate($templateId);
                }
                $tempFilesToDelete[] = $doc->file_path; 
            }
        }

        // Guardar el PDF consolidado en la carpeta de resoluciones (acceso público)
        $finalFileName = 'expediente_final_' . $agreement->id . '_' . time() . '.pdf';
        $finalPath = 'resoluciones/' . $finalFileName;
        
        $absolutePath = storage_path('app/public/' . $finalPath);
        
        // Asegurarnos de que la carpeta existe
        if (!file_exists(dirname($absolutePath))) {
            mkdir(dirname($absolutePath), 0755, true);
        }

        // Crear el archivo físico
        $pdf->Output($absolutePath, 'F');

        // Registrarlo en la tabla de documentos principal del convenio
        $agreement->documents()->create([
            'name' => 'Expediente Consolidado Final',
            'file_path' => $finalPath,
            'extension' => 'pdf' // Mantenemos tu estructura
        ]);

        // Eliminar archivos físicos temporales
        foreach($tempFilesToDelete as $tempFile) {
            Storage::delete($tempFile);
        }
        // Eliminar los registros de la base de datos
        RoadmapDocument::whereIn('file_path', $tempFilesToDelete)->delete();

        return back()->with('status', 'Expediente generado y archivos temporales limpiados con éxito.');
    }
}