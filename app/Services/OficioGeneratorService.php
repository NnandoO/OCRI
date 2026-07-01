<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\Oficio;
use App\Models\RoadmapItem;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OficioGeneratorService
{
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
        $pdf = $this->createDocument($agreement, $item, $directedTo, $oficioNumber, 'opinion');

        $filename = $this->sanitizeFilename(
            "OFICIO N° {$oficioNumber} OCRI UNCP"
        ) . '.pdf';

        $relativePath = "oficios/{$agreement->id}/{$filename}";
        $fullPath = storage_path("app/public/{$relativePath}");

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pdf->Output($fullPath, 'F');

        $oficio = $agreement->oficios()->create([
            'roadmap_item_id' => $item->id,
            'area_name' => $item->area_name,
            'directed_to' => $directedTo,
            'oficio_number' => $oficioNumber,
            'file_path' => $relativePath,
            'file_original_name' => $filename,
            'type' => 'opinion',
        ]);

        $item->documents()->create([
            'file_path' => $relativePath,
            'original_name' => $filename,
            'type' => 'entrada',
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
        $fullPath = storage_path("app/public/{$relativePath}");

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 1. Generar carátula del oficio en un PDF temporal
        $pdf = $this->createDocument($agreement, null, $directedTo, $oficioNumber, 'final', $referenciaText);
        $tmpCover = tempnam(sys_get_temp_dir(), 'cover_') . '.pdf';
        $pdf->Output($tmpCover, 'F');

        // 2. Filtrar solo los documentos que existen
        $inputs = [$tmpCover];
        foreach ($documentosAdjuntar as $docPath) {
            if (file_exists($docPath)) {
                $inputs[] = $docPath;
            } else {
                Log::warning("[Expediente Final] Archivo no encontrado: {$docPath}");
            }
        }

        // 3. Fusionar con Ghostscript
        $escapedInputs = implode(' ', array_map('escapeshellarg', $inputs));
        $escapedOutput = escapeshellarg($fullPath);
        $cmd = "gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -sOutputFile={$escapedOutput} {$escapedInputs} 2>&1";
        $output = null;
        $returnCode = null;
        exec($cmd, $output, $returnCode);

        // 4. Limpiar temporal
        if (file_exists($tmpCover)) {
            unlink($tmpCover);
        }

        if ($returnCode !== 0) {
            Log::error("[Expediente Final] Ghostscript falló: " . implode("\n", $output));
            throw new \RuntimeException("Error al fusionar PDFs con Ghostscript.");
        }

        // 5. Registrar en BD
        $oficio = $agreement->oficios()->create([
            'roadmap_item_id' => null,
            'area_name' => 'RECTORADO',
            'directed_to' => $directedTo,
            'oficio_number' => $oficioNumber,
            'file_path' => $relativePath,
            'file_original_name' => $filename,
            'type' => 'final',
        ]);

        $agreement->documents()->create([
            'name' => 'Expediente Final a Rectorado',
            'file_path' => $relativePath,
            'extension' => 'pdf',
        ]);

        return $oficio;
    }

    protected function createDocument(
        Agreement $agreement,
        ?RoadmapItem $item,
        string $directedTo,
        string $oficioNumber,
        string $type,
        string $referenciaText = ''
    ): Fpdi {
        $pdf = new Fpdi();
        $pdf->SetAutoPageBreak(true, 30);
        $pdf->AddPage();
        $pdf->SetMargins(25, 10, 20);

        $this->addHeader($pdf, $oficioNumber);
        $this->addBody($pdf, $agreement, $item, $directedTo, $oficioNumber, $type, $referenciaText);
        $this->addFooter($pdf);

        return $pdf;
    }

    protected function addHeader(Fpdi $pdf, string $oficioNumber): void
    {
        $logoPath = public_path('Logo-UNCP.png');

        // Logo UNCP (izquierda)
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 8, 22, 0, 'PNG');
        }


        $ocriLogo = public_path('ocri_logo.png');
        if (file_exists($ocriLogo)) {
            $pdf->Image($ocriLogo, 173, 8, 22, 0, 'PNG');
        } else {
            // Placeholder textual mientras se proporciona el logo oficial
            $pdf->SetXY(168, 10);
            $pdf->SetFont('Helvetica', 'B', 7);
            $pdf->Cell(28, 4, $this->t('O.C.R.I.'), 0, 1, 'C');
            $pdf->SetX(168);
            $pdf->SetFont('Helvetica', '', 6);
            $pdf->Cell(28, 3, $this->t('UNCP'), 0, 1, 'C');
            $pdf->SetDrawColor(0, 102, 153);
            $pdf->SetLineWidth(0.3);
            $pdf->Rect(168, 8, 28, 20);
        }

        $pdf->SetY(12);

        // Nombre de la universidad
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->Cell(0, 6, $this->t('UNIVERSIDAD NACIONAL DEL CENTRO DEL PERU'), 0, 1, 'C');

        // Nombre de la oficina
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 5, $this->t('OFICINA DE COOPERACION Y RELACIONES INTERNACIONALES'), 0, 1, 'C');
        $pdf->Ln(2);

        // Año
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->Cell(0, 5, '"' . $this->t($this->getYearName()) . '"', 0, 1, 'C');
        $pdf->Ln(4);

        // Línea separadora
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.3);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(4);

        // Lugar y fecha
        $fecha = 'Huancayo, ' . now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 5, $this->t($fecha), 0, 1, 'R');
        $pdf->Ln(2);

        // Número de oficio (a la izquierda, sin subrayado)
        $pdf->SetFont('Helvetica', 'B', 11);
        $pdf->Cell(0, 6, $this->t("OFICIO N° {$oficioNumber}"), 0, 1, 'L');
        $pdf->Ln(4);
    }

    protected function addBody(
        Fpdi $pdf,
        Agreement $agreement,
        ?RoadmapItem $item,
        string $directedTo,
        string $oficioNumber,
        string $type,
        string $referenciaText = ''
    ): void {
        // Destinatario
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 5, $this->t('Señor(a):'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->MultiCell(0, 5, $this->t($directedTo), 0, 'L');
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 5, $this->t('Presente.-'), 0, 1, 'L');
        $pdf->Ln(3);

        // ASUNTO
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(0, 5, $this->t('ASUNTO :'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 10);

        if ($type === 'opinion') {
            $asunto = "SOLICITUD DE OPINION TECNICA PARA EL CONVENIO: {$agreement->name}";
        } else {
            $asunto = "SOLICITUD DE SUSCRIPCION DEL CONVENIO: {$agreement->name}";
        }
        $pdf->MultiCell(0, 5, $this->t($asunto), 0, 'L');
        $pdf->Ln(2);

        // Referencia (solo para oficio final a rectorado)
        if ($type === 'final') {
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(0, 5, $this->t('Referencia :'), 0, 1, 'L');
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->MultiCell(0, 4, $this->t($referenciaText), 0, 'L');
            $pdf->Ln(3);
        }

        // Cuerpo
        $pdf->SetFont('Helvetica', '', 10);
        if ($type === 'opinion') {
            $hasDictamen = $agreement->dictamen_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($agreement->dictamen_path);
            $cuerpo = "Tengo el agrado de dirigirme a usted, ";
            if ($hasDictamen) {
                $cuerpo .= "en atencion a que mediante el Dictamen N° {$agreement->resolution_number}, se ha recibido en esta Oficina de Cooperacion y Relaciones Internacionales el proyecto del Convenio: {$agreement->name}, para su correspondiente tramitacion.\n\n";
            } else {
                $cuerpo .= "en relacion al Convenio: {$agreement->name}, que se viene tramitando en esta Oficina de Cooperacion y Relaciones Internacionales.\n\n";
            }
            $cuerpo .= "En tal sentido, y de conformidad con las atribuciones establecidas, remito a su despacho el proyecto de convenio a fin de que, en el marco de sus competencias, se sirva emitir la OPINION TECNICA correspondiente y la factibilidad de la suscripcion del referido instrumento.\n\n";
            $cuerpo .= "Sin otro particular, hago propicia la oportunidad para expresarle las muestras de mi especial consideracion y estima personal.";
        } else {
            $cuerpo = "Tengo el agrado de dirigirme a usted, con la finalidad de solicitar la SUSCRIPCION del Convenio: {$agreement->name}, el cual ha sido debidamente revisado y cuenta con las opiniones tecnicas favorables de las areas correspondientes.\n\n";
            $cuerpo .= "Se adjunta el expediente completo con el historial de opiniones tecnicas emitidas, para su evaluacion y fines pertinentes.\n\n";
            $cuerpo .= "Sin otro particular, hago propicia la oportunidad para expresarle las muestras de mi especial consideracion y estima personal.";
        }
        $pdf->MultiCell(0, 4.5, $this->t($cuerpo), 0, 'J');
        $pdf->Ln(6);

        // Despedida
        $pdf->SetFont('Helvetica', '', 10);
        $pdf->Cell(0, 5, $this->t('Atentamente,'), 0, 1, 'L');
        $pdf->Ln(14);

        // Firma escaneada
        $firmaPath = public_path('firma.png');
        if (file_exists($firmaPath)) {
            $pdf->Image($firmaPath, 80, $pdf->GetY() - 5, 50, 0, 'PNG');
            $pdf->Ln(28);
        } else {
            $pdf->Ln(20);
        }
    }

    protected function addFooter(Fpdi $pdf): void
    {
        $pdf->SetAutoPageBreak(false);
        $pdf->SetY(-15);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(25, $pdf->GetY() - 2, 185, $pdf->GetY() - 2);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell(0, 4, $this->t('c.c. Archivo'), 0, 1, 'L');
    }

    protected function t(string $text): string
    {
        $result = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        if ($result === false) {
            Log::warning('iconv conversion failed for text, using original', ['text' => $text]);
            return $text;
        }
        return $result;
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
