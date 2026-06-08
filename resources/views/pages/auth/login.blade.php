<x-layouts::auth :title="__('Iniciar sesion')">
    <div class="flex flex-col gap-6">
        <div class="mb-2 text-center">
    <h1 class="text-3xl font-extrabold text-zinc-200 dark:text-zinc-400">
        {{ __('Inicia sesion') }}
    </h1>
    
    <p class="mt-2 text-sm text-zinc-200 dark:text-zinc-400">
        {{ __('Ingresa tu correo y contraseña para iniciar sesion') }}
    </p>
</div>

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electronico')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="correo@ejemplo.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Contraseña')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Contraseña')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('¿Olvidaste tu contraseña?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Recordarme')" :checked="old('remember')" />

            <div class="flex items-center justify-end text-zinc-100">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Iniciar sesion') }}
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
