<x-filament-panels::page>
    <div x-data="{
        pesquisa: @entangle('pesquisa'),
        focarProximo(event) {
            let inputs = Array.from(document.querySelectorAll('input[data-contada]'));
            let index = inputs.indexOf(event.target);
            if (index > -1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
                inputs[index + 1].select();
            }
        }
    }" class="space-y-6">

        <div
            class="flex items-center justify-between gap-4 p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
            <div class="flex-1 max-w-md">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input type="text" placeholder="Pesquisar por lote ou produto (ou use o leitor)..."
                        wire:model.live.debounce.300ms="pesquisa"
                        x-on:keydown.enter.prevent="document.querySelector('input[data-contada]')?.focus()?.select()" />
                </x-filament::input.wrapper>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status:</span>
                @if ($record->status === 'pendente')
                    <x-filament::badge iconSize="md" color="warning" icon="heroicon-s-clock">
                        Pendente
                    </x-filament::badge>
                @else
                    <x-filament::badge iconSize="md" color="success" icon="heroicon-s-check-badge">
                        Aprovado
                    </x-filament::badge>
                @endif
            </div>
        </div>

        <div
            class="overflow-x-auto bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <table class="w-full text-left divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Produto / Lote</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Vencimento</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center text-gray-900 dark:text-white">Registrada
                        </th>
                        <th class="px-4 py-3 text-sm font-semibold text-center text-gray-900 dark:text-white w-32">
                            Contada</th>
                        <th class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white">Diferença / Motivo
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->itensFiltrados as $id => $item)
                        <tr wire:key="row-{{ $id }}"
                            class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-950 dark:text-white" title="{{ $item['produto'] }}">
                                    {{ $item['produto'] }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Lote: <span
                                        class="font-mono">{{ $item['numero_lote'] }}</span></div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $item['vencimento'] }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center font-mono text-gray-600 dark:text-gray-400">
                                {{ $item['registrada'] }}
                            </td>
                            <td class="px-4 py-3">
                                <x-filament::input.wrapper :valid="true">
                                    <x-filament::input type="number" class="text-center font-bold"
                                        value="{{ $item['contada'] }}" data-contada="{{ $id }}"
                                        :disabled="$record->status !== 'pendente'"
                                        x-on:blur="$wire.salvarContagem({{ $id }}, $event.target.value)"
                                        x-on:keydown.enter.prevent="focarProximo($event)" x-init="$loop.first ? ($refs.primeiroInput = $el) : null" />
                                </x-filament::input.wrapper>
                            </td>
                            <td class="px-4 py-3" width='400px'>
                                @php
                                    $diferenca = ($item['contada'] ?? 0) - $item['registrada'];
                                @endphp

                                <div class="flex flex-col gap-2">
                                    @if ($diferenca != 0)
                                        <div
                                            class="flex items-center gap-1.5 text-xs font-bold {{ $diferenca > 0 ? 'text-success-600' : 'text-danger-600' }}">
                                            @if ($diferenca > 0)
                                                <x-filament::icon icon="heroicon-m-plus-circle" class="w-4 h-4" />
                                                Sobra: {{ abs($diferenca) }}
                                            @else
                                                <x-filament::icon icon="heroicon-m-minus-circle" class="w-4 h-4" />
                                                Falta: {{ abs($diferenca) }}
                                            @endif
                                        </div>

                                        <x-filament::input.wrapper>
                                            <x-filament::input type="text" placeholder="Por que a diferença?"
                                                class="text-xs" value="{{ $item['motivo'] }}" :disabled="$record->status !== 'pendente'"
                                                x-on:blur="$wire.salvarMotivo({{ $id }}, $event.target.value)" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-success-700 bg-success-50 rounded-md dark:bg-success-900/30 dark:text-success-400">
                                            <x-filament::icon icon="heroicon-m-check" class="w-3 h-3 mr-1" />
                                            Correto
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-500 dark:text-gray-400 italic">
                                Nenhum lote encontrado para esta contagem.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</x-filament-panels::page>
