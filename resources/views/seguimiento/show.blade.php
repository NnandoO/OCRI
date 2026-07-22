<x-layouts::app title="Seguimiento: {{ $agreement->title }}">
    <div class="p-6 max-w-6xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Seguimiento del Convenio</flux:heading>
                <flux:subheading class="mt-1">{{ $agreement->title }}</flux:subheading>
            </div>
            <flux:button href="{{ route('seguimiento.index') }}" variant="ghost" icon="arrow-left">Volver a Seguimiento</flux:button>
        </div>

        @if(session('status'))
            <div class="bg-green-50 text-green-700 p-3 rounded-lg text-sm border border-green-200">
                {{ session('status') }}
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 text-red-700 p-3 rounded-lg text-sm border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Columna Izquierda: Plan de Trabajo --}}
            <div class="md:col-span-1 space-y-6">
                <flux:card>
                    <flux:heading size="lg" class="mb-4">Plan de Trabajo</flux:heading>
                    
                    @if($agreement->workPlan)
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700 mb-4 text-center">
                            <flux:icon name="document-text" class="size-10 mx-auto text-blue-500 mb-2" />
                            <p class="text-sm font-bold truncate" title="{{ $agreement->workPlan->original_name }}">
                                {{ $agreement->workPlan->original_name }}
                            </p>
                            <flux:button href="{{ Storage::url($agreement->workPlan->file_path) }}" target="_blank" variant="ghost" size="sm" class="mt-2 text-blue-600">Ver Documento</flux:button>
                        </div>
                    @else
                        <p class="text-sm text-zinc-500 mb-4">Aún no se ha subido un plan de trabajo oficial para este convenio.</p>
                    @endif

                    <form action="{{ route('seguimiento.storePlan', $agreement->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <flux:field>
                            <flux:label>{{ $agreement->workPlan ? 'Actualizar Plan' : 'Subir Plan' }}</flux:label>
                            <input type="file" name="work_plan_file" required accept=".pdf,.doc,.docx" class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                            <flux:error name="work_plan_file" />
                        </flux:field>
                        <flux:button type="submit" variant="primary" class="w-full">Guardar Plan</flux:button>
                    </form>
                </flux:card>
            </div>

            {{-- Columna Derecha: Informes --}}
            <div class="md:col-span-2 space-y-6">
                
                {{-- Formulario de Nuevo Informe --}}
                <flux:card class="bg-zinc-50/50 dark:bg-zinc-900/20 border-dashed">
                    <flux:heading size="md" class="mb-4">Registrar Nuevo Informe</flux:heading>
                    <form action="{{ route('seguimiento.storeReport', $agreement->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <flux:field class="sm:col-span-2">
                            <flux:label>Título / Descripción Breve</flux:label>
                            <flux:input name="title" placeholder="Ej. Primer Informe Semestral" required />
                        </flux:field>
                        
                        <flux:field>
                            <flux:label>Fecha del Informe</flux:label>
                            <flux:input type="date" name="date" required value="{{ date('Y-m-d') }}" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Oficio (Opcional)</flux:label>
                            <input type="file" name="oficio_file" class="block w-full text-sm" />
                        </flux:field>

                        <flux:field>
                            <flux:label>Respuesta (Opcional)</flux:label>
                            <input type="file" name="respuesta_file" class="block w-full text-sm" />
                        </flux:field>

                        <div class="sm:col-span-2 flex justify-end">
                            <flux:button type="submit" variant="primary" icon="plus">Añadir Informe</flux:button>
                        </div>
                    </form>
                </flux:card>

                {{-- Listado de Informes --}}
                <div>
                    <flux:heading size="lg" class="mb-4">Historial de Informes</flux:heading>
                    
                    @if($agreement->reports->isEmpty())
                        <div class="text-center py-8 text-zinc-500 border border-dashed rounded-xl">
                            <p>No hay informes registrados todavía.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($agreement->reports as $report)
                                <flux:card class="relative overflow-hidden">
                                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="font-bold text-lg text-zinc-800 dark:text-zinc-200">{{ $report->title }}</h4>
                                            <p class="text-sm text-zinc-500 flex items-center gap-1 mt-1">
                                                <flux:icon name="calendar" variant="mini" class="size-4" />
                                                {{ $report->date->format('d \d\e F, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        @if($report->oficio_path)
                                            <a href="{{ Storage::url($report->oficio_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 rounded-lg transition-colors text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                                <flux:icon name="document-arrow-down" class="size-4 text-blue-500" />
                                                <span class="truncate max-w-[150px]" title="{{ $report->oficio_original_name }}">Oficio: {{ $report->oficio_original_name }}</span>
                                            </a>
                                        @endif

                                        @if($report->respuesta_path)
                                            <a href="{{ Storage::url($report->respuesta_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm font-medium bg-green-50 hover:bg-green-100 dark:bg-green-900/30 dark:hover:bg-green-900/50 rounded-lg transition-colors text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                                <flux:icon name="check-badge" class="size-4 text-green-500" />
                                                <span class="truncate max-w-[150px]" title="{{ $report->respuesta_original_name }}">Rpta: {{ $report->respuesta_original_name }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </flux:card>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-layouts::app>
