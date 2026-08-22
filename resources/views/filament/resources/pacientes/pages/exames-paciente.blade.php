<div class="space-y-6">
    <!-- Cabeçalho de Ações -->
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                @svg('heroicon-o-beaker', 'w-6 h-6 text-primary-600 dark:text-primary-400')
                Exames Laboratoriais do Paciente
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Visão consolidada do último resultado por parâmetro, tendência e histórico evolutivo.
            </p>
        </div>

        <div>
            {{ $this->lancarResultadoAction }}
        </div>
    </div>

    <!-- Tabela Resumo Principal -->
    <div
        class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                @svg('heroicon-o-table-cells', 'w-5 h-5 text-gray-500')
                Resumo dos Últimos Resultados por Parâmetro
            </h3>
            <span
                class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                {{ count($this->resumoParametros) }}
                {{ count($this->resumoParametros) === 1 ? 'parâmetro' : 'parâmetros' }}
            </span>
        </div>

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
                            <th class="px-4 py-3">Faixa Ideal</th>
                            <th class="px-4 py-3">Último Resultado</th>
                            <th class="px-4 py-3">Data da Última Coleta</th>
                            <th class="px-4 py-3 text-center">Tendência</th>
                            <th class="px-4 py-3 text-right">Ação</th>
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
                                                <span
                                                    class="text-xs font-normal text-gray-500">({{ $resumo->unidade_medida }})</span>
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

                                <!-- Faixa Ideal -->
                                <td class="px-4 py-3.5 text-gray-600 dark:text-gray-300 font-medium">
                                    {{ $resumo->faixa_ideal }}
                                </td>

                                <!-- Último Resultado -->
                                <td class="px-4 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-extrabold text-gray-900 dark:text-white">
                                            {{ number_format($resumo->ultimo_valor, 2, ',', '.') }}{{ $unidade }}
                                        </span>

                                        @if ($resumo->status_normalidade === 'normal')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                <span class="w-1.5 h-1.5 me-1 bg-emerald-500 rounded-full"></span>
                                                Normal
                                            </span>
                                        @elseif ($resumo->status_normalidade === 'baixo')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                <span class="w-1.5 h-1.5 me-1 bg-amber-500 rounded-full"></span>
                                                Baixo
                                            </span>
                                        @elseif ($resumo->status_normalidade === 'alto')
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                <span class="w-1.5 h-1.5 me-1 bg-rose-500 rounded-full"></span>
                                                Alto
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                <span class="w-1.5 h-1.5 me-1 bg-blue-500 rounded-full"></span>
                                                Informativo
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Data da Coleta -->
                                <td class="px-4 py-3.5 text-gray-700 dark:text-gray-300 font-medium">
                                    {{ $resumo->data_ultima_coleta ? $resumo->data_ultima_coleta->format('d/m/Y') : '-' }}
                                </td>

                                <!-- Tendência -->
                                <td class="px-4 py-3.5 text-center">
                                    @if ($resumo->tendencia_texto !== '-')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600"
                                            title="Variação percentual em relação à coleta anterior">
                                            {{ $resumo->tendencia_texto }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 font-medium">-</span>
                                    @endif
                                </td>

                                <!-- Ação: Ver Histórico & Evolução -->
                                <td class="px-4 py-3.5 text-right">
                                    <x-filament::button
                                        type="button"
                                        wire:click="abrirHistoricoEvolucao({{ $resumo->parametro_id }})"
                                        icon="heroicon-o-chart-bar"
                                        color="primary"
                                        size="xs"
                                        outlined
                                    >
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

        <x-filament::modal
            id="modal-historico-evolucao"
            width="4xl"
            display-classes="block"
        >
            <x-slot name="heading">
                <div class="flex items-center gap-2 text-lg font-bold text-gray-900 dark:text-white">
                    @svg('heroicon-o-chart-bar', 'w-5 h-5 text-primary-600')
                    <span>{{ $hParam->nome_parametro }}</span>
                </div>
            </x-slot>

            <x-slot name="description">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    <span class="text-primary-600 font-semibold uppercase">{{ $hParam->exame->nome ?? 'Exame' }}</span>
                    &bull; Faixa Ideal de Referência: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $hFaixa }}</span>
                </div>
            </x-slot>

            <!-- Corpo do Modal -->
            <div class="space-y-6">
                <!-- Widget do Gráfico de Linha -->
                <div
                    class="bg-gray-50 dark:bg-gray-900/50 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
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
                                            {{ number_format($hItem->valor_resultado, 2, ',', '.') }}{{ $hUnidade }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($hItem->status_normalidade === 'normal')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                    Normal
                                                </span>
                                            @elseif ($hItem->status_normalidade === 'baixo')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                    Baixo
                                                </span>
                                            @elseif ($hItem->status_normalidade === 'alto')
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                    Alto
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                    Informativo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400 italic">
                                            {{ $hItem->registro->observacoes ?: '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                {{ ($this->editarItemAction)(['itemId' => $hItem->id]) }}

                                                <x-filament::icon-button
                                                    type="button"
                                                    wire:click="excluirItem({{ $hItem->id }})"
                                                    wire:confirm="Tem certeza que deseja excluir esta medição?"
                                                    icon="heroicon-o-trash"
                                                    color="danger"
                                                    size="sm"
                                                    tooltip="Excluir medição"
                                                />
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
