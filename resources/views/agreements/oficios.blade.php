<x-layouts::app :title="'Generar Oficios - ' . $agreement->title">
    <div class="p-6 max-w-5xl mx-auto space-y-6">

        <div class="flex justify-between items-center">
            <div>
                <flux:heading size="xl" level="1" class="font-black tracking-tight">Generar Oficios de Solicitud de Opinión</flux:heading>
                <flux:text size="sm" class="text-zinc-500 mt-1">Se generará un oficio independiente para cada área seleccionada en la hoja de ruta.</flux:text>
            </div>
            <flux:button :href="route('agreements.show', $agreement->id)" variant="ghost" icon="arrow-left" wire:navigate>
                Volver al Expediente
            </flux:button>
        </div>

        <div class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl p-4">
            <div class="flex items-center gap-2 text-amber-700 dark:text-amber-400">
                <flux:icon name="information-circle" variant="mini" class="size-5" />
                <span class="text-sm font-medium">Complete los datos para cada área. El sistema generará automáticamente el formato del oficio con la plantilla institucional.</span>
            </div>
        </div>

        @php
            $oficioItems = $agreement->roadmapItems->reject(function($item) {
                return strtolower(trim($item->area_name)) === 'rectorado';
            })->values();

            $parts = $nextOficioNumber ? explode('-', $nextOficioNumber) : ['1', date('Y')];
            $nextNum = (int)$parts[0];
            $currentYear = $parts[1] ?? date('Y');
        @endphp

        <form action="{{ route('agreements.oficios.store', $agreement->id) }}" method="POST" class="space-y-4"
              x-data="{ submitting: false, recalc(index) {
                      const inputs = document.querySelectorAll('.oficio-number-input');
                      const changed = inputs[index];
                      if (!changed) return;
                      const match = changed.value.match(/^(\d+)/);
                      if (!match) return;
                      let num = parseInt(match[1], 10);
                      const yearPart = changed.value.includes('-') ? changed.value.split('-')[1] : '{{ $currentYear }}';
                      for (let i = index + 1; i < inputs.length; i++) {
                          num++;
                          const padded = String(num).padStart(3, '0');
                          inputs[i].value = padded + '-' + yearPart;
                      }
                  }
              }"
              x-on:submit="if(submitting) { $event.preventDefault(); return; } submitting = true;">
            @csrf

            @foreach($oficioItems as $idx => $item)
                <flux:card class="bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700 shadow-sm">
                    <div class="flex items-center gap-3 mb-5 border-b border-zinc-100 dark:border-zinc-700 pb-3">
                        <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                            <flux:icon name="building-library" class="size-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <flux:heading size="md">{{ $item->area_name }}</flux:heading>
                            <flux:text size="xs" class="text-zinc-500">Configure el oficio para solicitar opinión técnica a esta área.</flux:text>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <flux:field>
                            <flux:label class="font-bold">A quién va dirigido</flux:label>
                            <flux:input 
                                name="areas[{{ $item->id }}][directed_to]" 
                                placeholder="Ej: Dr. Juan Pérez, Vicerrector de Investigación" 
                                value="{{ old('areas.' . $item->id . '.directed_to') }}"
                                required 
                                class="dark:bg-zinc-800/50 uppercase"
                                oninput="this.value = this.value.toUpperCase()"
                            />
                            <flux:error name="areas.{{ $item->id }}.directed_to" />
                        </flux:field>

                        <flux:field>
                            <flux:label class="font-bold">N° de Oficio</flux:label>
                            <flux:input 
                                class="dark:bg-zinc-800/50 uppercase oficio-number-input"
                                name="areas[{{ $item->id }}][oficio_number]" 
                                placeholder="Ej: 001-2026" 
                                value="{{ old('areas.' . $item->id . '.oficio_number', str_pad($nextNum + $idx, 3, '0', STR_PAD_LEFT) . '-' . $currentYear) }}"
                                required 
                                x-on:input="
                                    this.value = this.value.toUpperCase();
                                    if (this.value.match(/^\d/)) {
                                        $data.recalc({{ $idx }});
                                    }
                                "
                            />
                            <flux:error name="areas.{{ $item->id }}.oficio_number" />
                            <flux:text size="xs" class="text-zinc-400 mt-1">Al modificar este número, los siguientes se ajustan automáticamente.</flux:text>
                        </flux:field>
                    </div>
                </flux:card>
            @endforeach

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button :href="route('agreements.show', $agreement->id)" variant="ghost">Cancelar</flux:button>
                <flux:button type="submit" variant="primary" icon="document-duplicate" class="px-8 py-3 shadow-lg shadow-indigo-500/20 bg-indigo-600 hover:bg-indigo-700"
                            x-bind:disabled="submitting">
                    <span x-show="!submitting">Generar Todos los Oficios</span>
                    <span x-show="submitting">Generando...</span>
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
