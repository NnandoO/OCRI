<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Oficio;
use App\Models\RoadmapItem;
use App\Services\OficioGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OficioController extends Controller
{
    protected $oficioGenerator;

    public function __construct(OficioGeneratorService $oficioGenerator)
    {
        $this->oficioGenerator = $oficioGenerator;
    }

    public function create(Agreement $agreement)
    {
        $agreement->load('roadmapItems');

        $currentYear = date('Y');
        $lastOficio = \App\Models\Oficio::whereYear('created_at', $currentYear)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOficio) {
            $parts = explode('-', $lastOficio->oficio_number);
            $lastNum = is_numeric($parts[0]) ? (int)$parts[0] : 0;
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        $nextOficioNumber = str_pad($nextNum, 3, '0', STR_PAD_LEFT) . '-' . $currentYear;

        return view('agreements.oficios', compact('agreement', 'nextOficioNumber'));
    }

    public function store(Request $request, Agreement $agreement)
    {
        $areas = $request->input('areas', []);

        if (empty($areas)) {
            return back()->withErrors(['areas' => 'Debe configurar al menos un area.']);
        }

        foreach ($areas as $itemId => $data) {
            $item = RoadmapItem::findOrFail($itemId);

            $validated = \Illuminate\Support\Facades\Validator::make($data, [
                'directed_to' => 'required|string|max:500',
                'oficio_number' => 'required|string|max:100',
            ])->validate();

            $this->oficioGenerator->generateOpinionOficio(
                $agreement,
                $item,
                $validated['directed_to'],
                $validated['oficio_number']
            );
        }

        return redirect()->route('agreements.show', $agreement->id)
            ->with('status', 'Oficios generados correctamente para todas las areas.');
    }

    public function generateExpedienteFinal(Request $request, Agreement $agreement)
    {
        $validated = $request->validate([
            'directed_to' => 'required|string|max:500',
            'oficio_number' => 'required|string|max:100',
        ]);

        $agreement->load(['roadmapItems.documents', 'oficios']);

        // Eliminar expediente final anterior (si existe) para regenerarlo
        $oldFinal = $agreement->oficios->where('type', 'final')->first();
        if ($oldFinal) {
            $oldPath = storage_path('app/public/' . $oldFinal->file_path);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $agreement->documents()->where('name', 'Expediente Final a Rectorado')->delete();
            $oldFinal->delete();
            $agreement->load('oficios'); // recargar
        }

        // Obtener todos los documentos, agrupar por área, ordenar áreas por el más reciente
        $items = $agreement->roadmapItems->reject(function($i) {
            return strtolower(trim($i->area_name)) === 'rectorado';
        })->sortByDesc(function($item) {
            $latest = $item->documents->sortByDesc('created_at')->first();
            return $latest ? $latest->created_at : $item->created_at;
        });

        $referencias = [];
        $documentosAdjuntar = [];
        $sinPdf = fn($n) => str_replace(['.pdf', '_'], ['', '-'], $n);

        foreach ($items as $item) {
            $entradas = $item->documents->where('type', 'entrada')->sortByDesc('created_at');
            $salidas = $item->documents->where('type', 'salida')->sortByDesc('created_at');

            foreach ($salidas as $doc) {
                $referencias[] = $sinPdf($doc->original_name);
                $p = storage_path('app/public/' . $doc->file_path);
                if (file_exists($p)) {
                    $documentosAdjuntar[] = $p;
                } else {
                    \Illuminate\Support\Facades\Log::warning("[Expediente Final Controller] Archivo faltante: {$p}");
                }
            }
            foreach ($entradas as $doc) {
                $referencias[] = $sinPdf($doc->original_name);
                $p = storage_path('app/public/' . $doc->file_path);
                if (file_exists($p)) {
                    $documentosAdjuntar[] = $p;
                } else {
                    \Illuminate\Support\Facades\Log::warning("[Expediente Final Controller] Archivo faltante: {$p}");
                }
            }
        }
        $referenciaText = !empty($referencias) ? implode(', ', $referencias) : '';

        if (empty($referenciaText)) {
            $referenciaText = "No se registran opiniones previas.";
        }

        // Pasar los paths al servicio para la fusión
        $this->oficioGenerator->setDocumentosAdjuntar($documentosAdjuntar);

        $oficio = $this->oficioGenerator->generateExpedienteFinal(
            $agreement,
            $validated['directed_to'],
            $validated['oficio_number'],
            $referenciaText,
            $documentosAdjuntar
        );

        // Ya no generamos el PDF automáticamente, redirigimos al editor
        return redirect()->route('oficios.edit', $oficio->id)
            ->with('status', 'Borrador de Expediente Final creado. Por favor revise y confirme.');
    }

    public function download(Oficio $oficio)
    {
        if (!Storage::disk('public')->exists($oficio->file_path)) {
            return back()->withErrors(['error' => 'El archivo del oficio no se encuentra en el servidor.']);
        }

        return Storage::disk('public')->download(
            $oficio->file_path,
            $oficio->file_original_name
        );
    }

    public function edit(Oficio $oficio)
    {
        return view('agreements.oficio_edit', compact('oficio'));
    }

    public function update(Request $request, Oficio $oficio)
    {
        $validated = $request->validate([
            'body_html' => 'required|string',
        ]);

        $oficio->update([
            'body_html' => $validated['body_html'],
        ]);

        // Generate the PDF
        $this->oficioGenerator->renderAndSavePdf($oficio);

        return redirect()->route('agreements.show', $oficio->agreement_id)
            ->with('status', 'Oficio generado correctamente en PDF.');
    }
}
