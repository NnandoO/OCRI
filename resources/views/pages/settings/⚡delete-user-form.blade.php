<?php

use Livewire\Component;

new class extends Component {}; ?>

<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Eliminar cuenta') }}</flux:heading>
        
        <!-- Nuevo diseño del subheading, manteniendo exactamente el texto original -->
        <div class="mt-2 border-l-4">
            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                {{ __('Eliminar tu cuenta y todos sus recursos') }}
            </p>
        </div>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" data-test="delete-user-button">
            {{ __('Eliminar cuenta') }}
        </flux:button>
    </flux:modal.trigger>

    <livewire:pages::settings.delete-user-modal />
</section>