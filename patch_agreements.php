<?php
$content = file_get_contents('app/Http/Controllers/AgreementController.php');

// Remove max sizes
$content = str_replace('|max:10240', '', $content);
$content = str_replace('max:10240', '', $content);

// Add compressor logic
$compressorLogic = <<<'PHP'
            $path = $file->store('resoluciones', 'public');
            app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $path));
PHP;
$content = str_replace("\$path = \$file->store('resoluciones', 'public');", $compressorLogic, $content);

file_put_contents('app/Http/Controllers/AgreementController.php', $content);
