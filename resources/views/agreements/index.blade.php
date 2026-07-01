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
    @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
    @if(request('per_page')) <input type="hidden" name="per_page" value="{{ request('per_page') }}"> @endif
    <flux:input 
        name="search" 
        value="{{ request('search') }}" 
        icon="magnifying-glass" 
        placeholder="Nombre, resolución, institución o país..."
        class="w-full sm:w-80 shadow-sm dark:bg-zinc-800/40" 
        clearable
    />
    <flux:button :href="route('agreements.create')" variant="primary" icon="plus" class="shadow-lg shadow-blue-500/20" wire:navigate>
        Nuevo Registro
    </flux:button>
</form>
</div>

{{-- Filtros de Estado con colores --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="flex flex-wrap gap-2">
        @php
            $filterColors = [
                '' => 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900 border-zinc-900 dark:border-white',
                'En Proceso' => 'bg-zinc-500 text-white border-zinc-500 dark:bg-zinc-400 dark:text-zinc-900 dark:border-zinc-400',
                'Vigente' => 'bg-emerald-700 text-white border-emerald-700 dark:bg-emerald-600 dark:text-white dark:border-emerald-600',
                'Por Vencer' => 'bg-amber-500 text-white border-amber-500 dark:bg-amber-400 dark:text-zinc-900 dark:border-amber-400',
                'Vencido' => 'bg-red-700 text-white border-red-700 dark:bg-red-600 dark:text-white dark:border-red-600',
            ];
            $inactiveColor = 'bg-white dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700';
        @endphp
        <a href="{{ route('agreements.index', array_merge(request()->except('status', 'page'), ['status' => ''])) }}" 
           class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border transition-colors {{ !request('status') ? $filterColors[''] : $inactiveColor }}">
            Todos
        </a>
        @foreach(['En Proceso', 'Vigente', 'Por Vencer', 'Vencido'] as $estado)
            <a href="{{ route('agreements.index', array_merge(request()->except('status', 'page'), ['status' => $estado])) }}" 
               class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-lg border transition-colors {{ request('status') === $estado ? $filterColors[$estado] : $inactiveColor }}">
                {{ $estado }}
            </a>
        @endforeach
    </div>
</div>

        {{-- Tabla de Convenios con fondo más claro --}}
        {{-- Ajustamos dark:bg-zinc-900 por dark:bg-zinc-800/40 para que sea más claro --}}
        <flux:card class="p-0 shadow-xl border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-800/30">
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
                        @php
                            $pendingOpinions = $agreement->roadmapItems
                                ? $agreement->roadmapItems->filter(function($item) {
                                    $entrada = $item->documents->where('type', 'entrada')->count() > 0;
                                    $salida = $item->documents->where('type', 'salida')->count() > 0;
                                    return !($entrada && $salida);
                                })->pluck('area_name')
                                : collect();
                            $hasPending = $pendingOpinions->count() > 0;
                        @endphp
                        <flux:table.row :key="$agreement->id" class="group hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                            
                            {{-- Quitamos el padding de la celda y lo manejamos con margen en el div interno para control total --}}
<flux:table.cell class="py-6">
    <div class="flex items-center gap-4 ml-10 relative"
         x-data="{ showTooltip: false }"
         x-on:mouseenter="showTooltip = true"
         x-on:mouseleave="showTooltip = false">
        
        {{-- Tooltip de opiniones pendientes --}}
        @if($hasPending)
        <div x-show="showTooltip"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-x-2"
             x-transition:enter-end="opacity-100 translate-x-0"
             class="absolute top-0 left-full ml-3 z-50 w-72"
             x-on:mouseenter="showTooltip = true"
             x-on:mouseleave="showTooltip = false">
            <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-xl border border-zinc-200 dark:border-zinc-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <flux:icon name="clock" variant="mini" class="size-4 text-amber-500" />
                    <span class="text-xs font-bold uppercase tracking-wider text-zinc-500">Opiniones Pendientes</span>
                </div>
                <div class="space-y-1.5">
                    @foreach($pendingOpinions as $area)
                        <div class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                            <span class="size-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <span>Falta opinión de <strong>{{ $area }}</strong></span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 pt-2 border-t border-zinc-100 dark:border-zinc-700">
                    <span class="text-[11px] text-zinc-400 font-medium">{{ $pendingOpinions->count() }} área(s) pendiente(s)</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Icono de la hoja --}}
        <div class="p-2.5 bg-zinc-100 dark:bg-zinc-700 rounded-xl text-zinc-400 group-hover:text-blue-500 transition-colors shrink-0 {{ $hasPending ? 'ring-2 ring-amber-300 dark:ring-amber-600' : '' }}">
            <flux:icon name="document-text" variant="outline" class="size-5" />
        </div>
        
        {{-- Texto del Expediente --}}
        <div>
            <div class="font-bold text-zinc-900 dark:text-zinc-100 leading-tight text-[15px]">
                {{ $agreement->resolution_number ?? $agreement->title }}
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
                                    $labelText = $agreement->status;
                                    $badgeClasses = 'bg-zinc-100 text-zinc-700 border-zinc-300 dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-600';
                                    
                                    if($agreement->status === 'Vigente' && $agreement->end_date) {
                                        $days = now()->diffInDays($agreement->end_date, false);
                                        if($agreement->end_date->isPast()) { 
                                            $badgeClasses = 'bg-red-100 text-red-800 border-red-300 dark:bg-red-900/40 dark:text-red-300 dark:border-red-700';
                                            $labelText = 'Vencido'; 
                                        } elseif($days <= 90) { 
                                            $badgeClasses = 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-900/40 dark:text-amber-300 dark:border-amber-700';
                                            $labelText = 'Por Vencer'; 
                                        } else { 
                                            $badgeClasses = 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700';
                                        }
                                    } elseif ($agreement->status === 'Vigente') {
                                        $badgeClasses = 'bg-emerald-100 text-emerald-800 border-emerald-300 dark:bg-emerald-900/40 dark:text-emerald-300 dark:border-emerald-700';
                                    } elseif ($agreement->status === 'En Proceso') {
                                        $badgeClasses = 'bg-zinc-100 text-zinc-700 border-zinc-300 dark:bg-zinc-700 dark:text-zinc-300 dark:border-zinc-600';
                                    }
                                @endphp

                                <div class="flex justify-center">
                                    <span class="inline-flex items-center font-black px-3.5 py-1 uppercase text-[10px] tracking-widest rounded-lg border {{ $badgeClasses }}">
                                        {{ $labelText }}
                                    </span>
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
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm text-zinc-500">
                        <span>Mostrar</span>
                        <form action="{{ route('agreements.index') }}" method="GET" class="m-0 p-0 inline">
                            @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                            <select name="per_page" onchange="this.form.submit()" 
                                    class="text-sm rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 px-2 py-1 focus:ring-blue-500 focus:border-blue-500">
                                @foreach([10, 15, 25, 50, 100] as $count)
                                    <option value="{{ $count }}" {{ $perPage == $count ? 'selected' : '' }}>{{ $count }}</option>
                                @endforeach
                            </select>
                        </form>
                        <span>por página</span>
                    </div>
                    <div>
                        {{ $agreements->links() }}
                    </div>
                </div>
            </div>
        </flux:card>
    </div>
</x-layouts::app>