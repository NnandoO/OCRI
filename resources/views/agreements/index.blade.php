<x-layouts::app :title="__('Directorio de Convenios')">
    <div class="p-6 max-w-7xl mx-auto space-y-8">
        
        {{-- Header --}}
{{-- Header --}}
<div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
    <div class="space-y-1">
        <flux:heading size="xl" level="1" class="font-black tracking-tight text-zinc-900 dark:text-white">Directorio de Convenios</flux:heading>
        <div class="flex items-center gap-2 text-zinc-500 dark:text-zinc-400">
            <flux:icon name="building-library" variant="outline" class="size-4" />
            <flux:text size="sm" class="font-medium text-zinc-600 dark:text-zinc-400">Oficina de Cooperación y Relaciones Internacionales • UNCP</flux:text>
        </div>
    </div>

{{-- Formulario de Búsqueda en index.blade.php --}}
<form action="{{ route('agreements.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
    <flux:input 
        name="search" 
        value="{{ request('search') }}" 
        icon="magnifying-glass" 
        placeholder="Nombre, resolución, institución o país..." {{-- 👈 Placeholder actualizado --}}
        class="w-full sm:w-80 shadow-sm dark:bg-zinc-800/40" 
        clearable
    />
    <flux:button :href="route('agreements.create')" variant="primary" icon="plus" class="shadow-lg shadow-blue-500/20" wire:navigate>
        Nuevo Registro
    </flux:button>
</form>
</div>

        {{-- Tabla de Convenios con fondo más claro --}}
        {{-- Ajustamos dark:bg-zinc-900 por dark:bg-zinc-800/40 para que sea más claro --}}
        <flux:card class="p-0 overflow-hidden shadow-xl border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800/30">
            <flux:table>
                <flux:table.columns>
                    {{-- PL-12 para dar mucho más aire al inicio de la tabla --}}
                    <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-5 font-bold uppercase text-[10px] tracking-widest text-zinc-500">
    <span class="ml-10">Expediente / Resolución</span>
</flux:table.column>
                    <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-5 font-bold uppercase text-[10px] tracking-widest text-zinc-500">Institución</flux:table.column>
                    <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-5 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Vigencia</flux:table.column>
                    <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-5 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Estado</flux:table.column>
                    <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-5 text-right pr-12"></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse($agreements as $agreement)
                        <flux:table.row :key="$agreement->id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                            
                            {{-- Quitamos el padding de la celda y lo manejamos con margen en el div interno para control total --}}
<flux:table.cell class="py-6">
    {{-- ml-10 empuja TODO el bloque (icono y texto) lejos del borde de la tabla --}}
    <div class="flex items-center gap-4 ml-10"> 
        {{-- Icono de la hoja --}}
        <div class="p-2.5 bg-zinc-100 dark:bg-zinc-700 rounded-xl text-zinc-400 group-hover:text-blue-500 transition-colors shrink-0">
            <flux:icon name="document-text" variant="outline" class="size-5" />
        </div>
        
        {{-- Texto del Expediente --}}
        <div>
            <div class="font-bold text-zinc-900 dark:text-zinc-100 leading-tight text-[15px]">
                {{ $agreement->title }}
            </div>
            <div class="mt-1">
                <span class="text-[11px] font-mono text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-700/50 px-2 py-0.5 rounded border border-zinc-200 dark:border-zinc-700">
                    {{ $agreement->resolution_number ?? 'S/R' }}
                </span>
            </div>
        </div>
    </div>
</flux:table.cell>

                            <flux:table.cell>
                                <div class="text-sm font-bold text-zinc-700 dark:text-zinc-300 line-clamp-1 mb-1">{{ $agreement->institution->name }}</div>
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-block size-1.5 rounded-full bg-blue-500"></span>
                                    <span class="text-[10px] uppercase font-black text-zinc-400 tracking-tighter">{{ $agreement->institution->country }}</span>
                                </div>
                            </flux:table.cell>

                            <flux:table.cell class="text-center">
                                @if($agreement->start_date && $agreement->end_date)
                                    <span class="text-xs font-bold text-zinc-600 dark:text-zinc-400 bg-zinc-100 dark:bg-zinc-700 px-3 py-1.5 rounded-lg border border-zinc-200 dark:border-zinc-600">
                                        {{ $agreement->end_date->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-[11px] italic text-zinc-400">Sin fecha</span>
                                @endif
                            </flux:table.cell>

                            <flux:table.cell class="text-center">
                                @php
                                    $statusColor = 'zinc';
                                    $labelText = $agreement->status;
                                    
                                    if($agreement->end_date) {
                                        $days = now()->diffInDays($agreement->end_date, false);
                                        if($agreement->end_date->isPast()) { 
                                            $statusColor = 'red'; $labelText = 'Vencido'; 
                                        } elseif($days <= 30) { 
                                            $statusColor = 'yellow'; $labelText = 'Por Vencer'; 
                                        } else { 
                                            $statusColor = 'green'; $labelText = 'Vigente'; 
                                        }
                                    }
                                @endphp

                                <div class="flex justify-center">
                                    <flux:badge 
                                        :color="$statusColor" 
                                        variant="subtle" 
                                        size="sm" 
                                        class="font-black px-3.5 py-1 uppercase text-[10px] tracking-widest border border-current/20 dark:bg-opacity-20"
                                    >
                                        {{ $labelText }}
                                    </flux:badge>
                                </div>
                            </flux:table.cell>

                            {{-- Acciones con PR-12 para simetría --}}
{{-- Acciones con PR-12 para simetría --}}
<flux:table.cell class="pr-12">
    <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
        {{-- Botón Ver --}}
        <flux:button 
            variant="ghost" 
            size="sm" 
            icon="eye" 
            :href="route('agreements.show', $agreement)" 
            wire:navigate 
        />
        
        {{-- BOTÓN EDITAR FUNCIONAL --}}
        <flux:button 
            variant="ghost" 
            size="sm" 
            icon="pencil-square" 
            :href="route('agreements.edit', $agreement)" 
            wire:navigate 
        />
    </div>
</flux:table.cell>
                        </flux:table.row>
                    @empty
                        {{-- (Estado vacío) --}}
                    @endforelse
                </flux:table.rows>
            </flux:table>

            <div class="px-12 py-5 bg-zinc-50 dark:bg-zinc-800/50 border-t border-zinc-200 dark:border-zinc-800">
                {{ $agreements->links() }}
            </div>
        </flux:card>
    </div>
</x-layouts::app>