<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\Oficio;
use App\Models\RoadmapItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OficioGeneratorService
{
    protected array $documentosAdjuntar = [];

    public function setDocumentosAdjuntar(array $paths): void
    {
        $this->documentosAdjuntar = $paths;
    }

    protected function getYearName(): string
    {
        $year = date('Y');
        $names = [
            '2023' => 'AÑO DE LA UNIDAD, LA PAZ Y EL DESARROLLO',
            '2024' => 'AÑO DEL BICENTENARIO, DE LA CONSOLIDACIÓN DE NUESTRA INDEPENDENCIA, Y DE LA CONMEMORACIÓN DE LAS HEROICAS BATALLAS DE JUNÍN Y AYACUCHO',
            '2025' => 'AÑO DEL BICENTENARIO DEL INICIO DE LA INDEPENDENCIA DEL PERÚ',
            '2026' => 'AÑO DE LA RECUPERACION Y CONSOLIDACION DE LA ECONOMÍA PERUANA',
        ];
        return $names[$year] ?? "AÑO {$year}";
    }

    public function generateOpinionOficio(Agreement $agreement, RoadmapItem $item, string $directedTo, string $oficioNumber): Oficio
    {
        $filename = $this->sanitizeFilename("OFICIO N° {$oficioNumber} OCRI UNCP") . '.pdf';
        $relativePath = "oficios/{$agreement->id}/{$filename}";

        $bodyHtml = $this->buildOpinionHtml($agreement, $directedTo);

        $oficio = $agreement->oficios()->create([
            'roadmap_item_id' => $item->id,
            'area_name' => $item->area_name,
            'directed_to' => $directedTo,
            'oficio_number' => $oficioNumber,
            'file_path' => $relativePath,
            'file_original_name' => $filename,
            'type' => 'opinion',
            'body_html' => $bodyHtml,
            'status' => 'draft',
        ]);

        return $oficio;
    }

    public function generateExpedienteFinal(
        Agreement $agreement,
        string $directedTo,
        string $oficioNumber,
        string $referenciaText,
        array $documentosAdjuntar = []
    ): Oficio {
        $filename = "OFICIO N° {$oficioNumber} OCRI UNCP - EXPEDIENTE FINAL RECTORADO.pdf";
        $relativePath = "oficios/{$agreement->id}/{$filename}";

        $bodyHtml = $this->buildFinalHtml($agreement, $directedTo, $referenciaText);

        $oficio = $agreement->oficios()->create([
            'roadmap_item_id' => null,
            'area_name' => 'RECTORADO',
            'directed_to' => $directedTo,
            'oficio_number' => $oficioNumber,
            'file_path' => $relativePath,
            'file_original_name' => $filename,
            'type' => 'final',
            'body_html' => $bodyHtml,
            'status' => 'draft',
        ]);

        return $oficio;
    }

    public function renderAndSavePdf(Oficio $oficio): void
    {
        $yearName = $this->getYearName();
        $dateText = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');

        $pdf = Pdf::loadView('pdf.oficio', compact('oficio', 'yearName', 'dateText'))
                  ->setPaper('a4')
                  ->setWarnings(false);

        $fullPath = storage_path("app/public/{$oficio->file_path}");
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf->save($fullPath);

        // Si es oficio final, fusionar los documentos adjuntos
        if ($oficio->type === 'final') {
            $this->mergeExpedienteFinal($oficio, $fullPath);
        }

        // Update status if it was draft
        if ($oficio->status === 'draft') {
            $oficio->update(['status' => 'generated']);
            
            // Register in documents if it's the first time generating
            if ($oficio->type === 'opinion' && $oficio->roadmap_item_id) {
                $item = RoadmapItem::find($oficio->roadmap_item_id);
                if ($item) {
                    $item->documents()->firstOrCreate([
                        'original_name' => $oficio->file_original_name,
                    ], [
                        'file_path' => $oficio->file_path,
                        'type' => 'entrada',
                    ]);
                }
            } elseif ($oficio->type === 'final') {
                $oficio->agreement->documents()->firstOrCreate([
                    'name' => 'Expediente Final a Rectorado',
                ], [
                    'file_path' => $oficio->file_path,
                    'extension' => 'pdf',
                ]);
            }
        }
    }

    protected function mergeExpedienteFinal(Oficio $oficio, string $basePdfPath): void
    {
        $agreement = $oficio->agreement;

        $pdfPaths = [];

        // Usar paths pasados desde el controlador (más confiable)
        if (!empty($this->documentosAdjuntar)) {
            foreach ($this->documentosAdjuntar as $path) {
                if (file_exists($path)) {
                    $pdfPaths[] = $path;
                } else {
                    Log::warning("[Expediente Final] Path faltante (del controlador): {$path}");
                }
            }
        } else {
            // Fallback: leer desde la BD
            $agreement->load('roadmapItems.documents');
            $items = $agreement->roadmapItems->reject(function($i) {
                return strtolower(trim($i->area_name)) === 'rectorado';
            })->sortByDesc(function($item) {
                $latest = $item->documents->sortByDesc('created_at')->first();
                return $latest ? $latest->created_at : $item->created_at;
            });

            foreach ($items as $item) {
                $salidas = $item->documents->where('type', 'salida')->sortByDesc('created_at');
                $entradas = $item->documents->where('type', 'entrada')->sortByDesc('created_at');

                foreach ($salidas as $doc) {
                    $p = storage_path('app/public/' . $doc->file_path);
                    if (file_exists($p)) {
                        $pdfPaths[] = $p;
                    } else {
                        Log::warning("[Expediente Final] Salida faltante (fallback): {$p}");
                    }
                }
                foreach ($entradas as $doc) {
                    $p = storage_path('app/public/' . $doc->file_path);
                    if (file_exists($p)) {
                        $pdfPaths[] = $p;
                    } else {
                        Log::warning("[Expediente Final] Entrada faltante (fallback): {$p}");
                    }
                }
            }
        }

        if (empty($pdfPaths)) {
            Log::warning("[Expediente Final] No hay documentos adjuntos para fusionar.");
            return;
        }

        try {
            $tempDir = storage_path('app/public/oficios/' . $agreement->id . '/tmp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $tempMerged = $tempDir . '/fusionado_temp_' . uniqid() . '.pdf';

            $inputFiles = escapeshellarg($basePdfPath);
            foreach ($pdfPaths as $pdfPath) {
                $inputFiles .= ' ' . escapeshellarg($pdfPath);
            }

            Log::info("[Expediente Final] Fusionando " . (count($pdfPaths) + 1) . " PDFs con Ghostscript");

            $cmd = sprintf(
                'gs -sDEVICE=pdfwrite -dNOPAUSE -dBATCH -dCompatibilityLevel=1.4 -sOutputFile=%s %s 2>&1',
                escapeshellarg($tempMerged),
                $inputFiles
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                Log::error("[Expediente Final] Ghostscript error: " . implode("\n", $output));
                throw new \RuntimeException("Ghostscript falló con código {$exitCode}");
            }

            if (!file_exists($tempMerged) || filesize($tempMerged) === 0) {
                throw new \RuntimeException("El archivo fusionado está vacío o no se generó.");
            }

            $mergedSize = filesize($tempMerged);
            copy($tempMerged, $basePdfPath);
            unlink($tempMerged);

            Log::info("[Expediente Final] Fusionado correctamente con Ghostscript: {$basePdfPath} ({$mergedSize} bytes, " . (count($pdfPaths) + 1) . " PDFs)");

        } catch (\Exception $e) {
            Log::error("[Expediente Final] Error en fusión Ghostscript: " . $e->getMessage());
        }
    }

    protected function buildOpinionHtml(Agreement $agreement, string $directedTo): string
    {
        $hasDictamen = $agreement->dictamen_path && Storage::disk('public')->exists($agreement->dictamen_path);
        
        $html = '<p>Señor(a):<br><strong>' . e($directedTo) . '</strong><br>Presente.-</p>';
        $html .= '<p><strong>ASUNTO:</strong> SOLICITUD DE OPINIÓN TÉCNICA PARA EL CONVENIO: ' . e($agreement->name) . '</p>';
        
        $html .= '<p style="text-align: justify;">Tengo el agrado de dirigirme a usted, ';
        if ($hasDictamen) {
            $html .= 'en atención a que mediante el Dictamen N° ' . e($agreement->resolution_number) . ', se ha recibido en esta Oficina de Cooperación y Relaciones Internacionales el proyecto del Convenio: <strong>' . e($agreement->name) . '</strong>, para su correspondiente tramitación.</p>';
        } else {
            $html .= 'en relación al Convenio: <strong>' . e($agreement->name) . '</strong>, que se viene tramitando en esta Oficina de Cooperación y Relaciones Internacionales.</p>';
        }
        
        $html .= '<p style="text-align: justify;">En tal sentido, y de conformidad con las atribuciones establecidas, remito a su despacho el proyecto de convenio a fin de que, en el marco de sus competencias, se sirva emitir la OPINIÓN TÉCNICA correspondiente y la factibilidad de la suscripción del referido instrumento.</p>';
        $html .= '<p style="text-align: justify;">Sin otro particular, hago propicia la oportunidad para expresarle las muestras de mi especial consideración y estima personal.</p>';
        $html .= '<p>Atentamente,</p>';
        
        return $html;
    }

    protected function buildFinalHtml(Agreement $agreement, string $directedTo, string $referenciaText): string
    {
        $html = '<p>Señor(a):<br><strong>' . e($directedTo) . '</strong><br>Presente.-</p>';
        $html .= '<p><strong>ASUNTO:</strong> SOLICITUD DE SUSCRIPCIÓN DEL CONVENIO: ' . e($agreement->name) . '</p>';
        $html .= '<p><strong>Referencia:</strong> ' . e($referenciaText) . '</p>';
        
        $html .= '<p style="text-align: justify;">Tengo el agrado de dirigirme a usted, con la finalidad de solicitar la SUSCRIPCIÓN del Convenio: <strong>' . e($agreement->name) . '</strong>, el cual ha sido debidamente revisado y cuenta con las opiniones técnicas favorables de las áreas correspondientes.</p>';
        $html .= '<p style="text-align: justify;">Se adjunta el expediente completo con el historial de opiniones técnicas emitidas, para su evaluación y fines pertinentes.</p>';
        $html .= '<p style="text-align: justify;">Sin otro particular, hago propicia la oportunidad para expresarle las muestras de mi especial consideración y estima personal.</p>';
        $html .= '<p>Atentamente,</p>';
        
        return $html;
    }

    protected function sanitizeFilename(string $name): string
    {
        $name = mb_strtoupper($name, 'UTF-8');
        $name = preg_replace('/[^A-Z0-9_\-\.\s]/', '', $name);
        $name = preg_replace('/\s+/', '_', $name);
        $name = trim($name, '_');
        return $name;
    }
}
