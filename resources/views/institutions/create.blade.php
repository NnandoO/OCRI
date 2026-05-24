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

        <flux:card class="p-0 overflow-hidden">
            <form action="{{ route('institutions.store') }}" method="POST">
                @csrf

                <div class="p-6 space-y-8 bg-zinc-50 dark:bg-zinc-700">
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 text-zinc-800 dark:text-white font-semibold">
                            <flux:icon name="building-library" variant="outline" class="size-5" />
                            <span>Información General</span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <flux:field class="md:col-span-2">
                                <flux:label>Nombre de la Institución</flux:label>
                                <flux:input name="name" placeholder="Ej: Universidad Nacional Mayor de San Marcos" required />
                                <flux:error name="name" />
                            </flux:field>

                            <flux:field>
                                <flux:label>País</flux:label>
                                <flux:input name="country" placeholder="Ej: Perú" required />
                                <flux:error name="country" />
                            </flux:field>

                            <flux:field>
                                <flux:label>Tipo de Institución</flux:label>
                                <flux:select name="type" placeholder="Selecciona el tipo...">
                                    <flux:select.option value="Universidad Nacional">Universidad Nacional</flux:select.option>
                                    <flux:select.option value="Universidad Internacional">Universidad Internacional</flux:select.option>
                                    <flux:select.option value="Empresa Nacional">Empresa Nacional</flux:select.option>
                                    <flux:select.option value="mpresa Internacional">Empresa Internacional</flux:select.option>
                                    <flux:select.option value="Municipalidad">Municipalidad</flux:select.option>
                                    <flux:select.option value="Salud">Salud</flux:select.option>
                                    <flux:select.option value="Otros">Otros</flux:select.option>
                                </flux:select>
                                <flux:error name="type" />
                            </flux:field>
                        </div>
                    </section>
                </div>

                <div class="bg-zinc-50 dark:bg-zinc-900/50 p-4 border-t flex justify-end gap-3">
                    <flux:button variant="ghost" :href="route('institutions.index')" wire:navigate>Cancelar</flux:button>
                    <flux:button type="submit" variant="primary">Guardar Institución</flux:button>
                </div>
            </form>
        </flux:card>
    </div>
</x-layouts::app>