<?php
$content = file_get_contents('resources/views/asistencia/index.blade.php');

$modal = <<<'BLADE'
    {{-- Modal para Regularizar Asistencia --}}
    <flux:modal name="regularizar-asistencia" class="md:w-[500px]">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Regularizar Asistencia</flux:heading>
                <flux:subheading>Ingresa manualmente un registro pasado o corrige uno existente.</flux:subheading>
            </div>
            <form action="{{ route('asistencia.regularizar') }}" method="POST" class="space-y-4">
                @csrf
                <flux:field>
                    <flux:label>Practicante</flux:label>
                    <flux:select name="practicante_id" searchable placeholder="Buscar practicante..." required>
                        @foreach($practicantes as $practicante)
                            <flux:select.option value="{{ $practicante->id }}">{{ $practicante->nombre }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="practicante_id" />
                </flux:field>
                <flux:field>
                    <flux:label>Fecha</flux:label>
                    <flux:input type="date" name="fecha" required value="{{ date('Y-m-d') }}" />
                    <flux:error name="fecha" />
                </flux:field>
                <div class="flex gap-4">
                    <flux:field class="w-1/2">
                        <flux:label>Hora Entrada</flux:label>
                        <flux:input type="time" name="hora_entrada" required />
                        <flux:error name="hora_entrada" />
                    </flux:field>
                    <flux:field class="w-1/2">
                        <flux:label>Hora Salida (Opcional)</flux:label>
                        <flux:input type="time" name="hora_salida" />
                        <flux:error name="hora_salida" />
                    </flux:field>
                </div>
                <div class="flex justify-end gap-2 mt-2">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancelar</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="primary">Guardar Registro</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</x-layouts::app>
BLADE;

$content = str_replace('</x-layouts::app>', $modal, $content);
file_put_contents('resources/views/asistencia/index.blade.php', $content);
