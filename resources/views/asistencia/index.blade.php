<x-layouts::app :title="__('Asistencia')">
    <div class="p-6 max-w-5xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">Control de Asistencia</flux:heading>
                <flux:subheading class="mt-1">Registro de entrada y salida de practicantes</flux:subheading>
            </div>
            <flux:modal.trigger name="nuevo-practicante">
                <flux:button icon="plus" class="shrink-0" size="sm">Nuevo Practicante</flux:button>
            </flux:modal.trigger>
        </div>

        {{-- Formulario de Entrada --}}
        <flux:card>
            <form action="{{ route('asistencia.store') }}" method="POST" class="flex flex-col sm:flex-row items-end sm:items-center gap-4"
                  x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf
                <div class="flex-1 w-full">
                    @if($errors->has('error'))
                        <div class="mb-3 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 p-2 rounded-lg font-medium border border-red-200 dark:border-red-800">
                            {{ $errors->first('error') }}
                        </div>
                    @endif
                    <flux:label class="font-bold">Practicante</flux:label>
                    <flux:select name="practicante_id" searchable placeholder="Buscar practicante..." required class="dark:bg-zinc-800/50">
                        @foreach($practicantes as $practicante)
                            <flux:select.option value="{{ $practicante->id }}">{{ $practicante->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:button type="submit" variant="primary" icon="arrow-right-end-on-rectangle" class="w-full sm:w-auto shrink-0 bg-blue-600 hover:bg-blue-700"
                            x-bind:disabled="submitting">
                    <span x-show="!submitting">Marcar Entrada</span>
                    <span x-show="submitting">Registrando...</span>
                </flux:button>
            </form>
        </flux:card>

        {{-- Selector de Fecha --}}
        <div class="flex items-center gap-3">
            <flux:icon name="calendar" variant="mini" class="size-5 text-zinc-400" />
            <form action="{{ route('asistencia.index') }}" method="GET" class="flex items-center gap-2">
                <input type="date" name="fecha" value="{{ $fecha }}"
                       onchange="this.form.submit()"
                       class="text-sm border border-zinc-300 dark:border-zinc-600 rounded-lg px-3 py-1.5 bg-white dark:bg-zinc-800" />
                <flux:text class="text-xs text-zinc-500">{{ \Carbon\Carbon::parse($fecha)->isoFormat('dddd D [de] MMMM [de] YYYY') }}</flux:text>
            </form>
        </div>

        {{-- Tabla de Registros --}}
        <flux:card class="p-0 shadow-sm">
            @if($registros->isEmpty())
                <div class="py-16 flex flex-col items-center justify-center text-zinc-400">
                    <flux:icon name="clipboard-document-list" variant="outline" class="size-12 mb-3" />
                    <p class="text-sm font-medium">No hay registros para esta fecha</p>
                    <p class="text-xs mt-1">Usa el formulario de arriba para marcar la entrada de un practicante.</p>
                </div>
            @else
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500">N°</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500">Nombre</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Entrada</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 font-bold uppercase text-[10px] tracking-widest text-zinc-500 text-center">Salida</flux:table.column>
                        <flux:table.column class="bg-zinc-50 dark:bg-zinc-800/60 py-4 text-right pr-6"></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @foreach($registros as $i => $r)
                            <flux:table.row class="hover:bg-zinc-50 dark:hover:bg-zinc-700/30 transition-colors">
                                <flux:table.cell class="text-xs text-zinc-400 font-mono">{{ $i + 1 }}</flux:table.cell>
                                <flux:table.cell>
                                    <a href="{{ route('practicantes.show', $r->practicante_id) }}" class="font-bold text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline transition-colors">
                                        {{ $r->practicante->nombre }}
                                    </a>
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
                                    @unless($r->hora_salida)
                                        <form action="{{ route('asistencia.salida', $r->id) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <flux:button type="submit" size="sm" variant="primary" icon="arrow-right-start-on-rectangle" class="bg-green-600 hover:bg-green-700 text-xs">
                                                Salida
                                            </flux:button>
                                        </form>
                                    @endunless
                                    <form action="{{ route('asistencia.destroy', $r->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Eliminar registro de {{ $r->practicante->nombre }}?')">
                                        @csrf @method('DELETE')
                                        <flux:button type="submit" size="sm" variant="ghost" icon="trash" class="text-red-400 hover:text-red-600" />
                                    </form>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            @endif
        </flux:card>

        {{-- Historial por fecha --}}
        @if($todasFechas->count() > 1)
        <flux:card class="bg-zinc-50 dark:bg-zinc-800/50">
            <flux:heading size="sm" class="mb-3">Historial por fecha</flux:heading>
            <div class="flex flex-wrap gap-1.5">
                @foreach($todasFechas as $f)
                    <a href="{{ route('asistencia.index', ['fecha' => $f]) }}"
                       class="px-3 py-1 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors {{ $f === $fecha ? 'ring-2 ring-blue-500' : '' }}">
                        {{ \Carbon\Carbon::parse($f)->format('d/m/Y') }}
                    </a>
                @endforeach
            </div>
        </flux:card>
        @endif

    </div>

    {{-- Modal para Registrar Nuevo Practicante --}}
    <flux:modal name="nuevo-practicante" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Registrar Nuevo Practicante</flux:heading>
                <flux:subheading>Ingresa los nombres y apellidos del practicante.</flux:subheading>
            </div>
            <form action="{{ route('practicantes.store') }}" method="POST" class="space-y-4">
                @csrf
                <flux:field>
                    <flux:label>Nombre Completo</flux:label>
                    <flux:input name="nombre" placeholder="Ej. JUAN PEREZ" required class="uppercase" oninput="this.value = this.value.toUpperCase()" autocomplete="off" />
                    <flux:error name="nombre" />
                </flux:field>
                <flux:field>
                    <flux:label>DNI</flux:label>
                    <flux:input name="dni" placeholder="Ej. 12345678" required maxlength="8" pattern="\d{8}" title="Debe contener 8 dígitos" autocomplete="off" />
                    <flux:error name="dni" />
                </flux:field>
                <div class="flex justify-end gap-2 mt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Guardar</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</x-layouts::app>
