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

        {{-- Importante: method="POST" con @method('PUT') para Laravel --}}
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
                        <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Nombre Oficial del Convenio</flux:label>
                        <flux:textarea 
                            name="name" 
                            placeholder="Nombre completo según resolución..." 
                            rows="3" 
                            required
                            class="dark:bg-zinc-800/50 resize-none"
                        >{{ old('name', $agreement->name) }}</flux:textarea>
                        <flux:error name="name" />
                    </flux:field>

                    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Título Corto --}}
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Título Corto / Referencia</flux:label>
                            <flux:input name="title" placeholder="Ej: UNCP - ESSALUD" value="{{ old('title', $agreement->title) }}" required class="dark:bg-zinc-800/50">
                            </flux:input>
                            <flux:error name="title" />
                        </flux:field>

                        {{-- N° de Resolución --}}
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">N° de Resolución Rectoral</flux:label>
                            <flux:input name="resolution_number" placeholder="R.R. N° 001-2026" value="{{ old('resolution_number', $agreement->resolution_number) }}" class="dark:bg-zinc-800/50">
                            </flux:input>
                            <flux:error name="resolution_number" />
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
                            <flux:select name="institution_id" searchable placeholder="Buscar institución..." required class="dark:bg-zinc-800/50">
                                @foreach($institutions as $institution)
                                    <flux:select.option 
                                        value="{{ $institution->id }}" 
                                        :selected="old('institution_id', $agreement->institution_id) == $institution->id"
                                    >
                                        {{ $institution->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
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
                            <flux:label class="font-bold text-blue-600 dark:text-blue-400">Actualizar Acervo Digital (PDF)</flux:label>
                            <flux:input type="file" name="document" id="doc_file" accept=".pdf" class="cursor-pointer dark:bg-zinc-800/50" />
                            @if($agreement->document_path)
                                <flux:text size="xs" class="mt-1">Archivo actual: <a href="{{ Storage::url($agreement->document_path) }}" target="_blank" class="text-blue-500 underline">Ver PDF</a></flux:text>
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

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700 mt-6">
                <flux:button :href="route('agreements.index')" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check-badge" class="px-8 py-3 shadow-lg shadow-blue-500/20">
                    Guardar Cambios
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>