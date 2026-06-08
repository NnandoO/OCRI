<x-layouts::app :title="__('Nueva Institución')">
    <div class="p-6 max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <flux:heading size="xl" level="1">Registrar Nueva Institución</flux:heading>
                <flux:subheading>Añade una nueva entidad aliada para los convenios de la UNCP.</flux:subheading>
            </div>
            <flux:button :href="route('institutions.index')" variant="ghost" icon="arrow-left" wire:navigate>
                Volver al Listado
            </flux:button>
        </div>

        <flux:card class="p-0 overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-sm">
            <form action="{{ route('institutions.store') }}" method="POST">
                @csrf

                <div class="p-6 space-y-8">
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 text-zinc-800 dark:text-white font-semibold mb-2">
                            <flux:icon name="building-library" variant="outline" class="size-5 text-blue-600 dark:text-blue-400" />
                            <span>Información General</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field class="md:col-span-2">
                                <flux:label class="font-bold">Nombre de la Institución</flux:label>
                                <flux:input name="name" placeholder="Ej: Universidad Nacional Mayor de San Marcos" required class="mt-2 dark:bg-zinc-800/50" />
                                <flux:error name="name" />
                            </flux:field>

                            {{-- CAMBIO AQUÍ: Campo de País Dinámico con buscador o escritura --}}
                            <flux:field x-data="{ nuevoPais: false }">
                                <div class="flex justify-between items-center mb-2">
                                    <flux:label class="font-bold">País</flux:label>
                                    <button type="button" x-on:click="nuevoPais = !nuevoPais" class="text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline focus:outline-none">
                                        <span x-show="!nuevoPais">✍️ Escribir país nuevo</span>
                                        <span x-show="nuevoPais" x-cloak>📋 Seleccionar existente</span>
                                    </button>
                                </div>

                                {{-- Opción A: Buscador de países existentes en la BD --}}
                                <div x-show="!nuevoPais">
                                    <flux:select x-bind:name="!nuevoPais ? 'country' : ''" searchable placeholder="Buscar país en el directorio..." class="dark:bg-zinc-800/50">
                                        <flux:select.option value="">-- Selecciona un país --</flux:select.option>
                                        @foreach($countries as $country)
                                            <flux:select.option value="{{ $country }}">{{ $country }}</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                {{-- Opción B: Entrada de texto libre para un país nuevo --}}
                                <div x-show="nuevoPais" x-cloak>
                                    <flux:input 
                                        x-bind:name="nuevoPais ? 'country' : ''" 
                                        placeholder="Ej: Argentina" 
                                        oninput="this.value = this.value.toUpperCase()"
                                        class="dark:bg-zinc-800/50 uppercase" 
                                    />
                                </div>
                                <flux:error name="country" />
                            </flux:field>

                            <flux:field>
                                <flux:label class="font-bold mb-2 block">Tipo de Institución</flux:label>
                                <flux:select name="type" placeholder="Selecciona el tipo..." class="dark:bg-zinc-800/50">
                                    <flux:select.option value="Universidad Nacional">Universidad Nacional</flux:select.option>
                                    <flux:select.option value="Universidad Internacional">Universidad Internacional</flux:select.option>
                                    <flux:select.option value="Empresa Nacional">Empresa Nacional</flux:select.option>
                                    <flux:select.option value="Empresa Internacional">Empresa Internacional</flux:select.option>
                                    <flux:select.option value="Municipalidad">Municipalidad</flux:select.option>
                                    <flux:select.option value="Salud">Salud</flux:select.option>
                                    <flux:select.option value="Otros">Otros</flux:select.option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>
                        </div>
                    </section>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 border-t border-zinc-100 dark:border-zinc-700/50 flex justify-end gap-3">
                    <flux:button variant="ghost" :href="route('institutions.index')" wire:navigate>Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" class="px-6 shadow-md shadow-blue-500/10">Guardar Institución</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>