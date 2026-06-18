<x-filament-panels::page>
    <style>
        .grade-horaria-container {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        .linha-horaria {
            display: flex;
            align-items: stretch;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: background-color 0.2s ease;
        }

        .dark .linha-horaria {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .linha-horaria:hover {
            background-color: rgba(59, 130, 246, 0.01);
        }

        .dark .linha-horaria:hover {
            background-color: rgba(59, 130, 246, 0.02);
        }

        .coluna-hora {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px 0px;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            width: 50px;
            flex-shrink: 0;
            background-color: rgba(0, 0, 0, 0.01);
        }

        .dark .coluna-hora {
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            background-color: rgba(255, 255, 255, 0.01);
        }

        .area-consultas {
            position: relative;
            flex-grow: 1;
            min-width: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
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
            gap: 8px;
            border-radius: 0px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            width: 100%;
            min-height: 32px;
            padding-left: 8px;
        }
    </style>



    {{-- Layout da Agenda em Duas Colunas (Responsivo) --}}
    <div class="w-full" style="display: flex; flex-wrap: wrap; gap: 1.5rem;">

        {{-- Coluna 1: Calendário de Navegação (Esquerda / Superior) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-100 dark:border-gray-700 flex flex-col justify-between h-fit w-full"
            style="flex: 1 1 290px; max-width: 350px; min-width: 260px;">
            <div>
                {{-- Cabeçalho do Mês --}}
                <div class="flex items-center justify-between">
                    <x-filament::icon-button wire:click="mesAnterior" size="sm" color="gray"
                        icon="heroicon-o-chevron-left" />
                    <h2 class="text-sm font-bold text-gray-800 dark:text-white">
                        {{ $this->obterNomeMes() }}
                    </h2>
                    <x-filament::icon-button wire:click="proximoMes" size="sm" color="gray"
                        icon="heroicon-o-chevron-right" />
                </div>

                {{-- Dias da Semana --}}
                <div class="text-center mb-2"
                    style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.15rem;">
                    @foreach (['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $diaSemana)
                        <div
                            style="display: flex; align-items: center; justify-content: center; aspect-ratio: 1/1; min-height: 32px; margin: 0 auto; width: 100%;">
                            <span class="text-[11px] font-bold text-gray-400 dark:text-gray-500">
                                {{ $diaSemana }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Dias do Mês --}}
                <div class="text-center"
                    style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 0.25rem;">
                    @foreach ($this->obterEstruturaCalendario() as $semana)
                        @foreach ($semana as $dia)
                            <div
                                style="display: flex; align-items: center; justify-content: center; aspect-ratio: 1/1; min-height: 34px; margin: 0 auto; width: 100%;">
                                @if (is_null($dia))
                                    {{-- Célula vazia --}}
                                @else
                                    @php
                                        $ehSelecionado = $dia['data'] === $dataSelecionada;
                                        $corBotao = $ehSelecionado
                                            ? 'primary'
                                            : ($dia['eh_passado']
                                                ? 'gray'
                                                : ($dia['disponivel']
                                                    ? 'success'
                                                    : 'danger'));
                                        $outlinedBotao = $dia['eh_hoje'] && !$ehSelecionado;
                                    @endphp
                                    <x-filament::button wire:click="selecionarData('{{ $dia['data'] }}')"
                                        :color="$corBotao" :outlined="$outlinedBotao" size="sm" :loading-indicator="false"
                                        :badge="$dia['total_consultas'] > 0 ? $dia['total_consultas'] : null" badge-color="gray" style="width: 32px;">
                                        {{ $dia['numero'] }}
                                    </x-filament::button>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <div
                class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-primary-600"></span>
                    <span>Selecionado</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-success-600"></span>
                    <span>Disponível</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full bg-danger-600"></span>
                    <span>Sem Vagas</span>
                </div>
            </div>
        </div>

        {{-- Coluna 2: Lista de Consultas (Direita / Inferior) --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col w-full"
            style="flex: 2 1 500px; min-width: 300px;">
            {{-- Cabeçalho da Lista --}}
            <div
                class="flex flex-row items-center justify-between gap-4 p-2 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($dataSelecionada)->translatedFormat('d \d\e F \d\e Y') }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $this->obterConsultasDoDia()->count() }} consulta(s) agendada(s) para este dia.
                    </p>
                </div>
                <div>
                    {{ $this->criarAgendamentoAction }}
                </div>
            </div>

            {{-- Grade Horária Dinâmica --}}
            <div class="grade-horaria-container">
                @php
                    $slotsGrade = $this->obterSlotsGrade();
                @endphp
                @foreach ($slotsGrade as $slot)
                    @php
                        $horaSlot = $slot['hora'];
                        $horaInt = (int) explode(':', $horaSlot)[0];
                        $consultasNoSlot = $slot['consultas'];
                    @endphp

                    <div class="linha-horaria">
                        {{-- Horário Lateral --}}
                        <div class="coluna-hora">
                            <span class="text-xs font-bold">
                                {{ $horaSlot }}
                            </span>
                        </div>

                        {{-- Área das Consultas --}}
                        <div class="area-consultas">
                            @if (!empty($consultasNoSlot))
                                {{-- Renderiza as consultas daquele slot --}}
                                @foreach ($consultasNoSlot as $consulta)
                                    @php
                                        $classeStatus = match ($consulta->status) {
                                            'confirmada'
                                                => 'bg-emerald-50 text-emerald-800 border-l-4 border-emerald-500 dark:bg-emerald-950/30 dark:text-emerald-300 dark:border-emerald-500',
                                            'cancelada'
                                                => 'bg-red-50 text-red-800 border-l-4 border-red-500 line-through opacity-75 dark:bg-red-950/30 dark:text-red-300 dark:border-red-500',
                                            'realizada'
                                                => 'bg-gray-50 text-gray-700 border-l-4 border-gray-400 dark:bg-gray-800 dark:text-gray-400 dark:border-gray-500',
                                            default
                                                => 'bg-amber-50 text-amber-800 border-l-4 border-amber-500 dark:bg-amber-950/30 dark:text-amber-300 dark:border-amber-500', // agendada
                                        };

                                        $limpoWhatsapp = preg_replace('/\D/', '', $consulta->obter_whatsapp_paciente);
                                    @endphp

                                    <div
                                        class="item-consulta {{ $classeStatus }} flex items-center justify-between gap-2">
                                        <div class="flex-grow min-w-0 py-1">
                                            <div class="flex items-center gap-1 flex-wrap">
                                                {{-- Horário Específico --}}
                                                <span class="text-primary-700 dark:text-primary-400 whitespace-nowrap">
                                                    {{ $consulta->data_inicio->format('H:i') }}-{{ $consulta->data_fim->format('H:i') }}
                                                </span>

                                                {{-- Nome do Paciente --}}
                                                <span
                                                    class="font-bold text-gray-900 dark:text-white truncate max-w-[136px] sm:max-w-[250px]"
                                                    title="{{ $consulta->obter_nome_paciente }}">
                                                    {{ $consulta->obter_nome_paciente }}
                                                </span>

                                                {{-- Badges --}}
                                                @if (is_null($consulta->paciente_id))
                                                    <span
                                                        class="inline-flex items-center rounded bg-blue-100 px-1 py-0.2 text-[9px] font-medium text-blue-700">
                                                        Novo
                                                    </span>
                                                @else
                                                    <a href="{{ route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $consulta->paciente_id]) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-0.5 text-[9px] font-medium text-primary-600 hover:underline"
                                                        title="Ver Prontuário">
                                                        <x-heroicon-s-folder-open class="h-3 w-3" />
                                                        <span>Prontuário</span>
                                                    </a>
                                                @endif

                                                {{-- Whatsapp --}}
                                                @if (!empty($consulta->obter_whatsapp_paciente))
                                                    <a href="https://wa.me/55{{ $limpoWhatsapp }}" target="_blank"
                                                        class="text-success-600 flex items-center gap-0.5 font-semibold text-[11px] hover:underline"
                                                        title="Enviar mensagem no WhatsApp">
                                                        <span>{{ $consulta->obter_whatsapp_paciente }}</span>
                                                    </a>
                                                @endif
                                            </div>

                                            {{-- Observação em linha própria abaixo --}}
                                            @if (!empty($consulta->observacoes))
                                                <div class="text-[11px] text-gray-500 dark:text-gray-400 italic break-words whitespace-normal"
                                                    title="{{ $consulta->observacoes }}">
                                                    {{ $consulta->observacoes }}
                                                </div>
                                            @endif
                                        </div>

                                        {{-- Dropdown de Ações --}}
                                        <div class="flex-shrink-0">
                                            <x-filament::dropdown placement="bottom-end">
                                                <x-slot name="trigger">
                                                    <x-filament::icon-button icon="heroicon-m-ellipsis-vertical"
                                                        color="gray" size="sm" label="Opções" />
                                                </x-slot>

                                                <x-filament::dropdown.list>
                                                    @if (in_array($consulta->status, ['agendada', 'cancelada']))
                                                        <x-filament::dropdown.list.item
                                                            wire:click="confirmarConsulta({{ $consulta->id }})"
                                                            icon="heroicon-m-check" color="success">
                                                            Confirmar Presença
                                                        </x-filament::dropdown.list.item>
                                                    @endif

                                                    @if (in_array($consulta->status, ['agendada', 'confirmada']))
                                                        <x-filament::dropdown.list.item
                                                            wire:click="cancelarConsulta({{ $consulta->id }})"
                                                            icon="heroicon-m-x-mark" color="danger">
                                                            Cancelar Consulta
                                                        </x-filament::dropdown.list.item>
                                                    @endif

                                                    <x-filament::dropdown.list.item
                                                        wire:click="mountAction('editarAgendamento', { id: {{ $consulta->id }} })"
                                                        icon="heroicon-m-pencil-square" color="primary">
                                                        Editar
                                                    </x-filament::dropdown.list.item>
                                                </x-filament::dropdown.list>
                                            </x-filament::dropdown>
                                        </div>
                                    </div>
                                @endforeach
                            @elseif (!empty($slot['continua_consulta']))
                                {{-- Slot ocupado por continuação de consulta --}}
                                <div class="text-gray-500 dark:text-gray-400 select-none">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 italic ml-2">
                                        {{ $slot['continua_consulta']->data_fim->format('H:i') }}
                                        {{ $slot['continua_consulta']->obter_nome_paciente }}
                                    </span>
                                </div>
                            @else
                                {{-- Linha Vazia: Botão "+" para adicionar consulta sutil --}}
                                <button type="button" class="botao-agendar-rapido"
                                    wire:click="mountAction('criarAgendamento', { hora: '{{ $horaSlot }}' })"
                                    title="Agendar para às {{ $horaSlot }}">
                                    <div class="badge-agendar-rapido">
                                        <x-heroicon-s-plus class="h-4 w-4" />
                                    </div>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Modais de Ações do Filament (Obrigatório para abrir modais de actions) --}}
    <x-filament-actions::modals />
</x-filament-panels::page>
