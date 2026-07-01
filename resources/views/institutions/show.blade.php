<x-layouts::app :title="$institution->name">
    <div class="p-6 max-w-5xl mx-auto">
        <flux:button :href="route('institutions.index')" variant="ghost" icon="arrow-left" class="mb-6" wire:navigate>
            Volver a Instituciones
        </flux:button>

        <flux:card class="p-6 mb-8 bg-zinc-50 dark:bg-zinc-700">
            <flux:heading size="xl">{{ $institution->name }}</flux:heading>
            <flux:subheading>{{ $institution->country }} • {{ $institution->type }}</flux:subheading>
        </flux:card>

        <flux:heading size="lg" class="mb-4">Convenios Asociados</flux:heading>
        
        <flux:card class="p-0 overflow-hidden">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Título</flux:table.column>
                    <flux:table.column>Tipo</flux:table.column>
                    <flux:table.column>Estado</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @foreach($institution->agreements as $agreement)
                        <flux:table.row>
                            <flux:table.cell>{{ $agreement->title }}</flux:table.cell>
                            <flux:table.cell>{{ $agreement->type->name }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge :color="$agreement->status === 'Vigente' ? 'green' : 'zinc'">{{ $agreement->status }}</flux:badge>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</x-layouts::app>