<x-layouts::app title="Registrar Nuevo Convenio">
    <div class="p-6 max-w-full mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <flux:heading size="xl" level="1" class="text-zinc-800 dark:text-white">Nuevo Convenio Institucional</flux:heading>
            <flux:button :href="route('agreements.index')" variant="ghost" icon="arrow-left" wire:navigate>
                Volver al Directorio
            </flux:button>
        </div>

        <form action="{{ route('agreements.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Bloque 1: Identificación --}}
            <flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6">
                <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-700 pb-3">
                    <flux:icon name="document-text" class="size-5 text-zinc-400" />
                    <flux:heading size="lg">Identificación del Documento</flux:heading>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-6">
                    <flux:field class="lg:col-span-3">
                        <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Nombre Oficial del Convenio</flux:label>
                        {{-- Transformación a mayúsculas aplicada --}}
                        <flux:textarea 
                            name="name" 
                            placeholder="NOMBRE COMPLETO SEGÚN RESOLUCIÓN..." 
                            rows="3" 
                            required
                            oninput="this.value = this.value.toUpperCase()"
                            class="dark:bg-zinc-800/50 resize-none mt-2 uppercase"
                        >{{ old('name') }}</flux:textarea>
                        <flux:error name="name" />
                    </flux:field>

                    <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Título Corto / Referencia</flux:label>
                            <flux:input 
                                name="title" 
                                placeholder="EJ: UNCP - ESSALUD" 
                                value="{{ old('title') }}" 
                                required 
                                oninput="this.value = this.value.toUpperCase()"
                                class="dark:bg-zinc-800/50 uppercase"
                            />
                            <flux:error name="title" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">N° de Resolución Rectoral</flux:label>
                            <flux:input 
                                name="resolution_number" 
                                placeholder="R.R. N° 001-2026" 
                                value="{{ old('resolution_number') }}" 
                                oninput="this.value = this.value.toUpperCase()"
                                class="dark:bg-zinc-800/50 uppercase"
                            />
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
                            <flux:select name="institution_id" searchable placeholder="Buscar institución..." required class="mt-2 dark:bg-zinc-800/50">
                                @foreach($institutions as $institution)
                                    <flux:select.option value="{{ $institution->id }}">{{ $institution->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>
                        <flux:field class="space-y-2">
                            <flux:label class="font-bold">Tipo de Convenio</flux:label>
                            <flux:select name="agreement_type_id" required class="mt-2 dark:bg-zinc-800/50">
                                @foreach($types as $type)
                                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
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
                            <flux:label class="font-bold text-blue-600 dark:text-blue-400">Acervo Digital (PDF)</flux:label>
                            <flux:input type="file" name="document" id="doc_file" accept=".pdf" class="mt-2 cursor-pointer dark:bg-zinc-800/50" />
                        </flux:field>
                        <div class="grid grid-cols-2 gap-4">
                            <flux:field class="space-y-2">
                                <flux:label class="font-bold">Fecha Inicio</flux:label>
                                <flux:input type="date" name="start_date" id="s_date" disabled class="mt-2 dark:bg-zinc-800/50" />
                            </flux:field>
                            <flux:field class="space-y-2">
                                <flux:label class="font-bold">Fecha Fin</flux:label>
                                <flux:input type="date" name="end_date" id="e_date" disabled class="mt-2 dark:bg-zinc-800/50" />
                            </flux:field>
                        </div>
                    </div>
                </flux:card>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700 mt-6">
                <flux:button :href="route('agreements.index')" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="check-badge" class="px-8 py-3 shadow-lg shadow-blue-500/20">
                    Registrar en el Sistema
                </flux:button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const docFile = document.getElementById('doc_file');
            const sDate = document.getElementById('s_date');
            const eDate = document.getElementById('e_date');
            if(docFile) {
                docFile.addEventListener('change', function() {
                    const hasFile = this.files.length > 0;
                    sDate.disabled = !hasFile;
                    eDate.disabled = !hasFile;
                    sDate.required = hasFile;
                    eDate.required = hasFile;
                    if(!hasFile) { sDate.value = ''; eDate.value = ''; }
                });
            }
        });
    </script>
</x-layouts::app>