<x-layouts::app title="Mapa de Procesos">
    <div class="p-6 max-w-7xl mx-auto">
        <flux:heading size="xl" class="mb-8 text-center">Flujo de Trabajo OCRI</flux:heading>

        @php
            // Definimos clases manuales para evitar problemas con la interpolación de strings de Tailwind
            $steps = [
                ['name' => 'Formulación', 'icon' => 'pencil-square', 'bg' => 'bg-zinc-100 dark:bg-zinc-800/50', 'text' => 'text-zinc-600 dark:text-zinc-400', 'border' => 'border-zinc-200 dark:border-zinc-700'],
                ['name' => 'Revisión Técnica', 'icon' => 'beaker', 'bg' => 'bg-blue-100 dark:bg-blue-900/30', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-200 dark:border-blue-800/50'],
                ['name' => 'Asesoría Jurídica', 'icon' => 'scale', 'bg' => 'bg-amber-100 dark:bg-amber-900/30', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-200 dark:border-amber-800/50'],
                ['name' => 'Suscripción', 'icon' => 'check-badge', 'bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-600 dark:text-green-400', 'border' => 'border-green-200 dark:border-green-800/50'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @foreach($steps as $step)
                <div class="flex flex-col gap-4">
                    <div class="p-3 rounded-lg {{ $step['bg'] }} border {{ $step['border'] }}">
                        <div class="flex items-center gap-2">
                            <flux:icon name="{{ $step['icon'] }}" class="size-5 {{ $step['text'] }}" />
                            <span class="font-bold text-xs uppercase tracking-wider {{ $step['text'] }}">{{ $step['name'] }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 min-h-[500px] bg-zinc-50/50 dark:bg-zinc-900/20 p-2 rounded-xl border-2 border-dashed border-zinc-200 dark:border-zinc-800">
                        @php
                            $agreementsInStep = \App\Models\Agreement::where('status', $step['name'])->with('institution')->get();
                        @endphp

                        @forelse($agreementsInStep as $agreement)
                            <flux:card class="p-3 shadow-sm hover:shadow-md transition-shadow bg-white dark:bg-zinc-800 border-zinc-200 dark:border-zinc-700">
                                <p class="text-xs font-bold truncate text-zinc-800 dark:text-zinc-100">{{ $agreement->institution->name }}</p>
                                <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mb-3 font-mono">{{ $agreement->resolution_number }}</p>
                                
                                <div class="flex justify-between items-center border-t border-zinc-100 dark:border-zinc-700 pt-2">
                                    <flux:dropdown>
                                        <flux:button size="xs" variant="ghost" icon="arrow-path" class="text-zinc-500 dark:text-zinc-400">Mover</flux:button>
                                        <flux:menu>
                                            @foreach($steps as $moveStep)
                                                @if($moveStep['name'] !== $step['name'])
                                                    <form action="{{ route('agreements.updateStatus', $agreement) }}" method="POST">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="{{ $moveStep['name'] }}">
                                                        <flux:menu.item as="button" type="submit" size="sm">
                                                            {{ $moveStep['name'] }}
                                                        </flux:menu.item>
                                                    </form>
                                                @endif
                                            @endforeach
                                        </flux:menu>
                                    </flux:dropdown>
                                    
                                    <flux:button icon="eye" size="xs" variant="ghost" :href="route('agreements.show', $agreement)" wire:navigate />
                                </div>
                            </flux:card>
                        @empty
                            <div class="flex flex-col items-center justify-center py-10 opacity-40">
                                <flux:icon name="inbox" class="size-8 text-zinc-300 dark:text-zinc-600" />
                                <p class="text-[10px] text-center text-zinc-400 mt-2 italic">Sin convenios</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>