<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Agreement;
use Illuminate\Support\Facades\Storage;

$agreements = Agreement::with(['documents', 'oficios', 'roadmapItems.documents'])->get();

echo "=== CONVENIOS CON PDFs VINCULADOS ===\n";
echo str_repeat('=', 80) . "\n";

foreach ($agreements as $a) {
    $docs = [];

    foreach ($a->documents as $d) {
        $exists = Storage::disk('public')->exists($d->file_path) ? 'OK' : 'FALTA';
        $size = $exists === 'OK' ? ' ('.round(Storage::disk('public')->size($d->file_path)/1024, 1).'KB)' : '';
        $docs[] = "  [$exists] Principal: $d->name → $d->file_path$size";
    }

    foreach ($a->oficios as $o) {
        $exists = Storage::disk('public')->exists($o->file_path) ? 'OK' : 'FALTA';
        $size = $exists === 'OK' ? ' ('.round(Storage::disk('public')->size($o->file_path)/1024, 1).'KB)' : '';
        $docs[] = "  [$exists] Oficio({$o->type}): $o->oficio_number → $o->file_path$size";
    }

    foreach ($a->roadmapItems as $item) {
        foreach ($item->documents as $d) {
            $exists = Storage::disk('public')->exists($d->file_path) ? 'OK' : 'FALTA';
            $size = $exists === 'OK' ? ' ('.round(Storage::disk('public')->size($d->file_path)/1024, 1).'KB)' : '';
            $docs[] = "  [$exists] {$item->area_name}[{$d->type}]: {$d->original_name} → {$d->file_path}$size";
        }
    }

    if (!empty($docs)) {
        $num = $a->resolution_number ?? 'S/R';
        echo "\n{$a->id}. $num | {$a->name}\n";
        echo implode("\n", $docs) . "\n";
    }
}

$sinPDF = $agreements->filter(fn($a) =>
    $a->documents->count() + $a->oficios->count() +
    $a->roadmapItems->sum(fn($i) => $i->documents->count()) === 0
);

echo "\n=== SIN NINGÚN PDF ($sinPDF->count()) ===\n";
echo str_repeat('=', 80) . "\n";
foreach ($sinPDF as $a) {
    echo "{$a->id}. {$a->resolution_number} | {$a->name}\n";
}

$total = $agreements->count();
$con = $total - $sinPDF->count();
echo "\n=== RESUMEN ===\n";
echo "Total: $total | Con PDFs: $con | Sin PDFs: {$sinPDF->count()}\n";
