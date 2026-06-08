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
                            <div class="flex items-start gap-2 mt-2">
                                <div class="flex-1">
                                    <flux:select name="institution_id" id="institution_select" searchable placeholder="Buscar institución..." required class="dark:bg-zinc-800/50">
                                        @foreach($institutions as $institution)
                                            <flux:select.option value="{{ $institution->id }}">{{ $institution->name }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                                <flux:modal.trigger name="create-institution-modal">
                                    <flux:button variant="primary" icon="plus" class="px-3" title="Registrar nueva institución" />
                                </flux:modal.trigger>
                            </div>
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

    {{-- MODAL DE CREACIÓN RÁPIDA DE INSTITUCIÓN --}}
    <flux:modal name="create-institution-modal" class="md:w-[500px]">
        <div class="mb-6">
            <flux:heading size="lg">Registrar Nueva Institución</flux:heading>
            <flux:subheading>Ingresa los datos básicos para añadirla al directorio.</flux:subheading>
        </div>

        <form id="quick-institution-form" class="space-y-4">
            <flux:field>
                <flux:label>Nombre de la Institución</flux:label>
                <flux:input id="new_inst_name" placeholder="Ej. Universidad Nacional de Ingeniería" required class="mt-1" />
            </flux:field>

            {{-- CAMBIO AQUÍ: Selector/Input de País Dinámico en el Modal --}}
            <flux:field x-data="{ nuevoPaisModal: false }">
                <div class="flex justify-between items-center mb-1">
                    <flux:label>País</flux:label>
                    <button type="button" x-on:click="nuevoPaisModal = !nuevoPaisModal" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline focus:outline-none">
                        <span x-show="!nuevoPaisModal">✍️ Escribir país nuevo</span>
                        <span x-show="nuevoPaisModal" x-cloak>📋 Seleccionar existente</span>
                    </button>
                </div>

                <div x-show="!nuevoPaisModal">
                    <flux:select id="new_inst_country_select" searchable placeholder="Buscar país..." class="mt-1">
                        <flux:select.option value="">-- Selecciona un país --</flux:select.option>
                        @foreach($countries as $country)
                            <flux:select.option value="{{ $country }}">{{ $country }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div x-show="nuevoPaisModal" x-cloak>
                    <flux:input 
                        id="new_inst_country_input" 
                        placeholder="Ej. Argentina" 
                        oninput="this.value = this.value.toUpperCase()"
                        class="mt-1 uppercase" 
                    />
                </div>
            </flux:field>

            <flux:field>
                <flux:label>Tipo de Institución</flux:label>
                <flux:select id="new_inst_type" placeholder="Selecciona un tipo" required class="mt-1">
                    <flux:select.option value="Universidad Nacional">Universidad Nacional</flux:select.option>
                    <flux:select.option value="Universidad Privada">Universidad Privada</flux:select.option>
                    <flux:select.option value="Entidad Gubernamental">Entidad Gubernamental</flux:select.option>
                    <flux:select.option value="Empresa Privada">Empresa Privada</flux:select.option>
                    <flux:select.option value="Organización Internacional">Organización Internacional</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="flex justify-end gap-2 pt-4">
                <flux:modal.close>
                    <flux:button variant="ghost">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" onclick="saveInstitution()" id="btn-save-inst">
                    Guardar y Seleccionar
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <script>
        // Lógica de fechas y PDF
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

        // Lógica para guardar la Institución por AJAX sin recargar la página
        async function saveInstitution() {
            const name = document.getElementById('new_inst_name').value;
            const type = document.getElementById('new_inst_type').value;
            const btnSave = document.getElementById('btn-save-inst');

            // Lógica para obtener el país dependiendo de qué input esté visible
            const selectCountry = document.getElementById('new_inst_country_select').value;
            const inputCountry = document.getElementById('new_inst_country_input').value;
            
            // Si el select tiene valor, lo usa; si no, asume que escribió uno nuevo.
            const country = selectCountry || inputCountry;

            if(!name || !country || !type) {
                alert('Por favor, completa todos los campos de la institución.');
                return;
            }

            btnSave.disabled = true;
            btnSave.innerText = 'Guardando...';

            try {
                const response = await fetch('/api/institutions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ name, country, type })
                });

                if(response.ok) {
                    const data = await response.json(); 
                    
                    // Agregar la nueva opción al select principal de instituciones
                    const select = document.getElementById('institution_select');
                    const newOption = new Option(data.name, data.id, true, true);
                    select.add(newOption);
                    
                    // Cerrar el modal y limpiar el formulario
                    document.getElementById('quick-institution-form').reset();
                    // Limpiar explícitamente el input de texto por si acaso
                    document.getElementById('new_inst_country_input').value = ''; 
                    Flux.modals.close('create-institution-modal');
                } else {
                    alert('Hubo un error al guardar la institución.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error de conexión al guardar.');
            } finally {
                btnSave.disabled = false;
                btnSave.innerText = 'Guardar y Seleccionar';
            }
        }
    </script>
</x-layouts::app>