<x-layouts::app :title="'Expediente: ' . $agreement->title">
    {{-- Inicializamos Alpine. Se usa str_replace para evitar errores con las barras invertidas --}}
    <div class="p-6 max-w-7xl mx-auto space-y-6" 
         x-data="{ activePdf: '{{ $agreement->documents->first() ? asset('storage/' . str_replace('\\', '/', $agreement->documents->first()->file_path)) : '' }}' }">
        
        @php
            // Lógica de control
            $hasRoadmap = $agreement->roadmapItems && $agreement->roadmapItems->count() > 0;
            $pendingCount = $hasRoadmap ? $agreement->roadmapItems->where('is_completed', false)->count() : 0;
            $hasFinalDoc = $agreement->documents->count() > 0;
            
            // Detecta si es un convenio histórico (Tiene Convenio PDF pero no tiene ruta)
            $isHistorical = (!$hasRoadmap && $hasFinalDoc);
            
            $allOpinionsReady = ($hasRoadmap && $pendingCount === 0) || $isHistorical;
            $notYetVigente = ($agreement->status !== 'Vigente');

            // Extraemos los PDFs de las opiniones individuales para la hoja de ruta (Izquierda)
            $opinionDocs = $hasRoadmap ? $agreement->roadmapItems->flatMap(function($item) {
                return $item->documents;
            })->sortByDesc('created_at') : collect();

            // Identificamos y separamos los documentos para los botones de descarga directa
            $expedienteDoc = $agreement->documents->first(fn($doc) => str_contains($doc->name, 'Solo Opiniones'));
            $convenioDoc = $agreement->documents->first(fn($doc) => !str_contains($doc->name, 'Solo Opiniones'));
        @endphp

        {{-- 1. ENCABEZADO PRINCIPAL --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-zinc-800 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div class="flex-1 min-w-0 pr-4">
                <flux:heading size="xl" class="break-words">{{ $agreement->title }}</flux:heading>
                <div class="flex items-center gap-2 mt-2">
                    <flux:icon name="building-library" class="size-4 text-zinc-400 shrink-0" />
                    <flux:text variant="subheading" class="font-medium truncate">{{ $agreement->institution->name }}</flux:text>
                </div>
            </div>
            <div class="flex flex-col items-start md:items-end shrink-0">
                <flux:badge size="lg" :color="$agreement->status === 'Vigente' ? 'green' : 'zinc'" variant="subtle">
                    {{ $agreement->status }}
                </flux:badge>
                <flux:text size="xs" class="mt-2 text-zinc-500">Registrado el {{ $agreement->created_at->format('d/m/Y') }}</flux:text>
            </div>
        </div>

        {{-- 2. FORMULARIO DE ACTIVACIÓN --}}
        @if($allOpinionsReady && $notYetVigente)
            <flux:card class="border-2 border-green-500 bg-green-50/50 dark:bg-green-900/10 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <flux:icon name="check-circle" variant="solid" class="text-green-600 dark:text-green-500 size-6 shrink-0" />
                    <flux:heading size="lg" class="text-green-800 dark:text-green-400">¡Expediente Aprobado!</flux:heading>
                </div>
                
                <flux:text class="mb-6 text-zinc-700 dark:text-zinc-300 text-sm">
                    Complete los datos de la <b>Resolución Rectoral</b> para oficializar la vigencia del convenio en el sistema.
                </flux:text>

                <form action="{{ route('agreements.activate', $agreement->id) }}" method="POST" class="space-y-5">
                    @csrf 
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <flux:field>
                            <flux:label>N° Resolución Rectoral</flux:label>
                            <flux:input name="resolution_number" icon="hashtag" placeholder="Ej. 0123-2026" value="{{ $agreement->resolution_number }}" required />
                        </flux:field>
                        <flux:field>
                            <flux:label>Fecha de Firma</flux:label>
                            <flux:input type="date" name="signature_date" required />
                        </flux:field>
                        <flux:field>
                            <flux:label>Inicio de Vigencia</flux:label>
                            <flux:input type="date" name="start_date" required />
                        </flux:field>
                        <flux:field>
                            <flux:label>Fin de Vigencia</flux:label>
                            <flux:input type="date" name="end_date" required />
                        </flux:field>
                    </div>

                    <div class="flex justify-end pt-2">
                        <flux:button type="submit" variant="primary" icon="paper-airplane">
                            Activar Convenio en el Sistema
                        </flux:button>
                    </div>
                </form>
            </flux:card>
        @endif

        {{-- 3. LAYOUT PRINCIPAL A DOS COLUMNAS --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- COLUMNA IZQUIERDA (Hoja de Ruta y Notas) --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                
                @if($isHistorical)
                    <flux:card class="border border-green-200 dark:border-green-800/50 bg-green-50/30 dark:bg-green-900/10">
                        <div class="flex items-center gap-2 mb-4">
                            <flux:icon name="shield-check" class="size-6 text-green-600 dark:text-green-500" />
                            <flux:heading size="md" class="text-green-800 dark:text-green-400">Opiniones Consolidadas</flux:heading>
                        </div>
                        <p class="text-sm text-green-700 dark:text-green-300 mb-5">
                            Este convenio se registró directamente con el documento final. Las opiniones técnicas obligatorias se consideran validadas sin adjuntos.
                        </p>
                        
                        <div class="space-y-2 opacity-80 pointer-events-none mb-6">
                            @foreach(['Rectorado', 'Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Asesoría Legal'] as $area)
                                <div class="flex items-center gap-3 p-3 bg-white dark:bg-zinc-800 border border-green-200 dark:border-green-900/50 rounded-xl shadow-sm">
                                    <div class="size-6 flex items-center justify-center rounded border bg-green-500 border-green-500 shrink-0">
                                        <flux:icon name="check" class="size-4 text-white" />
                                    </div>
                                    <span class="text-sm font-medium text-zinc-500 line-through">{{ $area }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-green-200 dark:border-green-900/50">
                            @if($convenioDoc)
                                <flux:button as="a" href="{{ asset('storage/' . str_replace('\\', '/', $convenioDoc->file_path)) }}" target="_blank" variant="primary" icon="document-text" class="w-full bg-green-600 hover:bg-green-700 text-white border-none">
                                    Descargar Convenio
                                </flux:button>
                            @endif
                        </div>
                    </flux:card>

                @elseif(!$hasRoadmap)
                    <flux:card>
                        <flux:heading size="md" class="mb-2">Configurar Hoja de Ruta</flux:heading>
                        <flux:text size="sm" class="mb-5 text-zinc-500">Seleccione las áreas para el seguimiento de opiniones técnicas:</flux:text>
                        
                        <form action="{{ route('agreements.roadmap.store', $agreement->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="flex flex-col gap-2">
                                @php $defaultAreas = ['Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Asesoría Legal', 'CEPRE', 'Rectorado', 'CEID']; @endphp
                                @foreach($defaultAreas as $area)
                                    <label class="flex items-center gap-3 p-3 bg-zinc-50 dark:bg-zinc-800/50 border border-zinc-200 dark:border-zinc-700 rounded-lg cursor-pointer hover:border-blue-400 transition-colors">
                                        <input type="checkbox" name="areas[]" value="{{ $area }}" class="rounded text-blue-600 focus:ring-blue-500 shrink-0">
                                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $area }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <flux:field class="pt-2">
                                <flux:label>Otras áreas (separadas por comas)</flux:label>
                                <flux:input name="extra_areas" placeholder="Ej: Facultad de Sistemas..." />
                            </flux:field>
                            <flux:button type="submit" variant="primary" class="w-full">Crear Hoja de Ruta</flux:button>
                        </form>
                    </flux:card>

                @else
                    <flux:card>
                        <flux:heading size="md" class="mb-6">Hoja de Ruta de Opiniones</flux:heading>
                        
                        <div class="space-y-3">
                            @foreach($agreement->roadmapItems as $item)
                                <div class="flex flex-col p-3 {{ $item->is_completed ? 'bg-green-50/40 dark:bg-green-900/10 border-green-200 dark:border-green-900/30' : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }} border rounded-xl shadow-sm transition-colors">
                                    
                                    <div class="flex items-center justify-between w-full gap-3">
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            <form action="{{ route('agreements.roadmap.check', $item->id) }}" method="POST" class="m-0 p-0 shrink-0">
                                                @csrf 
                                                @method('PATCH')
                                                <button type="submit" class="size-6 flex items-center justify-center rounded border transition-colors {{ $item->is_completed ? 'bg-green-500 border-green-500' : 'bg-zinc-50 dark:bg-zinc-900 border-zinc-300 dark:border-zinc-600 hover:border-zinc-400' }}">
                                                    @if($item->is_completed) <flux:icon name="check" class="size-4 text-white" /> @endif
                                                </button>
                                            </form>

                                            <span class="text-sm truncate font-medium {{ $item->is_completed ? 'line-through text-zinc-400 dark:text-zinc-500' : 'text-zinc-700 dark:text-zinc-200' }}" title="{{ $item->area_name }}">
                                                {{ $item->area_name }}
                                            </span>
                                        </div>

                                        @if($item->area_name !== 'Rectorado' && !$allOpinionsReady)
                                            <div class="shrink-0">
                                                <form action="{{ route('agreements.roadmap.upload', $item->id) }}" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                                    @csrf
                                                    <label for="file-upload-{{ $item->id }}" class="cursor-pointer inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors" title="Adjuntar documento">
                                                        <flux:icon name="paper-clip" class="size-5" />
                                                    </label>
                                                    <input id="file-upload-{{ $item->id }}" type="file" name="document" accept=".pdf" class="hidden" onchange="this.form.submit()" />
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    @if($item->documents && $item->documents->count() > 0)
                                        <div class="mt-2 pl-9 space-y-1 border-t border-zinc-100 dark:border-zinc-700/50 pt-2">
                                            @foreach($item->documents->sortByDesc('created_at') as $doc)
                                                <div class="flex items-center justify-between text-[11px] bg-zinc-50 dark:bg-zinc-900/50 pl-2 pr-1 py-1 rounded border border-zinc-200/50 dark:border-zinc-700/50 group">
                                                    <div class="flex items-center gap-1.5 flex-1 min-w-0 pr-2">
                                                        <flux:icon name="document" variant="mini" class="size-3 text-zinc-400 shrink-0" />
                                                        <span class="truncate text-zinc-600 dark:text-zinc-400" title="{{ $doc->original_name }}">{{ $doc->original_name }}</span>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-1 shrink-0">
                                                        <span class="text-zinc-400 mr-2">{{ $doc->created_at->format('d/m H:i') }}</span>
                                                        
                                                        <button type="button" class="text-zinc-400 hover:text-blue-500 transition-colors p-0.5 rounded focus:outline-none" title="Ver archivo" x-on:click="activePdf = '{{ asset('storage/' . str_replace('\\', '/', $doc->file_path)) }}'">
                                                            <flux:icon name="eye" variant="mini" class="size-4" />
                                                        </button>
                                                        
                                                        <a href="{{ asset('storage/' . str_replace('\\', '/', $doc->file_path)) }}" target="_blank" class="text-zinc-400 hover:text-green-500 transition-colors p-0.5 rounded focus:outline-none" title="Descargar archivo">
                                                            <flux:icon name="arrow-down-tray" variant="mini" class="size-4" />
                                                        </a>

                                                        <form action="{{ route('agreements.roadmap.delete-doc', $doc->id) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Estás seguro de eliminar este archivo?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-zinc-400 hover:text-red-500 transition-colors p-0.5 rounded focus:outline-none" title="Eliminar archivo">
                                                                <flux:icon name="trash" variant="mini" class="size-4" />
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        {{-- Botones de Descarga Directa --}}
                        @if($allOpinionsReady)
                            <div class="mt-6 pt-5 border-t border-zinc-200 dark:border-zinc-700 space-y-3">
                                @if(!$expedienteDoc)
                                    <form action="{{ route('agreements.roadmap.consolidate', $agreement->id) }}" method="POST" class="m-0 p-0">
                                        @csrf
                                        <flux:button type="submit" variant="primary" icon="document-duplicate" class="w-full bg-blue-600 hover:bg-blue-700">
                                            Unir y Generar Expediente
                                        </flux:button>
                                        <p class="mt-2 text-center text-xs text-zinc-500">
                                            Consolidará las opiniones técnicas en un solo PDF.
                                        </p>
                                    </form>
                                @else
                                    <flux:button as="a" href="{{ asset('storage/' . str_replace('\\', '/', $expedienteDoc->file_path)) }}" target="_blank" variant="primary" icon="arrow-down-tray" class="w-full bg-blue-600 hover:bg-blue-700 text-white border-none">
                                        Descargar Expediente (Opiniones)
                                    </flux:button>
                                @endif

                                @if($convenioDoc)
                                    <flux:button as="a" href="{{ asset('storage/' . str_replace('\\', '/', $convenioDoc->file_path)) }}" target="_blank" variant="outline" icon="document-text" class="w-full">
                                        Descargar Solo el Convenio
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </flux:card>
                @endif

                {{-- CUADRO DE SITUACIÓN / NOTAS (Movido debajo de Hoja de Ruta) --}}
                <flux:card class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 relative">
                    <div class="absolute top-4 right-4 text-amber-500 dark:text-amber-600">
                        <flux:icon name="pencil-square" class="size-5" />
                    </div>
                    <flux:heading size="sm" class="mb-3 text-amber-800 dark:text-amber-500">Situación / Notas del Convenio</flux:heading>
                    
                    <form action="{{ route('agreements.update', $agreement->id) }}" method="POST" class="m-0 p-0">
                        @csrf
                        @method('PUT')
                        <textarea name="situation" rows="2" 
                                  class="w-full text-sm rounded-lg border border-amber-200 dark:border-amber-700/50 bg-white dark:bg-zinc-900 focus:border-amber-500 focus:ring-amber-500 text-zinc-700 dark:text-zinc-300 placeholder-zinc-400 transition-colors resize-none p-3" 
                                  placeholder="Anota aquí la situación o estado actual del trámite...">{{ $agreement->situation ?? '' }}</textarea>
                        
                        <div class="flex justify-end mt-2">
                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg bg-amber-600 hover:bg-amber-700 text-white transition-colors">
                                Guardar Nota
                            </button>
                        </div>
                    </form>
                </flux:card>

            </div>

            {{-- COLUMNA DERECHA (Visor, Info y Acervo) --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                
                {{-- VISOR PDF --}}
                <div x-show="activePdf" style="display: none;" x-transition>
                    <flux:card class="space-y-4 border-zinc-300 dark:border-zinc-700 shadow-lg bg-zinc-50 dark:bg-zinc-800/80">
                        <div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-3">
                            <div class="flex items-center gap-2">
                                <flux:icon name="document-magnifying-glass" class="text-blue-600 dark:text-blue-400 size-6" />
                                <flux:heading size="lg">Visor del Documento Activo</flux:heading>
                            </div>
                            <flux:button icon="x-mark" size="sm" variant="subtle" x-on:click="activePdf = ''" title="Cerrar visor" />
                        </div>
                        
                        <div class="w-full h-[700px] bg-zinc-200 dark:bg-zinc-900 rounded-xl overflow-hidden border border-zinc-300 dark:border-zinc-700">
                            <iframe :src="activePdf" class="w-full h-full border-0" allow="autoplay"></iframe>
                        </div>
                    </flux:card>
                </div>

                {{-- Tarjeta de Información --}}
                <flux:card>
                    <flux:heading size="md" class="mb-5">Información del Registro</flux:heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-400 font-bold mb-1">Nombre Oficial</p>
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200 leading-relaxed">{{ $agreement->name }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-400 font-bold mb-1">Tipo de Alianza</p>
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $agreement->type->name ?? 'No especificado' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-400 font-bold mb-1">Resolución Rectoral</p>
                            <p class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $agreement->resolution_number ?? 'En Trámite de Aprobación' }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase tracking-wider text-zinc-400 font-bold mb-1">País de la IES</p>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $agreement->institution->country }}</span>
                            </div>
                        </div>
                    </div>
                </flux:card>

                {{-- Acervo Digital --}}
                <flux:card>
                    <div class="flex justify-between items-center mb-5">
                        <flux:heading size="md">Acervo Digital Final</flux:heading>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        {{-- ZONA NUEVA: Formulario de Subida Directa si no existe el convenio principal --}}
                        @if(!$convenioDoc)
                            <div class="p-4 border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/30 mb-2">
                                <form action="{{ route('agreements.upload-main', $agreement->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3 m-0 p-0">
                                    @csrf
                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                        <div class="flex-1">
                                            <span class="text-sm font-bold text-zinc-700 dark:text-zinc-200 block">Subir Convenio Firmado Principal</span>
                                            <span class="text-xs text-zinc-500 block mt-0.5">Adjunte directamente el PDF del convenio final para este registro.</span>
                                        </div>
                                        <div class="flex items-center gap-2 w-full sm:w-auto">
                                            <input type="file" name="document" accept=".pdf" required class="block text-sm text-zinc-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-zinc-800 dark:file:text-blue-400 border border-zinc-200 dark:border-zinc-700 rounded-md cursor-pointer flex-1 sm:flex-none" />
                                            <flux:button type="submit" size="sm" variant="primary" icon="arrow-up-tray">
                                                Subir
                                            </flux:button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        @endif

                        @if($agreement->documents->isEmpty())
                            <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                                <flux:icon name="document-magnifying-glass" class="size-10 text-zinc-300 mb-3" />
                                <p class="text-sm font-medium text-zinc-500">No hay documentos finales en el acervo.</p>
                                <p class="text-xs text-zinc-400 mt-1">Suba el convenio principal o una las opiniones de la hoja de ruta.</p>
                            </div>
                        @else
                            {{-- Solo mostramos los Documentos Principales ($agreement->documents) --}}
                            @foreach($agreement->documents as $doc)
                                @php
                                    $isConsolidated = str_contains($doc->name, 'Solo Opiniones');
                                    $isHistorical = str_contains($doc->name, 'Histórico');
                                    
                                    $displayName = $doc->name;
                                    $iconColor = 'text-blue-500';
                                    
                                    if ($isHistorical) {
                                        $displayName = 'Convenio Firmado (Registro Histórico)';
                                    } elseif ($isConsolidated) {
                                        $displayName = 'Expediente Final (Opiniones Unidas)';
                                        $iconColor = 'text-green-500';
                                    } else {
                                        $displayName = 'Convenio Firmado';
                                    }
                                @endphp

                                <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                    <div class="flex items-center gap-4 flex-1 min-w-0">
                                        <div class="p-2 bg-white dark:bg-zinc-900 rounded-lg shadow-sm shrink-0">
                                            <flux:icon name="document-text" class="{{ $iconColor }} size-6" />
                                        </div>
                                        <div class="flex flex-col flex-1 min-w-0">
                                            <span class="text-sm font-bold text-zinc-700 dark:text-zinc-200 truncate">{{ $displayName }}</span>
                                            <span class="text-xs text-zinc-500 mt-0.5">Subido el {{ $doc->created_at->format('d/m/Y') }} a las {{ $doc->created_at->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center gap-2 shrink-0 ml-4">
                                        <flux:button icon="eye" size="sm" variant="primary" 
                                                     x-on:click="activePdf = '{{ asset('storage/' . str_replace('\\', '/', $doc->file_path)) }}'" 
                                                     title="Visualizar en pantalla">
                                            Ver
                                        </flux:button>
                                        <flux:button as="a" href="{{ asset('storage/' . str_replace('\\', '/', $doc->file_path)) }}" target="_blank" icon="arrow-down-tray" size="sm" variant="outline" title="Descargar PDF" class="hidden sm:flex">
                                            Descargar
                                        </flux:button>
                                        
                                        {{-- BOTÓN DE ELIMINAR --}}
                                        <flux:button type="button" icon="trash" size="sm" variant="danger" 
                                                     onclick="if(confirm('¿Estás seguro de eliminar este archivo del acervo?')) { window.document.getElementById('delete-main-doc-{{ $doc->id }}').submit(); }" 
                                                     title="Eliminar PDF" class="hidden sm:flex" />
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- FORMULARIOS OCULTOS PARA ELIMINAR --}}
                            @foreach($agreement->documents as $doc)
                                <form id="delete-main-doc-{{ $doc->id }}" action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endforeach
                        @endif
                    </div>
                </flux:card>

            </div>
        </div>
    </div>
</x-layouts::app>