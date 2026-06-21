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
use setasign\Fpdi\Fpdi;

class AgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = Agreement::query()->with('institution');

        // Motor de Búsqueda Global en AgreementController.php
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
            'resolution_number' => 'nullable|string|max:100',
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
            // Todo apunta a resoluciones
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
    // FUNCIONES PARA LOS DOCUMENTOS (PDF)
    // ==========================================

    public function uploadDocument(Request $request, RoadmapItem $item)
    {
        if (!$request->hasFile('document')) {
            return redirect()->route('agreements.show', $item->agreement_id)
                ->withErrors(['document' => 'No se recibió el archivo.']);
        }

        $request->validate(['document' => 'required|mimes:pdf|max:10240']); 
        
        // Todo apunta a resoluciones
        $path = $request->file('document')->store('resoluciones', 'public');
        
        $item->documents()->create([
            'file_path' => $path,
            'original_name' => $request->file('document')->getClientOriginalName()
        ]);

        return redirect()->route('agreements.show', $item->agreement_id)
                         ->with('status', 'Documento subido correctamente al área.');
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
        // CORREGIDO: $do1ument -> $document
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

    public function consolidateExpedient(Agreement $agreement)
    {
        $pdf = new Fpdi();
        $tempFilesToDelete = [];

        // Obtener todos los documentos de las opiniones
        $documents = RoadmapDocument::whereHas('roadmapItem', function($query) use ($agreement) {
            $query->where('agreement_id', $agreement->id);
        })->orderBy('created_at', 'desc')->get();

        if ($documents->isEmpty()) {
            return back()->with('status', 'No hay documentos para consolidar.');
        }

        $mergedPages = 0;

        foreach ($documents as $doc) {
            $filePath = storage_path('app/public/' . $doc->file_path);
            
            if (file_exists($filePath)) {
                
                // 1. LA MAGIA: Usamos la herramienta que instalaste (gs) para quitarle la compresión al PDF
                $tempDowngradedPath = storage_path('app/public/temp_' . uniqid() . '.pdf');
                $cmd = "gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=" . escapeshellarg($tempDowngradedPath) . " " . escapeshellarg($filePath);
                exec($cmd);

                // 2. Le pasamos el archivo arreglado a FPDI
                $fileToProcess = file_exists($tempDowngradedPath) ? $tempDowngradedPath : $filePath;

                try {
                    $pageCount = $pdf->setSourceFile($fileToProcess);
                    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                        $templateId = $pdf->importPage($pageNo);
                        $size = $pdf->getTemplateSize($templateId);
                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($templateId);
                        $mergedPages++;
                    }
                } catch (\Exception $e) {
                    // Si un archivo está corrupto, lo ignoramos para que no rompa el sistema
                }

                // 3. Borramos el archivo temporal que usamos para arreglarlo
                if (file_exists($tempDowngradedPath)) {
                    unlink($tempDowngradedPath);
                }
                
                $tempFilesToDelete[] = $doc->file_path; 
            }
        }

        if ($mergedPages === 0) {
            return back()->with('status', 'Error: No se pudieron procesar los PDFs.');
        }

        // 4. Guardamos el PDF unido final en la carpeta resoluciones
        $finalFileName = 'expediente_opiniones_' . $agreement->id . '_' . time() . '.pdf';
        $finalPath = 'resoluciones/' . $finalFileName;
        $pdf->Output(storage_path('app/public/' . $finalPath), 'F');

        // 5. Lo registramos en la base de datos
        $agreement->documents()->create([
            'name' => 'Expediente Final (Solo Opiniones)',
            'file_path' => $finalPath,
            'extension' => 'pdf'
        ]);

        // 6. Eliminamos los PDFs sueltos de las opiniones
        foreach($tempFilesToDelete as $tempFile) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($tempFile);
        }
        RoadmapDocument::whereIn('file_path', $tempFilesToDelete)->delete();

        return back()->with('status', 'Opiniones unidas y expediente generado con éxito.');
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