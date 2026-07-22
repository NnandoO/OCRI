<x-layouts::app title="Seguimiento de Convenios">
    <div class="p-6 max-w-7xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl" level="1">Seguimiento de Convenios</flux:heading>
                <flux:subheading class="mt-1">Control de hitos, informes y planes de trabajo de convenios Vigentes.</flux:subheading>
            </div>
        </div>

        <div class="grid gap-4">
            @forelse($agreements as $agreement)
                @php
                    $isExpiringSoon = $agreement->end_date && $agreement->end_date->diffInMonths(now()) <= 6 && $agreement->end_date->isFuture();
                    $isExpired = $agreement->end_date && $agreement->end_date->isPast();
                @endphp
                
                <a href="{{ route('seguimiento.show', $agreement->id) }}" class="block">
                    <flux:card class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors cursor-pointer border-l-4 {{ $isExpiringSoon ? 'border-l-amber-500' : ($isExpired ? 'border-l-red-500' : 'border-l-green-500') }}">
                        <div class="flex justify-between items-start gap-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-zinc-900 dark:text-zinc-100">{{ $agreement->title }}</h3>
                                    @if($isExpired)
                                        <flux:badge size="sm" color="red">Vencido</flux:badge>
                                    @elseif($isExpiringSoon)
                                        <flux:badge size="sm" color="amber" class="animate-pulse">Próximo a Vencer</flux:badge>
                                    @else
                                        <flux:badge size="sm" color="green">Vigente</flux:badge>
                                    @endif
                                </div>
                                <p class="text-sm text-zinc-600 dark:text-zinc-400"><strong>Institución:</strong> {{ $agreement->institution->name }}</p>
                                <div class="flex items-center gap-4 mt-2 text-xs text-zinc-500">
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="calendar" variant="mini" class="size-4" />
                                        Vence: {{ $agreement->end_date ? $agreement->end_date->format('d/m/Y') : 'Indefinido' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon name="document-text" variant="mini" class="size-4" />
                                        Resolución: {{ $agreement->resolution_number }}
                                    </span>
                                </div>
                            </div>
                            <flux:icon name="chevron-right" class="text-zinc-400" />
                        </div>
                    </flux:card>
                </a>
            @empty
                <div class="text-center py-12 text-zinc-500">
                    <flux:icon name="clipboard-document-check" variant="outline" class="size-12 mx-auto mb-3 opacity-50" />
                    <p>No hay convenios vigentes en este momento.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $agreements->links() }}
        </div>
    </div>
</x-layouts::app>
