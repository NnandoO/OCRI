<?php

use Livewire\Component;
use Livewire\Attributes\Title;

new #[Title('Configuración de Apariencia')] class extends Component {
    //
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Configuración de apariencia') }}</flux:heading>

    <!-- Solo pasamos el heading, quitamos el subheading de aquí -->
    <x-pages::settings.layout :heading="__('Apariencia')">
        
        <!-- Tu nuevo subheading con estilo personalizado -->
        <div class="mb-6 border-l-4 ">
            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                {{ __('Actualiza los ajustes de apariencia para tu cuenta.') }}
            </p>
            <p class="mt-1 text-xs italic text-zinc-500 dark:text-zinc-400">
                {{ __('Estos cambios se aplicarán a todo el panel de gestión.') }}
            </p>
        </div>

        <!-- Tarjeta que mantiene el diseño del Dashboard -->
        <flux:card class="relative overflow-hidden border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800 p-1">
            <flux:radio.group x-data variant="segmented" x-model="$flux.appearance" class="w-full">
                <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
                <flux:radio value="dark" icon="moon">{{ __('Oscuro') }}</flux:radio>
                <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
            </flux:radio.group>
        </flux:card>

    </x-pages::settings.layout>
</section>