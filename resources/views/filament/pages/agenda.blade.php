<x-filament-panels::page>
    <style>
        .grade-horaria-container {
            display: block;
            width: 100%;
            height: 600px;
            overflow-y: auto;
            box-sizing: border-box;
            padding-right: 4px;
        }
        .linha-horaria {
            display: flex;
            align-items: stretch;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            min-height: 60px;
            transition: background-color 0.2s ease;
        }
        .dark .linha-horaria {
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .linha-horaria:hover {
            background-color: rgba(59, 130, 246, 0.01);
        }
        .dark .linha-horaria:hover {
            background-color: rgba(59, 130, 246, 0.02);
        }
        .coluna-hora {
            display: flex;
            align-items: start;
            justify-content: center;
            padding: 8px 12px;
            border-right: 1px solid rgba(0,0,0,0.05);
            width: 75px;
            flex-shrink: 0;
            background-color: rgba(0, 0, 0, 0.01);
        }
        .dark .coluna-hora {
            border-right: 1px solid rgba(255,255,255,0.05);
            background-color: rgba(255,255,255,0.01);
        }
        .area-consultas {
            position: relative;
            flex-grow: 1;
            padding: 6px 12px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 6px;
        }
        .botao-agendar-rapido {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.2s ease, background-color 0.2s ease;
            background-color: rgba(59, 130, 246, 0.01);
            border: none;
            cursor: pointer;
            z-index: 10;
        }
        .dark .botao-agendar-rapido {
            background-color: rgba(59, 130, 246, 0.02);
        }
        .area-consultas:hover .botao-agendar-rapido {
            opacity: 1;
        }
        .badge-agendar-rapido {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2563eb;
            background-color: #ffffff;
            border: 1px dashed #2563eb;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.1s ease, background-color 0.2s ease;
        }
        .dark .badge-agendar-rapido {
            color: #60a5fa;
            background-color: #1f2937;
            border: 1px dashed #60a5fa;
        }
        .badge-agendar-rapido:hover {
            transform: scale(1.1);
            background-color: #eff6ff;
        }
        .dark .badge-agendar-rapido:hover {
            background-color: #1e3a8a;
        }
        .item-consulta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            width: 100%;
        }
        .item-consulta:hover {
            transform: translateX(2px);
        }
    </style>

    @php
        $configuracao = \App\Models\GoogleConfiguracao::obterConfiguracao();
        $estaConectado = $configuracao->estaConectado();
    @endphp

    {{-- Seção de Integração com o Google Calendar --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 transition-all duration-300">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-red-50 dark:bg-red-950/30 rounded-lg text-red-600 dark:text-red-400">
                    <x-heroicon-o-link class="h-6 w-6" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Integração Google Calendar</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sincronize as consultas da clínica em tempo real com a agenda do Google.</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                {{-- Indicador de Status --}}
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold {{ $estaConectado ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400' }}">
                    <span class="h-2.5 w-2.5 rounded-full animate-pulse {{ $estaConectado ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    {{ $estaConectado ? 'Conectado' : 'Desconectado' }}
                </div>

                {{-- Ações de Integração --}}
                <div class="flex items-center gap-2">
                    {{ $this->configurarGoogleAction }}

                    @if ($estaConectado)
                        <button 
                            type="button"
                            wire:click="desconectarGoogle"
                            wire:confirm="Tem certeza de que deseja desconectar o Google Calendar? As consultas criadas não serão mais enviadas para lá."
                            class="fi-btn fi-btn-color-danger relative inline-flex items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-size-md gap-1.5 px-3 py-2 text-sm text-white bg-red-600 hover:bg-red-500 dark:bg-red-500 dark:hover:bg-red-400"
                        >
                            <x-heroicon-m-power class="h-4 w-4" />
                            <span>Desconectar</span>
                        </button>
                    @elseif (!empty($configuracao->client_id))
                        <button 
                            type="button"
                            wire:click="conectarGoogle"
                            class="fi-btn fi-btn-color-primary relative inline-flex items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-btn-size-md gap-1.5 px-3 py-2 text-sm text-white bg-primary-600 hover:bg-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400"
                        >
                            <x-heroicon-m-arrow-path class="h-4 w-4 animate-spin-slow" />
                            <span>Conectar Agenda</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Layout da Agenda em Duas Colunas (Responsivo) --}}
    <div class="w-full" style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
        
        {{-- Coluna 1: Calendário de Navegação (Esquerda / Superior) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700 flex flex-col justify-between h-fit w-full" style="flex: 1 1 290px; max-width: 350px; min-width: 260px;">
            <div>
                {{-- Cabeçalho do Mês --}}
                <div class="flex items-center justify-between mb-4">
                    <button 
                        type="button" 
                        wire:click="mesAnterior"
                        class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all"
                    >
                        <x-heroicon-s-chevron-left class="h-4 w-4" />
                    </button>
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">
                        {{ $this->obterNomeMes() }}
                    </h2>
                    <button 
                        type="button" 
                        wire:click="proximoMes"
                        class="p-2 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all"
                    >
                        <x-heroicon-s-chevron-right class="h-4 w-4" />
                    </button>
                </div>

                {{-- Dias da Semana --}}
                <div class="text-center mb-2" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.15rem;">
                    @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $diaSemana)
                        <div style="display: flex; align-items: center; justify-content: center; aspect-ratio: 1/1; min-height: 32px; margin: 0 auto; width: 100%;">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500">
                                {{ $diaSemana }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Dias do Mês --}}
                <div class="text-center" style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.25rem;">
                    @foreach($this->obterEstruturaCalendario() as $semana)
                        @foreach($semana as $dia)
                            <div style="display: flex; align-items: center; justify-content: center; aspect-ratio: 1/1; min-height: 34px; margin: 0 auto; width: 100%;">
                                @if(is_null($dia))
                                    {{-- Célula vazia --}}
                                @else
                                    @php
                                        $ehSelecionado = $dia['data'] === $dataSelecionada;
                                        
                                        $classeEstilo = 'text-xs font-semibold transition-all relative duration-200 ';
                                        
                                        if ($ehSelecionado) {
                                            $classeEstilo .= 'bg-primary-600 text-white shadow-md shadow-primary-200 dark:shadow-none scale-105';
                                        } elseif ($dia['eh_hoje']) {
                                            $classeEstilo .= 'border-2 border-primary-500 text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-950/20';
                                        } else {
                                            $classeEstilo .= 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700';
                                        }
                                    @endphp
                                    <button 
                                        type="button" 
                                        wire:click="selecionarData('{{ $dia['data'] }}')"
                                        class="{{ $classeEstilo }}"
                                        style="width: 32px !important; height: 32px !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; border-radius: 50% !important; padding: 0 !important; box-sizing: border-box !important;"
                                    >
                                        <span>{{ $dia['numero'] }}</span>
                                        
                                        {{-- Indicador de Consultas no Dia --}}
                                        @if($dia['tem_consulta'])
                                            <span class="absolute bottom-1 h-1 w-1 rounded-full {{ $ehSelecionado ? 'bg-white' : 'bg-primary-500' }}"></span>
                                        @endif
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-primary-600"></span>
                    <span>Selecionado</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full border-2 border-primary-500"></span>
                    <span>Hoje</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                    <span>Tem Consulta</span>
                </div>
            </div>
        </div>

        {{-- Coluna 2: Lista de Consultas (Direita / Inferior) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700 flex flex-col min-h-[400px] w-full" style="flex: 2 1 500px; min-width: 300px;">
            {{-- Cabeçalho da Lista --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-gray-100 dark:border-gray-700 mb-6">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Consultas de {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $this->obterConsultasDoDia()->count() }} consulta(s) agendada(s) para este dia.
                    </p>
                </div>
                <div>
                    {{ $this->criarAgendamentoAction }}
                </div>
            </div>

            {{-- Grade Horária de 24 Horas --}}
            <div class="grade-horaria-container">
                @php
                    $consultasPorHora = $this->obterConsultasDoDiaPorHora();
                @endphp
                @for($hora = 0; $hora < 24; $hora++)
                    @php
                        $consultasNaHora = $consultasPorHora[$hora];
                    @endphp
                    
                    <div class="linha-horaria">
                        {{-- Horário Lateral --}}
                        <div class="coluna-hora">
                            <span class="text-xs font-bold text-gray-400">
                                {{ sprintf('%02d:00', $hora) }}
                            </span>
                        </div>

                        {{-- Área das Consultas --}}
                        <div class="area-consultas">
                            @if(empty($consultasNaHora))
                                {{-- Linha Vazia: Botão "+" para adicionar consulta sutil --}}
                                <button 
                                    type="button"
                                    class="botao-agendar-rapido"
                                    wire:click="mountAction('criarAgendamento', { hora: {{ $hora }} })"
                                    title="Agendar para às {{ sprintf('%02d:00', $hora) }}"
                                >
                                    <div class="badge-agendar-rapido">
                                        <x-heroicon-s-plus class="h-4 w-4" />
                                    </div>
                                </button>
                            @else
                                {{-- Renderiza as consultas daquela hora --}}
                                @foreach($consultasNaHora as $consulta)
                                    @php
                                        $classeStatus = match ($consulta->status) {
                                            'confirmada' => 'bg-emerald-50 text-emerald-800 border-l-4 border-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-500',
                                            'cancelada' => 'bg-red-50 text-red-800 border-l-4 border-red-500 line-through opacity-75 dark:bg-red-950/30 dark:text-red-300 dark:border-red-500',
                                            'realizada' => 'bg-gray-50 text-gray-700 border-l-4 border-gray-400 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-500',
                                            default => 'bg-amber-50 text-amber-800 border-l-4 border-amber-500 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-500', // agendada
                                        };

                                        $limpoWhatsapp = preg_replace('/\D/', '', $consulta->obter_whatsapp_paciente);
                                    @endphp
                                    
                                    <div class="item-consulta {{ $classeStatus }}">
                                        <div class="flex items-center gap-3 flex-wrap">
                                            {{-- Horário Específico --}}
                                            <span class="font-extrabold text-primary-700 dark:text-primary-400">
                                                {{ $consulta->data_inicio->format('H:i') }} - {{ $consulta->data_fim->format('H:i') }}
                                            </span>
                                            
                                            {{-- Nome do Paciente --}}
                                            <span class="font-bold text-gray-900 dark:text-white">
                                                {{ $consulta->obter_nome_paciente }}
                                            </span>

                                            {{-- Badges --}}
                                            @if(is_null($consulta->paciente_id))
                                                <span class="inline-flex items-center rounded bg-blue-100 px-1 py-0.2 text-[9px] font-medium text-blue-700">
                                                    Novo
                                                </span>
                                            @else
                                                <a 
                                                    href="{{ route('filament.admin.resources.pacientes.edit', ['record' => $consulta->paciente_id]) }}" 
                                                    target="_blank"
                                                    class="inline-flex items-center gap-0.5 text-[9px] font-medium text-primary-600 hover:underline"
                                                    title="Ver Prontuário"
                                                >
                                                    <x-heroicon-s-folder-open class="h-3 w-3" />
                                                    <span>Prontuário</span>
                                                </a>
                                            @endif

                                            {{-- Whatsapp --}}
                                            @if(!empty($consulta->obter_whatsapp_paciente))
                                                <a 
                                                    href="https://wa.me/55{{ $limpoWhatsapp }}" 
                                                    target="_blank" 
                                                    class="text-gray-500 hover:text-emerald-600 flex items-center gap-1 font-semibold"
                                                    title="Enviar mensagem no WhatsApp"
                                                >
                                                    <svg class="h-3 w-3 text-emerald-500 inline fill-current" viewBox="0 0 24 24">
                                                        <path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-1.041-.397-1.977-1.233-.728-.651-1.22-1.455-1.364-1.7-.144-.244-.015-.378.107-.5l.342-.404c.108-.144.144-.244.216-.405.072-.162.036-.305-.018-.413-.054-.109-.487-1.173-.667-1.606-.177-.424-.356-.363-.487-.37l-.415-.008c-.144 0-.378.054-.576.27-.198.216-.757.739-.757 1.8 0 1.062.774 2.088.882 2.233.108.144 1.522 2.324 3.69 3.259.516.222.918.355 1.233.455.519.165 1.002.141 1.38.084.42-.063.857-.348 1.157-.751.3-.404.3-.75.21-.825-.09-.074-.324-.162-.648-.324z"/>
                                                    </svg>
                                                    <span>{{ $consulta->obter_whatsapp_paciente }}</span>
                                                </a>
                                            @endif

                                            {{-- Obs --}}
                                            @if(!empty($consulta->observacoes))
                                                <span class="text-gray-400 italic font-normal max-w-xs truncate" title="{{ $consulta->observacoes }}">
                                                    - "{{ $consulta->observacoes }}"
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Ações Rápidas no Bloco --}}
                                        <div class="flex items-center gap-1 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm rounded px-1.5 py-0.5 border border-black/5 dark:border-white/5 shadow-xs">
                                            @if(in_array($consulta->status, ['agendada', 'cancelada']))
                                                <button 
                                                    type="button" 
                                                    wire:click="confirmarConsulta({{ $consulta->id }})"
                                                    class="p-1 text-gray-400 hover:text-emerald-600 rounded-sm transition-all"
                                                    title="Confirmar Presença"
                                                >
                                                    <x-heroicon-o-check class="h-3.5 w-3.5" />
                                                </button>
                                            @endif

                                            @if(in_array($consulta->status, ['agendada', 'confirmada']))
                                                <button 
                                                    type="button" 
                                                    wire:click="cancelarConsulta({{ $consulta->id }})"
                                                    class="p-1 text-gray-400 hover:text-red-600 rounded-sm transition-all"
                                                    title="Cancelar Consulta"
                                                >
                                                    <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                                                </button>
                                            @endif

                                            <button 
                                                type="button" 
                                                wire:click="mountAction('editarAgendamento', { id: {{ $consulta->id }} })"
                                                class="p-1 text-gray-400 hover:text-amber-600 rounded-sm transition-all"
                                                title="Editar"
                                            >
                                                <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                                            </button>

                                            <button 
                                                type="button" 
                                                wire:click="excluirConsulta({{ $consulta->id }})"
                                                wire:confirm="Tem certeza de que deseja excluir este agendamento permanentemente? Esta ação também removerá o evento correspondente no Google Calendar."
                                                class="p-1 text-gray-400 hover:text-red-600 rounded-sm transition-all"
                                                title="Excluir Permanentemente"
                                            >
                                                <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>

    </div>

    {{-- Modais de Ações do Filament (Obrigatório para abrir modais de actions) --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
