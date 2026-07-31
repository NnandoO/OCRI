<?php
$content = file_get_contents('app/Http/Controllers/SeguimientoController.php');

// Remove max sizes
$content = str_replace('|max:10240', '', $content);
$content = str_replace('max:10240', '', $content);

// Add compressor logic for work plan
$compressorLogic1 = <<<'PHP'
        $path = $file->store('work_plans', 'public');
        if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
            app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $path));
        }
PHP;
$content = str_replace("\$path = \$file->store('work_plans', 'public');", $compressorLogic1, $content);

// Add compressor logic for oficio_path
$compressorLogic2 = <<<'PHP'
            $report->oficio_path = $request->file('oficio_file')->store('reports', 'public');
            if (strtolower($request->file('oficio_file')->getClientOriginalExtension()) === 'pdf') {
                app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $report->oficio_path));
            }
PHP;
$content = str_replace("\$report->oficio_path = \$request->file('oficio_file')->store('reports', 'public');", $compressorLogic2, $content);

// Add compressor logic for respuesta_path
$compressorLogic3 = <<<'PHP'
            $report->respuesta_path = $request->file('respuesta_file')->store('reports', 'public');
            if (strtolower($request->file('respuesta_file')->getClientOriginalExtension()) === 'pdf') {
                app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $report->respuesta_path));
            }
PHP;
$content = str_replace("\$report->respuesta_path = \$request->file('respuesta_file')->store('reports', 'public');", $compressorLogic3, $content);

file_put_contents('app/Http/Controllers/SeguimientoController.php', $content);
