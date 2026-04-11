<x-filament-panels::page>
  <form wire:submit="save" class="space-y-6">
    {{ $this->form }}

    <div class="flex pt-5">
      <x-filament::button type="submit" wire:loading.attr="disabled">
        <x-filament::loading-indicator wire:loading class="h-5 w-5" />
        Save
      </x-filament::button>
    </div>
  </form>
</x-filament-panels::page>
