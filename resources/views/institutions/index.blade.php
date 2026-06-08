<x-layouts::app :title="__('Aliados e Instituciones')">
    {{-- Fondo más claro: En modo oscuro usamos un gris grafito en lugar de negro --}}
    <div class="min-h-screen bg-zinc-200 dark:bg-zinc-600 p-6 md:p-8 transition-colors duration-300">
        <div class="max-w-7xl mx-auto space-y-10">
            
{{-- Header con Estilo Limpio --}}
<div class="flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-zinc-200 dark:border-zinc-800 pb-8">
    <div class="space-y-1">
        <flux:heading size="xl" level="1" class="font-extrabold tracking-tight text-zinc-900 dark:text-zinc-100">
            Aliados e Instituciones
        </flux:heading>
        <div class="flex items-center gap-2 text-zinc-500">
            <flux:icon name="globe-alt" class="size-4" />
            <flux:text size="sm" class="font-medium">Red de Cooperación Interinstitucional • UNCP</flux:text>
        </div>
    </div>
    
    {{-- Formulario de Búsqueda --}}
    <form action="{{ route('institutions.index') }}" method="GET" class="flex items-center gap-3">
        <flux:input 
            name="search" 
            value="{{ request('search') }}" 
            icon="magnifying-glass" 
            placeholder="Nombre o país..." 
            class="w-48 sm:w-64" 
            clearable 
        />
        <flux:button :href="route('institutions.create')" variant="primary" icon="plus" class="shadow-md" wire:navigate>
            Nueva Institución
        </flux:button>
    </form>
</div>

            {{-- Grid de Tarjetas --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($institutions as $institution)
                    {{-- Tarjeta: Blanca en Light, Gris Carbón en Dark (no negra) --}}
                    <flux:card class="group p-0 overflow-hidden flex flex-col bg-white dark:bg-zinc-800 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] dark:shadow-none border-zinc-200 dark:border-zinc-700 rounded-2xl hover:border-blue-500 dark:hover:border-blue-500 transition-all duration-300">
                        
                        {{-- Top Bar sutil --}}
                        <div class="px-6 py-3 bg-zinc-50 dark:bg-zinc-700/50 border-b border-zinc-100 dark:border-zinc-700 flex justify-between items-center">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-zinc-400">ID #{{ $institution->id }}</span>
                            <flux:badge size="sm" color="blue" variant="subtle" class="text-[9px] font-bold uppercase">Entidad</flux:badge>
                        </div>

                        <div class="p-6 flex-1 space-y-5">
                            <div>
                                <flux:heading size="lg" class="font-bold text-zinc-800 dark:text-zinc-100 leading-snug">
                                    {{ $institution->name }}
                                </flux:heading>
                                <div class="flex items-center gap-2 mt-2 text-zinc-500 dark:text-zinc-400">
                                    <flux:icon name="map-pin" class="size-4 text-blue-500" />
                                    <span class="text-xs font-medium uppercase tracking-wider">{{ $institution->country }}</span>
                                </div>
                            </div>

                            {{-- Información de tipo y conteo --}}
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-300 text-[10px] font-bold rounded-md border border-zinc-200 dark:border-zinc-600">
                                    {{ strtoupper($institution->type) }}
                                </span>
                                <span class="px-2 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-[10px] font-bold rounded-md border border-blue-100 dark:border-blue-800">
                                    {{ $institution->agreements_count ?? 0 }} CONVENIOS
                                </span>
                            </div>
                        </div>

                        {{-- Footer con botones claros --}}
                        <div class="px-6 py-4 bg-zinc-50 dark:bg-zinc-800/80 border-t border-zinc-100 dark:border-zinc-700 flex justify-between items-center">
                            <flux:button size="sm" variant="ghost" class="text-xs font-bold" :href="route('institutions.show', $institution)" wire:navigate>
                                Ver Detalles
                            </flux:button>
                            
                            <flux:dropdown>
                                <flux:button variant="ghost" icon="ellipsis-horizontal" size="sm" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil-square" :href="route('institutions.edit', $institution)" wire:navigate>Editar</flux:menu.item>
                                    <flux:menu.separator />
                                    <form action="{{ route('institutions.destroy', $institution) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <flux:menu.item as="button" variant="danger" icon="trash">Eliminar</flux:menu.item>
                                    </form>
                                </flux:menu>
                            </flux:dropdown>
                        </div>
                    </flux:card>
                @endforeach

                {{-- Tarjeta de Registro con luz --}}
                <a href="{{ route('institutions.create') }}" wire:navigate class="border-2 border-dashed border-zinc-300 dark:border-zinc-700 rounded-2xl p-10 flex flex-col items-center justify-center group hover:bg-white dark:hover:bg-zinc-800 hover:border-blue-500 transition-all duration-300">
                    <div class="p-4 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-400 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300">
                        <flux:icon name="plus" class="size-6" />
                    </div>
                    <span class="mt-4 font-bold text-[11px] uppercase tracking-widest text-zinc-400 group-hover:text-blue-600">
                        Añadir Institución
                    </span>
                </a>
            </div>

            <div class="mt-10">
                {{ $institutions->links() }}
            </div>
        </div>
    </div>
</x-layouts::app>