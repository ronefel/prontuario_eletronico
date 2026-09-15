<div class="space-y-6">
    <!-- Cabeçalho de Ações -->
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                @svg('heroicon-o-beaker', 'w-6 h-6 text-primary-600 dark:text-primary-400')
                Exames Laboratoriais do Paciente
            </h2>
        </div>

        <div class="flex items-center gap-3">
            {{ $this->imprimirLaudoEvolutivoAction }}
            {{ $this->lancarResultadoAction }}
        </div>
    </div>

    <!-- Tabela Resumo Principal -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">


        @if ($this->resumoParametros->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                @svg('heroicon-o-document-magnifying-glass', 'w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3')
                <p class="text-sm font-medium">Nenhum resultado de exame registrado para este paciente.</p>
                <p class="text-xs text-gray-400 mt-1">Clique em "Lançar Resultado de Exame" no topo para incluir o
                    primeiro.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-gray-50 dark:bg-gray-900/60 text-gray-600 dark:text-gray-300 font-semibold uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">Exame / Parâmetro</th>
                            <th class="px-4 py-3">Resultado Atual</th>
                            <th class="px-4 py-3">Resultado Anterior</th>
                            <th class="px-4 py-3">Valor Ideal</th>
                            <th class="px-4 py-3 text-right"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach ($this->resumoParametros as $resumo)
                            @php
                                $unidade = $resumo->unidade_medida ? ' ' . $resumo->unidade_medida : '';
                            @endphp
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/50 transition-colors">
                                <!-- Exame / Parâmetro -->
                                <td class="px-4 py-3.5">
                                    @if ($resumo->is_exame_simples)
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $resumo->exame_nome }}
                                            @if ($resumo->unidade_medida)
                                                <span class="text-xs font-normal text-gray-500">({{ $resumo->unidade_medida }})</span>
                                            @endif
                                        </span>
                                    @else
                                        <div class="flex flex-col">
                                            <span
                                                class="text-[10px] font-semibold text-primary-600 dark:text-primary-400 uppercase tracking-wide">
                                                {{ $resumo->exame_nome }}
                                            </span>
                                            <span class="text-sm font-bold text-gray-900 dark:text-white">
                                                {{ $resumo->parametro_nome }}
                                                @if ($resumo->unidade_medida)
                                                    <span
                                                        class="text-xs font-normal text-gray-500">({{ $resumo->unidade_medida }})</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Resultado Atual -->
                                <td class="px-4 py-3.5">
                                    <div class="flex flex-col gap-1">
                                        @if ($resumo->data_ultima_coleta)
                                            <div
                                                class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                                <span>{{ $resumo->data_ultima_coleta->format('d/m/Y') }}</span>
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                {{ $this->formatarValorResultado($resumo->ultimo_valor) }}{{ $unidade }}
                                            </span>

                                            @if ($resumo->status_normalidade === 'normal')
                                                <x-filament::badge color="success" size="sm">
                                                    Normal
                                                </x-filament::badge>
                                            @elseif ($resumo->status_normalidade === 'baixo')
                                                <x-filament::badge color="danger" size="sm">
                                                    Baixo
                                                </x-filament::badge>
                                            @elseif ($resumo->status_normalidade === 'alto')
                                                <x-filament::badge color="danger" size="sm">
                                                    Alto
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="info" size="sm">
                                                    Informativo
                                                </x-filament::badge>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Resultado Anterior -->
                                <td class="px-4 py-3.5">
                                    @if ($resumo->penultimo_valor !== null)
                                        <div class="flex flex-col gap-1">
                                            @if ($resumo->data_penultima_coleta)
                                                <div
                                                    class="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                                                    <span>{{ $resumo->data_penultima_coleta->format('d/m/Y') }}</span>
                                                </div>
                                            @endif
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $this->formatarValorResultado($resumo->penultimo_valor) }}{{ $unidade }}
                                                </span>

                                                @if ($resumo->status_penultimo === 'normal')
                                                    <x-filament::badge color="success" size="sm">
                                                        Normal
                                                    </x-filament::badge>
                                                @elseif ($resumo->status_penultimo === 'baixo')
                                                    <x-filament::badge color="danger" size="sm">
                                                        Baixo
                                                    </x-filament::badge>
                                                @elseif ($resumo->status_penultimo === 'alto')
                                                    <x-filament::badge color="danger" size="sm">
                                                        Alto
                                                    </x-filament::badge>
                                                @elseif ($resumo->status_penultimo === 'informativo')
                                                    <x-filament::badge color="info" size="sm">
                                                        Informativo
                                                    </x-filament::badge>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-gray-400 font-medium">-</span>
                                    @endif
                                </td>

                                <!-- Faixa Ideal -->
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 font-medium">
                                    {{ $resumo->faixa_ideal }}
                                </td>

                                <!-- Ação: Ver Histórico & Evolução -->
                                <td class="px-4 py-3.5 text-right">
                                    <x-filament::button type="button"
                                        wire:click="abrirHistoricoEvolucao({{ $resumo->parametro_id }})"
                                        icon="heroicon-o-chart-bar" color="primary" size="xs" outlined>
                                        Histórico & Evolução
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Modal NATIVO de Histórico e Evolução do Filament -->
    @if ($this->selectedParametroId && $this->historicoParametroSelecionado)
        @php
            $hData = $this->historicoParametroSelecionado;
            $hParam = $hData->parametro;
            $hUnidade = $hParam->unidade_medida ? " ({$hParam->unidade_medida})" : '';
            $hMin = $hParam->valor_minimo_ideal;
            $hMax = $hParam->valor_maximo_ideal;

            $hFaixa = '-';
            if ($hMin !== null && $hMax !== null) {
                $hFaixa = "{$hMin} a {$hMax}{$hUnidade}";
            } elseif ($hMin !== null) {
                $hFaixa = ">= {$hMin}{$hUnidade}";
            } elseif ($hMax !== null) {
                $hFaixa = "<= {$hMax}{$hUnidade}";
            }
        @endphp

        <x-filament::modal id="modal-historico-evolucao" width="4xl" display-classes="block">
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                    @svg('heroicon-o-chart-bar', 'w-5 h-5 text-primary-600')
                    <span>{{ $hParam->nome_parametro }}</span>
                </div>
            </x-slot>

            <x-slot name="description">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="text-primary-600 font-semibold uppercase">{{ $hParam->exame->nome ?? 'Exame' }}</span>
                    &bull; Valor Ideal de Referência: <span
                        class="font-bold text-gray-700 dark:text-gray-300">{{ $hFaixa }}</span>
                </div>
            </x-slot>

            <!-- Corpo do Modal -->
            <div class="space-y-6">
                <!-- Widget do Gráfico de Linha -->
                <div class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    @livewire(
                        \App\Filament\Widgets\ExameEvolucaoChartWidget::class,
                        [
                            'pacienteId' => $paciente->id,
                            'parametroId' => $selectedParametroId,
                        ],
                        key('chart-modal-' . $selectedParametroId)
                    )
                </div>

                <!-- Tabela de Registros Históricos -->
                <div class="space-y-3">
                    <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="bg-gray-100/70 dark:bg-gray-900 text-gray-600 dark:text-gray-300 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-2.5">Data da Coleta</th>
                                    <th class="px-4 py-2.5">Resultado</th>
                                    <th class="px-4 py-2.5">Status</th>
                                    <th class="px-4 py-2.5">Observações</th>
                                    <th class="px-4 py-2.5 text-right">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                @foreach ($hData->itens as $hItem)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/40 transition-colors">
                                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                            {{ $hItem->registro->data_exame ? $hItem->registro->data_exame->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="px-4 py-3 font-extrabold text-gray-900 dark:text-white">
                                            {{ $this->formatarValorResultado($hItem->valor_resultado) }}{{ $hUnidade }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($hItem->status_normalidade === 'normal')
                                                <x-filament::badge color="success" size="sm">
                                                    Normal
                                                </x-filament::badge>
                                            @elseif ($hItem->status_normalidade === 'baixo')
                                                <x-filament::badge color="danger" size="sm">
                                                    Baixo
                                                </x-filament::badge>
                                            @elseif ($hItem->status_normalidade === 'alto')
                                                <x-filament::badge color="danger" size="sm">
                                                    Alto
                                                </x-filament::badge>
                                            @else
                                                <x-filament::badge color="info" size="sm">
                                                    Informativo
                                                </x-filament::badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 italic">
                                            {{ $hItem->registro->observacoes ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                {{ ($this->editarItemAction)(['itemId' => $hItem->id]) }}

                                                <x-filament::icon-button type="button"
                                                    wire:click="excluirItem({{ $hItem->id }})"
                                                    wire:confirm="Tem certeza que deseja excluir esta medição?"
                                                    icon="heroicon-o-trash" color="danger" size="sm"
                                                    tooltip="Excluir medição" />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </x-filament::modal>
    @endif

    <!-- Modals de Ações do Filament -->
    <x-filament-actions::modals />
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.addEventListener('openUrlInNewTab', function(event) {
                const url = event.detail?.[0]?.url || event.detail?.url;
                if (url) {
                    window.open(url, '_blank');
                }
            });
        });
    </script>
@endpush