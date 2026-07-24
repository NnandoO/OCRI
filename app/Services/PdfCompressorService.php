<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PdfCompressorService
{
    /**
     * Comprime un archivo PDF in situ utilizando Ghostscript.
     * 
     * @param string $absolutePath Ruta absoluta al archivo PDF en el sistema.
     * @return bool True si la compresión fue exitosa, False si falló.
     */
    public function compressPdf(string $absolutePath): bool
    {
        if (!file_exists($absolutePath)) {
            Log::warning("[PdfCompressor] El archivo no existe: {$absolutePath}");
            return false;
        }

        $tempPath = $absolutePath . '.compressed.tmp';

        // dPDFSETTINGS=/screen (baja resolución, muy comprimido, aprox 72dpi, ideal visualización)
        // dPDFSETTINGS=/ebook (calidad media, 150dpi)
        // dPDFSETTINGS=/printer (alta calidad, 300dpi)
        $cmd = sprintf(
            'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dPDFSETTINGS=/screen -dNOPAUSE -dQUIET -dBATCH -sOutputFile=%s %s 2>&1',
            escapeshellarg($tempPath),
            escapeshellarg($absolutePath)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode === 0 && file_exists($tempPath) && filesize($tempPath) > 0) {
            // Reemplazar el original
            copy($tempPath, $absolutePath);
            unlink($tempPath);
            Log::info("[PdfCompressor] Comprimido exitosamente: {$absolutePath}");
            return true;
        } else {
            Log::error("[PdfCompressor] Error comprimiendo {$absolutePath}. Code: {$exitCode}. Salida: " . implode("\n", $output));
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
            return false;
        }
    }
}
