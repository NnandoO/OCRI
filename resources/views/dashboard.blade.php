<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <flux:card class="relative overflow-hidden border-s-4 border-green-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Vigentes</flux:text>
                        <flux:heading size="xl" class="mt-1 text-zinc-700 dark:text-green-400">{{ $stats['vigentes'] }}</flux:heading>
                    </div>
                    <flux:icon name="check-badge" variant="outline" class="size-10 text-green-100 dark:text-green-700" />
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">Convenios con ejecución activa.</flux:text>
            </flux:card>

            <flux:card class="relative overflow-hidden border-s-4 border-yellow-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Prontos a Vencer</flux:text>
                        <flux:heading size="xl" class="mt-1 text-yellow-600 dark:text-yellow-400">{{ $stats['por_vencer'] }}</flux:heading>
                    </div>
                    <flux:icon name="clock" variant="outline" class="size-10 text-yellow-100 dark:text-yellow-900/30" />
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">Expiran en los próximos 90 días.</flux:text>
            </flux:card>

            <flux:card class="relative overflow-hidden border-s-4 border-red-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Vencidos</flux:text>
                        <flux:heading size="xl" class="mt-1 text-red-600 dark:text-red-400">{{ $stats['vencidos'] }}</flux:heading>
                    </div>
                    <flux:icon name="exclamation-triangle" variant="outline" class="size-10 text-red-100 dark:text-red-900/30" />
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">Requieren renovación inmediata.</flux:text>
            </flux:card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 flex-1">
            
            <div class="lg:col-span-2 relative h-full rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-4 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <flux:heading level="2" size="lg">Convenios Recientes</flux:heading>
                    <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square" :href="route('agreements.index')" wire:navigate>Ver todos</flux:button>
                </div>
                
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Institución</flux:table.column>
                        <flux:table.column>País</flux:table.column>
                        <flux:table.column>Estado</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($recentAgreements as $agreement)
                            <flux:table.row>
                                <flux:table.cell class="font-medium">{{ $agreement->institution->name }}</flux:table.cell>
                                <flux:table.cell>{{ $agreement->institution->country }}</flux:table.cell>
                                <flux:table.cell>
                                    @if($agreement->status === 'En Proceso')
                                        <flux:badge color="zinc" size="sm">En Proceso</flux:badge>
                                    @elseif($agreement->end_date?->isPast())
                                        <flux:badge color="red" size="sm">Vencido</flux:badge>
                                    @else
                                        <flux:badge color="green" size="sm">Vigente</flux:badge>
                                    @endif
                                </flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="space-y-4">
                <flux:card class="dark:bg-zinc-800">
                    <flux:heading size="md" class="mb-3">Acciones Rápidas</flux:heading>
                    <div class="grid grid-cols-1 gap-2">
                        {{-- RUTAS CORREGIDAS --}}
                        <flux:button :href="route('agreements.create')" icon="plus" variant="primary" class="justify-start" wire:navigate>Nuevo Convenio</flux:button>
                        <flux:button :href="route('agreements.index')" icon="list-bullet" variant="ghost" class="justify-start" wire:navigate>Seguimiento de Convenios</flux:button>
                        <flux:button 
            :href="route('reports.index')" 
            icon="magnifying-glass-circle" 
            variant="ghost" 
            class="justify-start" 
            wire:navigate
        >
            Generar Reporte
        </flux:button>
                    </div>
                </flux:card>

                <flux:card class="bg-zinc-50 dark:bg-zinc-800/50">
                    <flux:heading size="sm" variant="subheading">Nota de Gestión</flux:heading>
                    <flux:text size="sm" class="mt-2 italic text-zinc-500">
                        "Recuerda que la centralización de datos mejora el ranking institucional de la UNCP."
                    </flux:text>
                </flux:card>
            </div>
        </div>
    </div>
</x-layouts::app>