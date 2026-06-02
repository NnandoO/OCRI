@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="OCRI-UNCP" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center justify-center bg-transparent dark:bg-transparent border-none shadow-none !h-auto !w-auto !overflow-visible">
            <x-app-logo-icon class="h-10 w-auto" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="OCRI-UNCP" {{ $attributes }}>
        <x-slot name="logo" class="flex items-center justify-center bg-transparent dark:bg-transparent border-none shadow-none !h-auto !w-auto !overflow-visible">
            <x-app-logo-icon class="h-10 w-auto" />
        </x-slot>
    </flux:brand>
@endif