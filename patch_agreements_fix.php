<?php
$content = file_get_contents('app/Http/Controllers/AgreementController.php');

$search = <<<'PHP'
            $path = $file->store('resoluciones', 'public');
            app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $path));
PHP;

$replace = <<<'PHP'
            $path = $file->store('resoluciones', 'public');
            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                app(\App\Services\PdfCompressorService::class)->compressPdf(storage_path('app/public/' . $path));
            }
PHP;

$content = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/AgreementController.php', $content);
