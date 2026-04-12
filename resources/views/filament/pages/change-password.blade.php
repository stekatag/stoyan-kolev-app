<x-filament-panels::page>
  <form wire:submit="save">
    {{ $this->form }}

    <div style="margin-top: 1.25rem">
      <x-filament::button type="submit" wire:loading.attr="disabled">
        <x-filament::loading-indicator wire:loading class="h-5 w-5" />
        Save
      </x-filament::button>
    </div>
  </form>
</x-filament-panels::page>
