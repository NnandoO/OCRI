<x-layouts::app :title="'Expediente: ' . $agreement->title">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        @php
            // Lógica de control para la Hoja de Ruta
            $hasRoadmap = $agreement->roadmapItems && $agreement->roadmapItems->count() > 0;
            $pendingCount = $hasRoadmap ? $agreement->roadmapItems->where('is_completed', false)->count() : 0;
            $allOpinionsReady = ($hasRoadmap && $pendingCount === 0);
            $notYetVigente = ($agreement->status !== 'Vigente');
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

        {{-- 2. FORMULARIO DE ACTIVACIÓN (Solo visible si todo está aprobado) --}}
        @if($allOpinionsReady && $notYetVigente)
            <flux:card class="border-2 border-green-500 bg-green-50/50 dark:bg-green-900/10 shadow-sm">
                <div class="flex items-center gap-3 mb-3">
                    <flux:icon name="check-circle" variant="solid" class="text-green-600 dark:text-green-500 size-6 shrink-0" />
                    <flux:heading size="lg" class="text-green-800 dark:text-green-400">¡Opiniones Técnicas Finalizadas!</flux:heading>
                </div>
                
                <flux:text class="mb-6 text-zinc-700 dark:text-zinc-300 text-sm">
                    Se han recibido todas las respuestas. Complete los datos de la <b>Resolución Rectoral</b> para oficializar la vigencia del convenio.
                </flux:text>

                <form action="{{ route('agreements.activate', $agreement->id) }}" method="POST" class="space-y-5">
                    @csrf 
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <flux:field>
                            <flux:label>N° Resolución Rectoral</flux:label>
                            <flux:input name="resolution_number" icon="hashtag" placeholder="Ej. 0123-2026" required />
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
            
            {{-- COLUMNA IZQUIERDA (Hoja de Ruta) --}}
            <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                
                @if(!$hasRoadmap)
                    {{-- Caso: No hay Hoja de Ruta configurada --}}
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
                    {{-- Caso: Hoja de Ruta Activa --}}
                    <flux:card>
                        <flux:heading size="md" class="mb-6">Hoja de Ruta de Opiniones</flux:heading>
                        
                        <div class="space-y-3">
                            @foreach($agreement->roadmapItems as $item)
                                <div class="flex flex-col p-3 {{ $item->is_completed ? 'bg-green-50/40 dark:bg-green-900/10 border-green-200 dark:border-green-900/30' : 'bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700' }} border rounded-xl shadow-sm transition-colors">
                                    
                                    {{-- Fila principal: Check, Área y Botones de Subida --}}
                                    <div class="flex items-center justify-between w-full gap-3">
                                        
                                        {{-- Izquierda: Checkbox y Nombre del área seguros contra desbordes --}}
                                        <div class="flex items-center gap-3 flex-1 min-w-0">
                                            {{-- FORMULARIO 1 AISLADO: Checkbox --}}
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

                                        {{-- Derecha: Botón de Subida (Oculto como un Clip) --}}
                                        @if($item->area_name !== 'Rectorado' && !$allOpinionsReady)
                                            <div class="shrink-0">
                                                {{-- FORMULARIO 2 AISLADO: Subida de Archivo --}}
                                                <form action="{{ route('agreements.roadmap.upload', $item->id) }}" method="POST" enctype="multipart/form-data" class="m-0 p-0">
                                                    @csrf
                                                    <label for="file-upload-{{ $item->id }}" class="cursor-pointer inline-flex items-center justify-center rounded-md p-1.5 text-zinc-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/30 dark:hover:text-blue-400 transition-colors" title="Adjuntar documento">
                                                        <flux:icon name="paper-clip" class="size-5" />
                                                    </label>
                                                    <input 
                                                        id="file-upload-{{ $item->id }}" 
                                                        type="file" 
                                                        name="document" 
                                                        accept=".pdf" 
                                                        class="hidden" 
                                                        onchange="this.form.submit()" 
                                                    />
                                                </form>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Historial de PDFs subidos (Miniatura) --}}
                                    @if($item->documents && $item->documents->count() > 0 && !$allOpinionsReady)
                                        <div class="mt-2 pl-9 space-y-1 border-t border-zinc-100 dark:border-zinc-700/50 pt-2">
                                            @foreach($item->documents->sortByDesc('created_at') as $doc)
                                                <div class="flex items-center justify-between text-[11px] bg-zinc-50 dark:bg-zinc-900/50 pl-2 pr-1 py-1 rounded border border-zinc-200/50 dark:border-zinc-700/50 group">
                                                    <div class="flex items-center gap-1.5 flex-1 min-w-0 pr-2">
                                                        <flux:icon name="document" variant="mini" class="size-3 text-zinc-400 shrink-0" />
                                                        <span class="truncate text-zinc-600 dark:text-zinc-400" title="{{ $doc->original_name }}">{{ $doc->original_name }}</span>
                                                    </div>
                                                    
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-zinc-400">{{ $doc->created_at->format('d/m H:i') }}</span>
                                                        
                                                        {{-- Botón de Eliminar Archivo (Solo aparece al pasar el mouse - hover) --}}
                                                        <form action="{{ route('agreements.roadmap.delete-doc', $doc->id) }}" method="POST" class="m-0 p-0" onsubmit="return confirm('¿Estás seguro de eliminar este archivo?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-zinc-300 hover:text-red-500 transition-colors p-0.5 rounded focus:outline-none" title="Eliminar archivo">
                                                                <flux:icon name="trash" variant="mini" class="size-3" />
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

                        {{-- Botón Consolidador --}}
                        @if($allOpinionsReady && $notYetVigente)
                            <div class="mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                                {{-- FORMULARIO 3 AISLADO: Consolidar --}}
                                <form action="{{ route('agreements.roadmap.consolidate', $agreement->id) }}" method="POST" class="m-0 p-0">
                                    @csrf
                                    <flux:button type="submit" variant="primary" icon="document-duplicate" class="w-full bg-blue-600 hover:bg-blue-700">
                                        Generar Expediente Final
                                    </flux:button>
                                    <p class="mt-2 text-center text-xs text-zinc-500">
                                        Unirá los PDFs temporales y limpiará el historial.
                                    </p>
                                </form>
                            </div>
                        @endif
                    </flux:card>
                @endif
            </div>

            {{-- COLUMNA DERECHA (Info y Acervo) --}}
            <div class="lg:col-span-7 xl:col-span-8 space-y-6">
                
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
                        <flux:heading size="md">Acervo Digital</flux:heading>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        @forelse($agreement->documents as $doc)
                            <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50/50 dark:bg-zinc-800/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="p-2 bg-white dark:bg-zinc-900 rounded-lg shadow-sm shrink-0">
                                        <flux:icon name="document-text" class="{{ str_contains($doc->name, 'Expediente') ? 'text-green-500' : 'text-blue-500' }} size-6" />
                                    </div>
                                    <div class="flex flex-col flex-1 min-w-0">
                                        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-200 truncate">{{ $doc->name }}</span>
                                        <span class="text-xs text-zinc-500 mt-0.5">Subido el {{ $doc->created_at->format('d/m/Y') }} a las {{ $doc->created_at->format('H:i') }}</span>
                                    </div>
                                </div>
                                
                                <div class="shrink-0 ml-4">
                                    {{-- Aquí podrías poner tu enlace de descarga real --}}
                                    <flux:button icon="arrow-down-tray" size="sm" variant="outline" title="Descargar PDF" class="hidden sm:flex">Descargar</flux:button>
                                    <flux:button icon="arrow-down-tray" size="sm" variant="outline" class="sm:hidden" />
                                </div>
                            </div>
                        @empty
                            <div class="py-12 flex flex-col items-center justify-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl bg-zinc-50 dark:bg-zinc-800/50">
                                <flux:icon name="document-magnifying-glass" class="size-10 text-zinc-300 mb-3" />
                                <p class="text-sm font-medium text-zinc-500">No hay documentos digitales en el acervo.</p>
                                <p class="text-xs text-zinc-400 mt-1">El expediente se generará al finalizar la hoja de ruta.</p>
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts::app>