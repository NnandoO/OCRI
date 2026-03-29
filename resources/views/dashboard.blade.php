<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <flux:card class="relative overflow-hidden border-s-4 border-green-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Vigentes</flux:text>
                        <flux:heading size="xl" class="mt-1 text-green-600 dark:text-green-400">42</flux:heading>
                    </div>
                    <flux:icon name="check-badge" variant="outline" class="size-10 text-green-100 dark:text-green-700" />
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">Convenios con ejecución activa.</flux:text>
            </flux:card>

            <flux:card class="relative overflow-hidden border-s-4 border-yellow-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Prontos a Vencer</flux:text>
                        <flux:heading size="xl" class="mt-1 text-yellow-600 dark:text-yellow-400">08</flux:heading>
                    </div>
                    <flux:icon name="clock" variant="outline" class="size-10 text-yellow-100 dark:text-yellow-900/30" />
                </div>
                <flux:text class="mt-2 text-xs text-zinc-500">Expiran en los próximos 90 días.</flux:text>
            </flux:card>

            <flux:card class="relative overflow-hidden border-s-4 border-red-500 bg-white dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:text variant="subheading" class="text-xs font-bold uppercase tracking-wider">Vencidos</flux:text>
                        <flux:heading size="xl" class="mt-1 text-red-600 dark:text-red-400">15</flux:heading>
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
                    <flux:button variant="ghost" size="sm" icon="arrow-top-right-on-square">Ver todos</flux:button>
                </div>
                
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Institución</flux:table.column>
                        <flux:table.column>País</flux:table.column>
                        <flux:table.column>Estado</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        <flux:table.row>
                            <flux:table.cell>Univ. de São Paulo</flux:table.cell>
                            <flux:table.cell>Brasil</flux:table.cell>
                            <flux:table.cell><flux:badge color="green" inset="top">Vigente</flux:badge></flux:table.cell>
                        </flux:table.row>
                        <flux:table.row>
                            <flux:table.cell>Univ. Complutense</flux:table.cell>
                            <flux:table.cell>España</flux:table.cell>
                            <flux:table.cell><flux:badge color="yellow" inset="top">Por Vencer</flux:badge></flux:table.cell>
                        </flux:table.row>
                    </flux:table.rows>
                </flux:table>
            </div>

            <div class="space-y-4">
                <flux:card>
                    <flux:heading size="md" class="mb-3">Acciones Rápidas</flux:heading>
                    <div class="grid grid-cols-1 gap-2">
                        <flux:button icon="plus" variant="primary" class="justify-start">Nuevo Convenio</flux:button>
                        <flux:button icon="document-text" variant="ghost" class="justify-start">Generar Reporte Mensual</flux:button>
                        <flux:button icon="envelope" variant="ghost" class="justify-start">Notificar a Facultades</flux:button>
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