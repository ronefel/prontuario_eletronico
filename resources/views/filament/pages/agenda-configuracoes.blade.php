<x-filament-panels::page>
    {{-- Formulário de Configuração Geral --}}
    <form wire:submit="salvar" class="space-y-6">
        {{ $this->form }}
    </form>
</x-filament-panels::page>
