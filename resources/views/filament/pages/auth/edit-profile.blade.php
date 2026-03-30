<x-filament-panels::page>
    <form id="form" wire:submit="save">
        {{ $this->form }}

        {{-- <x-filament::actions :actions="[$this->getSaveFormAction()]" :full-width="true" class="m-4" /> --}}
    </form>
</x-filament-panels::page>
