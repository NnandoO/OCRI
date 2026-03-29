<x-layouts::app :title="__('Nuevo Convenio')">
    <div class="p-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl" level="1">Registrar Nuevo Convenio</flux:heading>
                <flux:subheading>Gestión del ciclo de vida de alianzas estratégicas interinstitucionales.</flux:subheading>
            </div>
            <flux:button :href="route('agreements.index')" variant="ghost" icon="arrow-left" wire:navigate>
                Volver al Directorio
            </flux:button>
        </div>

        <flux:card class="p-0 overflow-hidden">
            <form action="{{ route('agreements.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="p-6 space-y-8">
                    
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 text-zinc-800 dark:text-white font-semibold border-b pb-2">
                            <flux:icon name="document-text" variant="outline" class="size-5" />
                            <span>Identificación del Documento</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <flux:field class="md:col-span-2">
                                <flux:label>Título del Convenio</flux:label>
                                <flux:input name="title" icon="pencil-square" placeholder="Ej: Convenio Marco de Cooperación entre UNCP y..." value="{{ old('title') }}" required />
                                <flux:error name="title" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Número de Resolución</flux:label>
                                <flux:input name="resolution_number" icon="hashtag" placeholder="R.C.U. N° 0123-2026" value="{{ old('resolution_number') }}" required />
                                <flux:error name="resolution_number" />
                            </flux:field>
                        </div>
                    </section>

                    <section class="space-y-4">
    <div class="flex items-center gap-2 text-zinc-800 dark:text-white font-semibold border-b pb-2">
        <flux:icon name="building-library" variant="outline" class="size-5" />
        <span>Institución y Clasificación</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <flux:field>
            <flux:label>Institución Aliada</flux:label>
            <flux:select name="institution_id" placeholder="Selecciona una institución..." required>
                @foreach($institutions as $institution)
                    <flux:select.option value="{{ $institution->id }}">
                        {{ $institution->name }} ({{ $institution->country }})
                    </flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="institution_id" />
        </flux:field>

        <flux:field>
            <flux:label>Tipo de Convenio</flux:label>
            <flux:select name="agreement_type_id" placeholder="Selecciona el tipo..." required>
                @foreach($types as $type)
                    <flux:select.option value="{{ $type->id }}">{{ $type->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="agreement_type_id" />
        </flux:field>
    </div>
</section>

                    <section class="space-y-4">
                        <div class="flex items-center gap-2 text-zinc-800 dark:text-white font-semibold border-b pb-2">
                            <flux:icon name="calendar-days" variant="outline" class="size-5" />
                            <span>Vigencia y Documentación Digital</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <flux:field>
                                <flux:label>Fecha de Inicio</flux:label>
                                <flux:input type="date" name="start_date" value="{{ old('start_date') }}" required />
                                <flux:error name="start_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Fecha de Vencimiento</flux:label>
                                <flux:input type="date" name="end_date" value="{{ old('end_date') }}" required />
                                <flux:error name="end_date" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Archivo PDF (Máx. 10MB)</flux:label>
                                <flux:input type="file" name="document" accept=".pdf" class="file:bg-zinc-100 dark:file:bg-zinc-700" />
                                <flux:error name="document" />
                            </flux:field>
                        </div>
                    </section>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 border-t flex justify-end gap-3">
                    <flux:button variant="ghost" :href="route('dashboard')" wire:navigate>Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" icon="document-check">
                        Registrar en OCRI
                    </flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>