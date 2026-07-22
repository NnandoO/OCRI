<x-layouts::app title="Editar Oficio">
    <div class="p-6 max-w-5xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <flux:heading size="xl">Editor Visual de Oficio</flux:heading>
                <flux:subheading>Modifica el contenido del oficio antes de generar el documento final (PDF).</flux:subheading>
            </div>
            <flux:button href="{{ route('agreements.show', $oficio->agreement_id) }}" variant="ghost" icon="arrow-left">Volver al Expediente</flux:button>
        </div>

        <flux:card>
            <form action="{{ route('oficios.update', $oficio->id) }}" method="POST" id="oficio-form">
                @csrf
                @method('PUT')
                
                <div class="mb-4 flex items-center justify-between bg-zinc-50 dark:bg-zinc-800/50 p-4 rounded-xl border border-zinc-200 dark:border-zinc-700">
                    <div>
                        <p class="text-xs text-zinc-500 font-bold uppercase tracking-wider">Documento a generar</p>
                        <p class="text-lg font-bold text-zinc-800 dark:text-zinc-200">OFICIO N° {{ $oficio->oficio_number }}</p>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Dirigido a: {{ $oficio->directed_to }}</p>
                    </div>
                    <div class="text-right">
                        <flux:badge size="lg" color="amber" class="animate-pulse">Borrador (Sin PDF)</flux:badge>
                    </div>
                </div>

                {{-- Contenedor del Editor --}}
                <div class="border border-zinc-300 dark:border-zinc-700 rounded-lg overflow-hidden bg-white">
                    <div id="toolbar-container" class="bg-zinc-100 border-b border-zinc-300 dark:bg-zinc-800 dark:border-zinc-700">
                        <span class="ql-formats">
                            <button class="ql-bold"></button>
                            <button class="ql-italic"></button>
                            <button class="ql-underline"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-list" value="ordered"></button>
                            <button class="ql-list" value="bullet"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-align" value=""></button>
                            <button class="ql-align" value="center"></button>
                            <button class="ql-align" value="right"></button>
                            <button class="ql-align" value="justify"></button>
                        </span>
                        <span class="ql-formats">
                            <button class="ql-clean"></button>
                        </span>
                    </div>
                    <div id="editor-container" class="h-[500px] text-zinc-800" style="font-family: Helvetica, Arial, sans-serif; font-size: 14px; line-height: 1.5;">
                        {!! $oficio->body_html !!}
                    </div>
                </div>
                
                <input type="hidden" name="body_html" id="body_html_input">

                <div class="mt-6 flex justify-end gap-3">
                    <flux:button href="{{ route('agreements.show', $oficio->agreement_id) }}" variant="outline">Cancelar</flux:button>
                    <flux:button type="submit" variant="primary" icon="document-text" class="bg-green-600 hover:bg-green-700 text-white border-none" onclick="prepareSubmit()">Confirmar y Generar PDF</flux:button>
                </div>
            </form>
        </flux:card>

    </div>

    <!-- Quill Editor JS & CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            modules: {
                toolbar: '#toolbar-container'
            },
            theme: 'snow'
        });

        function prepareSubmit() {
            // Extraer el HTML del editor de Quill y ponerlo en el input hidden
            document.getElementById('body_html_input').value = quill.root.innerHTML;
        }
    </script>
</x-layouts::app>
