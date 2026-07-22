<x-layouts::app :title="__('Resumen del Practicante')">
    <div class="p-6 max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <flux:button href="{{ route('asistencia.index') }}" variant="ghost" icon="arrow-left" class="shrink-0" />
                <div>
                    <flux:heading size="xl" level="1">{{ $practicante->nombre }}</flux:heading>
                    <flux:subheading class="mt-1">
                        {{ $practicante->dni ? 'DNI: ' . $practicante->dni : 'DNI no registrado' }} &bull; Resumen de Asistencia y Horas Acumuladas
                    </flux:subheading>
                </div>
            </div>
            
            <flux:button href="{{ route('practicantes.export', $practicante) }}" variant="primary" icon="document-arrow-down" class="bg-emerald-600 hover:bg-emerald-700">
                Exportar Excel
            </flux:button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:card>
                <flux:subheading>Total Horas Acumuladas</flux:subheading>
                <flux:heading size="2xl" class="mt-2 text-blue-600 dark:text-blue-400">{{ $totalHoras }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:subheading>Días Asistidos</flux:subheading>
                <flux:heading size="2xl" class="mt-2">{{ $asistencias->count() }}</flux:heading>
            </flux:card>
        </div>

        {{-- Tabla de Registros --}}
        <flux:card class="p-0 shadow-sm mt-6">
            @if($asistencias->isEmpty())
                <div class="py-16 flex flex-col items-center justify-center text-zinc-400">
                    <flux:icon name="clipboard-document-list" variant="outline" class="size-12 mb-3" />
                    <p class="text-sm font-medium">No hay registros de asistencia</p>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500">Fecha</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Entrada</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Salida</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-right pr-6">Horas</flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($asistencias as $r)
                            <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                                <flux:table.cell>
                                    <span class="font-medium text-sm text-zinc-800 dark:text-zinc-200">
                                        {{ \Carbon\Carbon::parse($r->fecha)->isoFormat('DD MMM YYYY') }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell class="text-center">
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 dark:text-blue-400">
                                        <flux:icon name="arrow-right-end-on-rectangle" variant="mini" class="size-3.5" />
                                        {{ $r->hora_entrada ? $r->hora_entrada->format('H:i') : '---' }}
                                    </span>
                                </flux:table.cell>
                                <flux:table.cell class="text-center">
                                    @if($r->hora_salida)
                                        <span class="inline-flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400">
                                            <flux:icon name="arrow-right-start-on-rectangle" variant="mini" class="size-3.5" />
                                            {{ $r->hora_salida->format('H:i') }}
                                        </span>
                                    @else
                                        <span class="text-xs text-zinc-400 italic">Pendiente</span>
                                    @endif
                                </flux:table.cell>
                                <flux:table.cell class="text-right pr-6">
                                    @if($r->hora_entrada && $r->hora_salida)
                                        @php
                                            $mins = $r->hora_entrada->diffInMinutes($r->hora_salida);
                                            $h = floor($mins / 60);
                                            $m = $mins % 60;
                                        @endphp
                                        <span class="text-sm font-bold text-zinc-700 dark:text-zinc-300">{{ $h }}h {{ $m }}m</span>
                                    @else
                                        <span class="text-sm text-zinc-400">---</span>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

    </div>
</x-layouts::app>
