<x-layouts::app title="Editar Convenio: {{ $agreement->title }}">
    {{-- CSS FIX: Forzamos el centrado de los iconos de Flux --}}
    <style>
        [data-flux-input] [data-flux-input-icon] {
            top: 0 !important;
            bottom: 0 !important;
            height: 100% !important;
            display: flex !important;
            align-items: center !important;
            transform: none !important; 
        }
        [data-flux-input-icon] svg {
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>

    <div class="p-6 max-w-full mx-auto space-y-6">
        {{-- Encabezado --}}
        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl" level="1" class="text-zinc-800 dark:text-white">Editar Convenio</flux:heading>
                <flux:subheading>Modificando el registro: <span class="font-bold text-blue-600">{{ $agreement->title }}</span></flux:subheading>
            </div>
            <flux:button :href="route('agreements.index')" variant="ghost" icon="arrow-left" wire:navigate>
                Cancelar y Volver
            </flux:button>
        </div>

        {{-- Formulario Principal de Actualización --}}
        <form action="{{ route('agreements.update', $agreement) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Bloque 1: Identificación --}}
            <flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <flux:icon name="document-text" class="size-5 text-zinc-400" />
                    <flux:heading size="lg">Identificación del Documento</flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                    <flux:field class="lg:col-span-3">
                        <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">N° de Convenio</flux:label>
                        <flux:input 
                            name="resolution_number" 
                            placeholder="R.R. N° 001-2026" 
                            value="{{ old('resolution_number', $agreement->resolution_number) }}" 
                            required
                            oninput="this.value = this.value.toUpperCase()"
                            class="dark:bg-zinc-800/50 uppercase"
                        />
                        <flux:error name="resolution_number" />
                    </flux:field>

                    <div class="lg:col-span-3 grid grid-cols-1 gap-6">
                        {{-- Nombre Oficial --}}
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Nombre Oficial del Convenio</flux:label>
                            <flux:textarea 
                                name="name" 
                                placeholder="NOMBRE COMPLETO SEGÚN RESOLUCIÓN..." 
                                rows="2" 
                                required
                                oninput="this.value = this.value.toUpperCase()"
                                class="dark:bg-zinc-800/50 resize-none uppercase"
                            >{{ old('name', $agreement->name) }}</flux:textarea>
                            <flux:error name="name" />
                        </flux:field>

                        {{-- Título Corto --}}
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Título Corto / Referencia</flux:label>
                            <flux:input name="title" placeholder="EJ: UNCP - ESSALUD" value="{{ old('title', $agreement->title) }}" required class="dark:bg-zinc-800/50 uppercase"
                                oninput="this.value = this.value.toUpperCase()">
                            </flux:input>
                            <flux:error name="title" />
                        </flux:field>
                    </div>
                </div>
            </flux:card>

            {{-- Bloque 2: Clasificación y Vigencia --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Columna: Clasificación --}}
                <flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-700 pb-3">
                        <flux:icon name="tag" class="size-5 text-zinc-400" />
                        <flux:heading size="lg">Categorización</flux:heading>
                    </div>
                    <div class="space-y-6">
                        <flux:field class="space-y-2">
                            <flux:label class="font-bold">Institución Aliada</flux:label>
                            @include('agreements.partials.institution-combobox', [
                                'institutions' => $institutions,
                                'selectedInstitutionId' => old('institution_id', $agreement->institution_id),
                                'selectedInstitutionName' => old('institution_id')
                                    ? (optional($institutions->firstWhere('id', (int) old('institution_id')))->name ?? '')
                                    : ($agreement->institution->name ?? ''),
                            ])
                        </flux:field>
                        <flux:field class="space-y-2">
                            <flux:label class="font-bold">Tipo de Convenio</flux:label>
                            <flux:select name="agreement_type_id" required class="dark:bg-zinc-800/50">
                                @foreach($types as $type)
                                    <flux:select.option 
                                        value="{{ $type->id }}" 
                                        :selected="old('agreement_type_id', $agreement->agreement_type_id) == $type->id"
                                    >
                                        {{ $type->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                    </div>
                </flux:card>

                {{-- Columna: Vigencia --}}
<flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6">
    <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-700 pb-3">
        <flux:icon name="calendar" class="size-5 text-zinc-400" />
        <flux:heading size="lg">Vigencia y Archivo</flux:heading>
    </div>
    <div class="space-y-6">
        <flux:field class="space-y-2">
            <flux:label class="font-bold text-blue-600 dark:text-blue-400">Subir un nuevo documento (PDF)</flux:label>
            <flux:input type="file" name="document" id="doc_file" accept=".pdf" class="cursor-pointer dark:bg-zinc-800/50" />
            
            {{-- Sistema de acervo digital con BOTÓN DE ELIMINAR CORREGIDO --}}
            @if($agreement->documents->count() > 0)
                <div class="mt-4 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                    <flux:text size="sm" class="font-semibold text-zinc-700 dark:text-zinc-300 mb-2">Archivos guardados actualmente:</flux:text>
                    <div class="space-y-2">
                        @foreach($agreement->documents as $doc)
                            <div class="flex items-center justify-between gap-2 text-sm bg-zinc-50 dark:bg-zinc-800/80 p-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                <div class="flex items-center gap-2 min-w-0">
                                    <flux:icon name="document-text" class="size-4 text-blue-500 shrink-0" />
                                    <span class="truncate font-medium text-zinc-700 dark:text-zinc-300" title="{{ $doc->name }}">{{ $doc->name }}</span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 px-2">
                                    <a href="{{ asset('storage/' . str_replace('\\', '/', $doc->file_path)) }}" target="_blank" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 font-bold" title="Ver PDF">
                                        Ver PDF
                                    </a>
                                    {{-- AQUÍ ESTÁ LA CORRECCIÓN: window.document.getElementById --}}
                                    <button type="button" onclick="if(confirm('¿Estás seguro de eliminar este archivo?')) { window.document.getElementById('delete-doc-{{ $doc->id }}').submit(); }" class="text-red-500 hover:text-red-700 transition-colors focus:outline-none" title="Eliminar archivo">
                                        <flux:icon name="trash" class="size-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                
                {{-- Formularios ocultos para eliminar documentos --}}
                @foreach($agreement->documents as $doc)
                    <form id="delete-doc-{{ $doc->id }}" action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endforeach
            @else
                <flux:text size="xs" class="mt-1 text-zinc-500">No hay ningún archivo principal en el acervo digital.</flux:text>
            @endif
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field class="space-y-2">
                <flux:label class="font-bold">Fecha Inicio</flux:label>
                <flux:input 
                    type="date" 
                    name="start_date" 
                    id="s_date" 
                    value="{{ old('start_date', $agreement->start_date?->format('Y-m-d')) }}" 
                    class="dark:bg-zinc-800/50" 
                />
            </flux:field>
            <flux:field class="space-y-2">
                <flux:label class="font-bold">Fecha Fin</flux:label>
                <flux:input 
                    type="date" 
                    name="end_date" 
                    id="e_date" 
                    value="{{ old('end_date', $agreement->end_date?->format('Y-m-d')) }}" 
                    class="dark:bg-zinc-800/50" 
                />
            </flux:field>
        </div>
    </div>
</flux:card>
            </div>

            {{-- Fila inferior redistribuida con justify-between --}}
            <div class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-700 mt-6">
                
                {{-- Botón de Eliminar (Izquierda - Alerta Destructiva) --}}
                <flux:button 
    type="button" 
    variant="danger" 
    icon="trash" 
    onclick="if(confirm('¿Estás completamente seguro de eliminar este convenio permanentemente? Esta acción es irreversible y borrará todo su historial.')) { window.document.getElementById('delete-agreement-form').submit(); }">
    Eliminar Convenio
</flux:button>

                {{-- Botones de Control del Formulario (Derecha) --}}
                <div class="flex gap-4">
                    <flux:button :href="route('agreements.index')" variant="ghost">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" icon="check-badge" class="px-8 py-3 shadow-lg shadow-blue-500/20">
                        Guardar Cambios
                    </flux:button>
                </div>
            </div>
        </form>

        {{-- FORMULARIO DE ELIMINACIÓN OCULTO (Afuera para evitar anidación HTML) --}}
        <form id="delete-agreement-form" action="{{ route('agreements.destroy', $agreement) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
</x-layouts::app>