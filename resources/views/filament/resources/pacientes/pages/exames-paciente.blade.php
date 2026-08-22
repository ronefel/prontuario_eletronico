<div class="space-y-6">
    <!-- Cabeçalho de Ações -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-beaker class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                Exames Laboratoriais do Paciente
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                Consulte o histórico, lance novos resultados e acompanhe a evolução gráfica dos parâmetros.
            </p>
        </div>

        <div>
            {{ $this->lancarResultadoAction }}
        </div>
    </div>

    <!-- Gráfico de Evolução Temporal -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        @livewire(\App\Filament\Widgets\ExameEvolucaoChartWidget::class, ['pacienteId' => $paciente->id], key('chart-exames-' . $paciente->id))
    </div>

    <!-- Histórico de Exames -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <x-heroicon-o-clock class="w-5 h-5 text-gray-500" />
                Histórico de Resultados Lançados
            </h3>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-primary-50 dark:bg-primary-950 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                {{ count($this->registros) }} {{ count($this->registros) === 1 ? 'exame' : 'exames' }}
            </span>
        </div>

        @if ($this->registros->isEmpty())
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <x-heroicon-o-document-magnifying-glass class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3" />
                <p class="text-sm font-medium">Nenhum exame laboratorial registrado para este paciente.</p>
                <p class="text-xs text-gray-400 mt-1">Clique em "Lançar Resultado de Exame" para cadastrar o primeiro.</p>
            </div>
        @else
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($this->registros as $registro)
                    <div class="p-5 hover:bg-gray-50/50 dark:hover:bg-gray-900/30 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-600">
                                    {{ $registro->data_exame ? $registro->data_exame->format('d/m/Y') : '-' }}
                                </span>
                                <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                    {{ $registro->exame->nome ?? 'Exame' }}
                                </h4>
                            </div>
                            
                            <button 
                                type="button"
                                wire:click="excluirRegistro({{ $registro->id }})"
                                wire:confirm="Tem certeza que deseja excluir este registro de exame?"
                                class="text-xs font-medium text-rose-600 dark:text-rose-400 hover:text-rose-800 dark:hover:text-rose-300 flex items-center gap-1 self-end sm:self-auto">
                                <x-heroicon-o-trash class="w-4 h-4" />
                                Excluir
                            </button>
                        </div>

                        @if ($registro->observacoes)
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3 italic bg-gray-50 dark:bg-gray-900/50 p-2 rounded-md border border-gray-100 dark:border-gray-800">
                                <span class="font-semibold not-italic">Obs:</span> {{ $registro->observacoes }}
                            </p>
                        @endif

                        <!-- Tabela de Parâmetros -->
                        <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-100/70 dark:bg-gray-800/80 text-gray-600 dark:text-gray-300 font-semibold uppercase tracking-wider">
                                    <tr>
                                        <th class="px-4 py-2">Parâmetro</th>
                                        <th class="px-4 py-2">Resultado Encontrado</th>
                                        <th class="px-4 py-2">Faixa Ideal / Referência</th>
                                        <th class="px-4 py-2 text-right">Status de Normalidade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    @foreach ($registro->itens as $item)
                                        @php
                                            $param = $item->parametro;
                                            $unidade = $param?->unidade_medida ? ' ' . $param->unidade_medida : '';
                                            $min = $param?->valor_minimo_ideal;
                                            $max = $param?->valor_maximo_ideal;
                                            
                                            $refString = '-';
                                            if ($min !== null && $max !== null) {
                                                $refString = "{$min} a {$max}{$unidade}";
                                            } elseif ($min !== null) {
                                                $refString = ">= {$min}{$unidade}";
                                            } elseif ($max !== null) {
                                                $refString = "<= {$max}{$unidade}";
                                            }
                                        @endphp
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                            <td class="px-4 py-2.5 font-medium text-gray-900 dark:text-gray-100">
                                                {{ $param?->nome_parametro ?? 'Parâmetro' }}
                                            </td>
                                            <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-white">
                                                {{ number_format($item->valor_resultado, 2, ',', '.') }}{{ $unidade }}
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                                {{ $refString }}
                                            </td>
                                            <td class="px-4 py-2.5 text-right">
                                                @if ($item->status_normalidade === 'normal')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                                                        <span class="w-1.5 h-1.5 me-1.5 bg-emerald-500 rounded-full"></span>
                                                        Normal
                                                    </span>
                                                @elseif ($item->status_normalidade === 'baixo')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                                                        <span class="w-1.5 h-1.5 me-1.5 bg-amber-500 rounded-full"></span>
                                                        Baixo
                                                    </span>
                                                @elseif ($item->status_normalidade === 'alto')
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                                                        <span class="w-1.5 h-1.5 me-1.5 bg-rose-500 rounded-full"></span>
                                                        Alto
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                                        <span class="w-1.5 h-1.5 me-1.5 bg-blue-500 rounded-full"></span>
                                                        Informativo
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Modals de Ações do Filament -->
    <x-filament-actions::modals />
</div>
