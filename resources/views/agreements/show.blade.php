<x-layouts::app :title="'Expediente: ' . $agreement->title">
    <div class="p-6 max-w-6xl mx-auto space-y-6">
        
        @php
            // Lógica de control para la Hoja de Ruta
            $hasRoadmap = $agreement->roadmapItems && $agreement->roadmapItems->count() > 0;
            $pendingCount = $hasRoadmap ? $agreement->roadmapItems->where('is_completed', false)->count() : 0;
            $allOpinionsReady = ($hasRoadmap && $pendingCount === 0);
            $notYetVigente = ($agreement->status !== 'Vigente');
        @endphp

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-zinc-800 p-6 rounded-xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
            <div>
                <flux:heading size="xl">{{ $agreement->title }}</flux:heading>
                <div class="flex items-center gap-2 mt-1">
                    <flux:icon name="building-library" class="size-4 text-zinc-400" />
                    <flux:text variant="subheading" class="font-medium">{{ $agreement->institution->name }}</flux:text>
                </div>
            </div>
            <div class="flex flex-col items-end">
                <flux:badge size="lg" :color="$agreement->status === 'Vigente' ? 'green' : 'zinc'" variant="subtle">
                    {{ $agreement->status }}
                </flux:badge>
                <flux:text size="xs" class="mt-1">Registrado el {{ $agreement->created_at->format('d/m/Y') }}</flux:text>
            </div>
        </div>

        @if($allOpinionsReady && $notYetVigente)
            <flux:card class="border-2 border-green-500 bg-green-50 dark:bg-green-900/10 shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <flux:icon name="check-circle" variant="solid" class="text-green-500 size-6" />
                    <flux:heading size="lg" class="text-green-800 dark:text-green-400">¡Opiniones Técnicas Finalizadas!</flux:heading>
                </div>
                
                <flux:subheading class="mb-6">Se han recibido todas las respuestas. Complete los datos de la <b>Resolución Rectoral</b> para oficializar la vigencia.</flux:subheading>

                <form action="{{ route('agreements.activate', $agreement->id) }}" method="POST" class="space-y-4">
                    @csrf 
                    @method('PATCH')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:field>
                            <flux:label>Número de Resolución Rectoral</flux:label>
                            <flux:input name="resolution_number" icon="hashtag" placeholder="R.R. N° 0123-2026-UNCP" required />
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

<flux:button type="submit" variant="primary" class="w-full" icon="paper-airplane">
    Activar Convenio en el Sistema
</flux:button>
                </form>
            </flux:card>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="lg:col-span-1 space-y-6">
                
                @if(!$hasRoadmap)
                    {{-- Caso: No hay Hoja de Ruta configurada --}}
                    <flux:card>
                        <flux:heading size="md" class="mb-4">Configurar Hoja de Ruta</flux:heading>
                        <flux:text size="sm" class="mb-4">Seleccione las áreas para seguimiento de opiniones técnicas:</flux:text>
                        
                        <form action="{{ route('agreements.roadmap.store', $agreement->id) }}" method="POST" class="space-y-4">
                            @csrf
                            <div class="space-y-2">
                                @php $defaultAreas = ['Vicerrectorado de Investigación', 'Vicerrectorado Académico', 'Asesoría Legal', 'CEPRE', 'Rectorado', 'CEID']; @endphp
                                @foreach($defaultAreas as $area)
                                    <label class="flex items-center gap-3 p-2 bg-zinc-50 dark:bg-zinc-800 border rounded cursor-pointer hover:border-zinc-400">
                                        <input type="checkbox" name="areas[]" value="{{ $area }}" class="rounded text-blue-600">
                                        <span class="text-sm">{{ $area }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <flux:field>
                                <flux:label>Otras áreas (comas)</flux:label>
                                <flux:input name="extra_areas" placeholder="Ej: Facultad de Sistemas..." />
                            </flux:field>
                            <flux:button type="submit" variant="primary" class="w-full" size="sm">Crear Hoja de Ruta</flux:button>
                        </form>
                    </flux:card>
                @else
                    {{-- Caso: Hoja de Ruta Activa --}}
                    <flux:card>
                        <flux:heading size="md" class="mb-6 italic">Hoja de Ruta de Opiniones</flux:heading>
                        
                        <div class="space-y-4">
                            @foreach($agreement->roadmapItems as $item)
                                <div class="flex items-center gap-3 p-3 {{ $item->is_completed ? 'bg-green-50/50 dark:bg-green-900/10' : 'bg-zinc-50 dark:bg-zinc-800' }} border rounded-lg">
                                    <form action="{{ route('agreements.roadmap.check', $item->id) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="size-6 flex items-center justify-center rounded border {{ $item->is_completed ? 'bg-green-500 border-green-500' : 'border-zinc-400' }}">
                                            @if($item->is_completed) <flux:icon name="check" class="size-4 text-white" /> @endif
                                        </button>
                                    </form>
                                    <div class="flex-1">
                                        <flux:text size="sm" class="{{ $item->is_completed ? 'line-through text-zinc-400' : 'font-semibold' }}">
                                            {{ $item->area_name }}
                                        </flux:text>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </flux:card>
                @endif
            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <flux:card>
                    <flux:heading size="md" class="mb-4">Información del Registro</flux:heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                        <div>
                            <flux:text size="xs" class="uppercase text-zinc-400 font-bold">Nombre Oficial</flux:text>
                            <flux:text size="sm" class="italic">"{{ $agreement->name }}"</flux:text>
                        </div>
                        <div>
                            <flux:text size="xs" class="uppercase text-zinc-400 font-bold">Tipo de Alianza</flux:text>
                            <flux:text size="sm">{{ $agreement->type->name ?? 'No especificado' }}</flux:text>
                        </div>
                        <div>
                            <flux:text size="xs" class="uppercase text-zinc-400 font-bold">Resolución</flux:text>
                            <flux:text size="sm">{{ $agreement->resolution_number ?? 'En Trámite' }}</flux:text>
                        </div>
                        <div>
                            <flux:text size="xs" class="uppercase text-zinc-400 font-bold">País de la IES</flux:text>
                            <flux:text size="sm">{{ $agreement->institution->country }}</flux:text>
                        </div>
                    </div>
                </flux:card>

                <flux:card>
                    <div class="flex justify-between items-center mb-4">
                        <flux:heading size="md">Acervo Digital</flux:heading>
                        <flux:button variant="ghost" size="sm" icon="plus">Añadir Archivo</flux:button>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-2">
                        @forelse($agreement->documents as $doc)
                            <div class="flex items-center justify-between p-3 border rounded-lg bg-zinc-50 dark:bg-zinc-800">
                                <div class="flex items-center gap-3">
                                    <flux:icon name="document-text" class="text-blue-500" />
                                    <flux:text size="sm" class="font-medium">{{ $doc->name }}</flux:text>
                                </div>
                                <flux:button icon="arrow-down-tray" size="sm" variant="ghost" />
                            </div>
                        @empty
                            <div class="py-8 text-center border-2 border-dashed rounded-lg">
                                <flux:text size="xs" class="text-zinc-400 italic">No hay documentos digitales asociados todavía.</flux:text>
                            </div>
                        @endforelse
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts::app>