<x-layouts::app title="Reportes Institucionales">
    {{-- Fondo mejorado para evitar que sea todo negro --}}
    <div class="min-h-screen bg-zinc-200 dark:bg-zinc-600 p-6 transition-colors duration-300">
        <div class="max-w-full mx-auto space-y-8">
            
            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-zinc-200 dark:border-zinc-800 pb-6">
                <div class="space-y-1">
                    <flux:heading size="xl" level="1" class="font-black tracking-tight text-zinc-900 dark:text-white">
                        Reportes de Gestión OCRI
                    </flux:heading>
                    <div class="flex items-center gap-2 text-zinc-500">
                        <flux:icon name="chart-bar" variant="outline" class="size-4" />
                        <flux:text size="sm" class="font-medium">Análisis de vigencia y alianzas estratégicas internacionales</flux:text>
                    </div>
                </div>
            </div>

            {{-- Bloque de Filtros --}}
            <flux:card class="bg-white dark:bg-zinc-900 shadow-xl border-zinc-200 dark:border-zinc-800 p-8">
                {{-- Agregamos un ID al form para el script de Excel --}}
                <form id="filter-form" action="{{ route('reports.index') }}" method="GET" class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-6 items-end">
                        <flux:field class="lg:col-span-4">
                            <flux:label class="font-bold text-[11px] uppercase tracking-widest text-zinc-400 mb-2 block">Nombre del Convenio</flux:label>
                            <flux:input name="search" icon="magnifying-glass" placeholder="Buscar..." value="{{ request('search') }}" class="dark:bg-zinc-800/50" />
                        </flux:field>

                        <flux:field class="lg:col-span-2">
                            <flux:label class="font-bold text-[11px] uppercase tracking-widest text-zinc-400 mb-2 block">Entidad</flux:label>
                            <flux:select name="classification" placeholder="Todas">
                                @foreach($classifications as $cls)
                                    <flux:select.option value="{{ $cls }}" :selected="request('classification') == $cls">{{ $cls }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field class="lg:col-span-2">
                            <flux:label class="font-bold text-[11px] uppercase tracking-widest text-zinc-400 mb-2 block">Modalidad</flux:label>
                            <flux:select name="type_id" placeholder="Todos">
                                @foreach($types as $type)
                                    <flux:select.option value="{{ $type->id }}" :selected="request('type_id') == $type->id">{{ $type->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field class="lg:col-span-2">
                            <flux:label class="font-bold text-[11px] uppercase tracking-widest text-zinc-400 mb-2 block">País</flux:label>
                            <flux:select name="country" placeholder="Todos">
                                @foreach($countries as $country)
                                    <flux:select.option value="{{ $country }}" :selected="request('country') == $country">{{ $country }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        </flux:field>

                        <flux:field class="lg:col-span-2">
                            <flux:label class="font-bold text-[11px] uppercase tracking-widest text-zinc-400 mb-2 block">Año</flux:label>
                            <flux:input type="number" name="year" placeholder="2026" value="{{ request('year') }}" class="dark:bg-zinc-800/50" />
                        </flux:field>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-zinc-100 dark:border-zinc-800">
                        <flux:button :href="route('reports.index')" variant="ghost" icon="arrow-path">Limpiar</flux:button>
                        
                        {{-- BOTÓN EXCEL: Cambiado a type="button" para que el ENTER lo ignore --}}
                        <flux:button 
                            type="button" 
                            onclick="downloadExcel()" 
                            variant="subtle" 
                            icon="document-arrow-down" 
                            class="text-green-600 dark:text-green-400 font-bold border-green-200 dark:border-green-900/30 bg-green-50 dark:bg-green-900/10"
                        >
                            Excel
                        </flux:button>

                        {{-- BOTÓN FILTRAR: Es el único SUBMIT, por lo tanto el ENTER lo activará --}}
                        <flux:button type="submit" variant="primary" icon="funnel" class="px-8 shadow-lg shadow-blue-500/20">
                            Filtrar
                        </flux:button>
                    </div>
                </form>
            </flux:card>

            {{-- Tabla de Resultados --}}
            <flux:card class="p-0 overflow-hidden shadow-xl border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/50 py-5 font-black uppercase text-[10px] tracking-widest text-zinc-500">
                            <span class="ml-12 text-zinc-500 dark:text-zinc-400">Expediente / Nombre Oficial</span>
                        </flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/50 py-5 font-black uppercase text-[10px] tracking-widest text-zinc-500">Entidad Aliada</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/50 py-5 font-black uppercase text-[10px] tracking-widest text-zinc-500">País</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/50 py-5 font-black uppercase text-[10px] tracking-widest text-zinc-500 text-center">Año</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/50 py-5 font-black uppercase text-[10px] tracking-widest text-zinc-500 text-center">Estado</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse($agreements as $agreement)
                            <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40 transition-colors">
                                <flux:table.cell class="py-5">
                                    <div class="flex items-center gap-5 ml-12 text-left">
                                        <div class="shrink-0 p-2 bg-zinc-100 dark:bg-zinc-700 rounded-lg text-zinc-400">
                                            <flux:icon name="document-text" variant="outline" class="size-4" />
                                        </div>
                                        <div class="max-w-md">
                                            <span class="block font-bold text-zinc-900 dark:text-zinc-100 leading-tight">
                                                {{ $agreement->title }}
                                            </span>
                                            <span class="text-[11px] text-zinc-500 dark:text-zinc-400 italic line-clamp-1 mt-1">
                                                "{{ $agreement->name }}"
                                            </span>
                                        </div>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <span class="block font-semibold text-zinc-700 dark:text-zinc-300">{{ $agreement->institution->name }}</span>
                                    <flux:badge size="sm" variant="subtle" class="mt-1.5 font-bold uppercase text-[9px] tracking-tighter">
                                        {{ $agreement->institution->type }}
                                    </flux:badge>
                                </flux:table.cell>

                                <flux:table.cell>
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="globe-alt" class="size-3 text-zinc-400" />
                                        <span class="text-xs uppercase font-medium text-zinc-600 dark:text-zinc-400">{{ $agreement->institution->country }}</span>
                                    </div>
                                </flux:table.cell>

                                <flux:table.cell class="text-center font-mono text-xs text-zinc-500">
                                    {{ $agreement->start_date?->format('Y') ?? '---' }}
                                </flux:table.cell>

                                <flux:table.cell class="text-center">
                                    <flux:badge 
                                        :color="$agreement->status === 'Vigente' ? 'green' : 'zinc'" 
                                        variant="subtle" 
                                        class="font-black px-3 uppercase text-[9px] tracking-widest border border-current/20"
                                    >
                                        {{ $agreement->status }}
                                    </flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="text-center py-20 text-zinc-400">
                                    No se encontraron resultados para los filtros aplicados.
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </flux:card>
        </div>
    </div>

    {{-- Script para manejar la descarga de Excel manteniendo los filtros --}}
    <script>
        function downloadExcel() {
            const form = document.getElementById('filter-form');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData).toString();
            
            // Redirigimos a la misma URL pero agregando el parámetro export=1
            window.location.href = "{{ route('reports.index') }}?" + params + "&export=1";
        }
    </script>
</x-layouts::app>