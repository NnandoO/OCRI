<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Configuración de Perfil')] class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Configuración de perfil') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Información del Perfil')" :subheading="__('Actualiza tu nombre y correo electrónico institucional.')">
        
        <form wire:submit="updateProfileInformation" class="mt-6 w-full space-y-6">
            
            <flux:card class="relative overflow-hidden border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    
                    <div class="md:col-span-2">
                        <flux:input 
                            wire:model="name" 
                            :label="__('Nombre y Apellidos')" 
                            type="text" 
                            required 
                            autofocus 
                            autocomplete="name" 
                            placeholder="Ej. Hernando De Palomino"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <flux:input 
                            wire:model="email" 
                            :label="__('Correo Institucional')" 
                            type="email" 
                            required 
                            autocomplete="email" 
                            placeholder="usuario@uncp.edu.pe"
                        />

                        @if ($this->hasUnverifiedEmail)
                            <flux:card class="mt-4 border-s-4 border-yellow-500 bg-yellow-50/50 p-4 dark:bg-yellow-900/10">
                                <div class="flex items-start">
                                    <flux:icon name="exclamation-triangle" variant="outline" class="mr-3 mt-0.5 size-5 text-yellow-600 dark:text-yellow-500" />
                                    <div>
                                        <flux:text class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            {{ __('Tu dirección de correo no está verificada.') }}
                                        </flux:text>
                                        <flux:link class="mt-1 block text-sm font-semibold cursor-pointer text-yellow-700 hover:text-yellow-600 dark:text-yellow-400 dark:hover:text-yellow-300" wire:click.prevent="resendVerificationNotification">
                                            {{ __('Reenviar enlace de verificación') }}
                                        </flux:link>
                                    </div>
                                </div>

                                @if (session('status') === 'verification-link-sent')
                                    <flux:text class="mt-3 text-sm font-medium text-green-600 dark:text-green-400">
                                        <flux:icon name="check-circle" class="mr-1 inline size-4" />
                                        {{ __('Se ha enviado un nuevo enlace de verificación.') }}
                                    </flux:text>
                                @endif
                            </flux:card>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-4 border-t border-zinc-100 pt-4 dark:border-zinc-700/50">
                    <x-action-message class="text-sm text-green-600 dark:text-green-400 font-medium" on="profile-updated">
                        {{ __('Datos actualizados.') }}
                    </x-action-message>
                    
                    <flux:button variant="primary" type="submit" data-test="update-profile-button">
                        {{ __('Guardar Cambios') }}
                    </flux:button>
                </div>
            </flux:card>
        </form>

        @if ($this->showDeleteUser)
            <div class="mt-8">
                <livewire:pages::settings.delete-user-form />
            </div>
        @endif
    </x-pages::settings.layout>
</section>