<x-layouts::app title="Registrar Nuevo Convenio">
    {{-- Variables de Alpine.js para controlar la previsualización del PDF en vivo --}}
    <div class="p-6 max-w-full mx-auto space-y-6" x-data="{ pdfPreview: null, hasFile: false }">
        
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
                        <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">N° de Convenio</flux:label>
                        <flux:input 
                            name="resolution_number" 
                            placeholder="R.R. N° 001-2026" 
                            value="{{ old('resolution_number', $nextResolutionNumber ?? '') }}" 
                            required
                            oninput="this.value = this.value.toUpperCase()"
                            class="dark:bg-zinc-800/50 uppercase"
                        />
                        <flux:error name="resolution_number" />
                    </flux:field>

                    <div class="lg:col-span-3 grid grid-cols-1 gap-6">
                        <flux:field>
                            <flux:label class="font-bold mb-2 block text-zinc-700 dark:text-zinc-300">Nombre Oficial del Convenio</flux:label>
                            <flux:textarea 
                                name="name" 
                                placeholder="NOMBRE COMPLETO SEGÚN RESOLUCIÓN..." 
                                rows="2" 
                                required
                                oninput="this.value = this.value.toUpperCase()"
                                class="dark:bg-zinc-800/50 resize-none uppercase"
                            >{{ old('name') }}</flux:textarea>
                            <flux:error name="name" />
                        </flux:field>

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
                    </div>
                </div>
            </flux:card>

            {{-- Grid a 2 columnas para Categorización y Archivo --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {{-- Bloque 2: Categorización --}}
                <flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6 h-fit">
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

                {{-- Bloque 3: Archivo y Visor (Dinámico) --}}
                <flux:card class="bg-white dark:bg-zinc-800 shadow-sm border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center gap-2 mb-6 border-b border-zinc-100 dark:border-zinc-700 pb-3">
                        <flux:icon name="paper-clip" class="size-5 text-zinc-400" />
                        <flux:heading size="lg">Acervo y Vigencia (Opcional)</flux:heading>
                    </div>
                    
                    <div class="space-y-6">

                        {{-- NUEVO: Campo para subir Dictamen / Documento Original --}}
                        <flux:field class="space-y-2">
                            <flux:label class="font-bold text-amber-600 dark:text-amber-400">Dictamen / Documento Original (Opcional)</flux:label>
                            <flux:text size="xs" class="text-zinc-500 -mt-1">Documento que sustenta la solicitud de dictamen de rectorado o la suscripcion del convenio.</flux:text>
                            <flux:input 
                                type="file" 
                                name="dictamen" 
                                accept=".pdf" 
                                class="cursor-pointer dark:bg-zinc-800/50"
                            />
                            <flux:error name="dictamen" />
                        </flux:field>

                        <hr class="border-zinc-200 dark:border-zinc-700">

                        <flux:field class="space-y-2">
                            <flux:label class="font-bold text-blue-600 dark:text-blue-400">Adjuntar Convenio Firmado (PDF)</flux:label>
                            {{-- Lógica de Alpine: Al subir el archivo, genera una URL temporal para el visor --}}
                            <flux:input 
                                type="file" 
                                name="document" 
                                accept=".pdf" 
                                class="cursor-pointer dark:bg-zinc-800/50"
                                x-on:change="
                                    const file = $event.target.files[0];
                                    hasFile = !!file;
                                    if(file && file.type === 'application/pdf') {
                                        pdfPreview = URL.createObjectURL(file);
                                    } else {
                                        pdfPreview = null;
                                    }
                                " 
                            />
                        </flux:field>

                        {{-- Estos campos solo aparecen si se seleccionó un PDF --}}
                        <div class="grid grid-cols-2 gap-4" x-show="hasFile" x-transition x-cloak>
                            <flux:field class="space-y-2">
                                <flux:label class="font-bold">Fecha Inicio</flux:label>
                                <flux:input type="date" name="start_date" class="dark:bg-zinc-800/50" x-bind:required="hasFile" />
                            </flux:field>
                            <flux:field class="space-y-2">
                                <flux:label class="font-bold">Fecha Fin</flux:label>
                                <flux:input type="date" name="end_date" class="dark:bg-zinc-800/50" x-bind:required="hasFile" />
                            </flux:field>
                        </div>

                        {{-- Visor de PDF Integrado --}}
                        <template x-if="pdfPreview">
                            <div class="w-full h-[400px] mt-4 bg-zinc-100 dark:bg-zinc-900 rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
                                <iframe :src="pdfPreview" class="w-full h-full border-0" allow="autoplay"></iframe>
                            </div>
                        </template>
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
                <flux:input id="new_inst_name" placeholder="Ej. Universidad Nacional de Ingeniería" required class="mt-1" oninput="this.value = this.value.toUpperCase()" />
            </flux:field>

            <flux:field class="space-y-1" x-data="{ nuevoPaisModal: false }">
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
                    <flux:button variant="ghost" id="btn-close-institution-modal">Cancelar</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary" onclick="saveInstitution()" id="btn-save-inst">
                    Guardar y Seleccionar
                </flux:button>
            </div>
        </form>
    </flux:modal>

    <script>
        async function saveInstitution() {
            const name = document.getElementById('new_inst_name').value;
            const type = document.getElementById('new_inst_type').value;
            const btnSave = document.getElementById('btn-save-inst');

            const selectCountry = document.getElementById('new_inst_country_select').value;
            const inputCountry = document.getElementById('new_inst_country_input').value;
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
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ name, country, type })
                });

                if (!response.ok) {
                    const errorTexto = await response.text();
                    console.error(errorTexto);
                    alert('Error en el servidor. Revisa la consola de inspección (F12) para ver el detalle.');
                    return;
                }

                const data = await response.json();
                
                const select = document.getElementById('institution_select');
                const newOption = new Option(data.name, data.id, true, true);
                select.add(newOption);
                
                document.getElementById('quick-institution-form').reset();
                document.getElementById('new_inst_country_input').value = ''; 
                
                const closeBtn = document.getElementById('btn-close-institution-modal');
                if (closeBtn) {
                    closeBtn.click();
                }

            } catch (error) {
                console.error('Error crítico:', error);
                alert('Ocurrió un fallo al procesar la respuesta del servidor: ' + error.message);
            } finally {
                btnSave.disabled = false;
                btnSave.innerText = 'Guardar y Seleccionar';
            }
        }
    </script>
</x-layouts::app>