<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6" wire:loading.attr="disabled">
            <x-filament::loading-indicator wire:loading class="h-5 w-5" />
            Save
        </x-filament::button>
    </form>
</x-filament-panels::page>