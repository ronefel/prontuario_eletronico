<?php

namespace App\Filament\Pages;

use App\Models\Agenda as ModelAgenda;
use App\Models\AgendaConfiguracao;
use App\Models\Paciente;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Filament\Support\RawJs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Agenda extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $title = '';

    protected static ?string $navigationLabel = 'Agenda';

    protected string $view = 'filament.pages.agenda';

    public string $dataSelecionada;

    public int $mesSelecionado;

    public int $anoSelecionado;

    public function mount()
    {
        $this->dataSelecionada = now()->format('Y-m-d');
        $this->mesSelecionado = now()->month;
        $this->anoSelecionado = now()->year;
    }

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    /**
     * Retorna a lista de consultas do dia selecionado.
     */
    public function obterConsultasDoDia(): Collection
    {
        return ModelAgenda::query()
            ->with('paciente')
            ->whereDate('data_inicio', $this->dataSelecionada)
            ->where('status', '!=', 'cancelada')
            ->orderBy('data_inicio')
            ->get();
    }

    /**
     * Gera os slots horários da agenda com as consultas agrupadas.
     * Expande dinamicamente caso existam consultas fora do horário comercial configurado.
     */
    public function obterSlotsGrade(): array
    {
        $configuracao = AgendaConfiguracao::obterConfiguracao();

        $horaInicioConfig = $configuracao->hora_inicio ?: '08:00';
        $horaFimConfig = $configuracao->hora_fim ?: '18:00';
        $intervalo = (int) ($configuracao->intervalo ?: 30);

        // Converte string 'HH:MM' para minutos desde a meia-noite
        $converterParaMinutos = function (string $horaString): int {
            [$h, $m] = explode(':', $horaString);

            return ((int) $h * 60) + (int) $m;
        };

        $minutosInicio = $converterParaMinutos($horaInicioConfig);
        $minutosFim = $converterParaMinutos($horaFimConfig);

        $consultas = $this->obterConsultasDoDia();

        // Expande os limites inicial/final da grade se houver consultas fora do horário comercial
        foreach ($consultas as $consulta) {
            $minutosConsultaInicio = ($consulta->data_inicio->hour * 60) + $consulta->data_inicio->minute;
            $duracao = abs($consulta->data_inicio->diffInMinutes($consulta->data_fim));
            $minutosConsultaFim = $minutosConsultaInicio + $duracao;

            if ($minutosConsultaInicio < $minutosInicio) {
                // Alinha o início expandido ao intervalo
                $minutosInicio = floor($minutosConsultaInicio / $intervalo) * $intervalo;
            }
            if ($minutosConsultaFim > $minutosFim) {
                // Alinha o fim expandido ao intervalo
                $minutosFim = ceil($minutosConsultaFim / $intervalo) * $intervalo;
            }
        }

        // Garante que minutosInicio < minutosFim
        if ($minutosInicio >= $minutosFim) {
            $minutosFim = $minutosInicio + $intervalo;
        }

        // Calcula limites da pausa
        $pausaInicio = null;
        $pausaFim = null;
        if (! empty($configuracao->pausa_inicio) && ! empty($configuracao->pausa_fim)) {
            $pausaInicio = $converterParaMinutos($configuracao->pausa_inicio);
            $pausaFim = $converterParaMinutos($configuracao->pausa_fim);
        }

        // Gera a lista de slots de minutos, omitindo os que caem na pausa
        $slotsMinutos = [];
        for ($m = $minutosInicio; $m < $minutosFim; $m += $intervalo) {
            if ($pausaInicio !== null && $m >= $pausaInicio && $m < $pausaFim) {
                continue;
            }
            $slotsMinutos[] = $m;
        }

        // Inicializa a grade com slots vazios
        $grade = [];
        foreach ($slotsMinutos as $minutosSlot) {
            $horaLegivel = sprintf('%02d:%02d', floor($minutosSlot / 60), $minutosSlot % 60);
            $grade[$minutosSlot] = [
                'hora' => $horaLegivel,
                'minutos' => $minutosSlot,
                'consultas' => [],
                'continua_consulta' => null,
            ];
        }

        // Agrupa as consultas em cada slot e também marca as continuações de consulta
        foreach ($consultas as $consulta) {
            $minutosConsultaInicio = ($consulta->data_inicio->hour * 60) + $consulta->data_inicio->minute;

            // Encontra o slot correspondente para início da consulta
            $slotEscolhido = null;
            foreach ($slotsMinutos as $minutosSlot) {
                if ($minutosSlot <= $minutosConsultaInicio) {
                    $slotEscolhido = $minutosSlot;
                } else {
                    break;
                }
            }

            if ($slotEscolhido === null) {
                $slotEscolhido = $slotsMinutos[0];
            }

            $grade[$slotEscolhido]['consultas'][] = $consulta;
        }

        // Identifica slots que continuam uma consulta em andamento
        foreach ($grade as $minutosSlot => &$slotInfo) {
            foreach ($consultas as $consulta) {
                $minutosConsultaInicio = ($consulta->data_inicio->hour * 60) + $consulta->data_inicio->minute;
                $duracao = abs($consulta->data_inicio->diffInMinutes($consulta->data_fim));
                $minutosConsultaFim = $minutosConsultaInicio + $duracao;

                // Um slot é continuação se for estritamente após o início e estritamente antes do fim da consulta
                if ($minutosSlot > $minutosConsultaInicio && $minutosSlot < $minutosConsultaFim) {
                    $slotInfo['continua_consulta'] = $consulta;
                    break; // Um slot só pode ser continuação de uma consulta
                }
            }
        }
        unset($slotInfo);

        return array_values($grade);
    }

    /**
     * Retorna a contagem de consultas por dia no mês selecionado.
     * Usado para exibir o badge com total de agendamentos no calendário.
     */
    public function obterContagemConsultasPorDia(): array
    {
        return ModelAgenda::query()
            ->whereYear('data_inicio', $this->anoSelecionado)
            ->whereMonth('data_inicio', $this->mesSelecionado)
            ->where('status', '!=', 'cancelada')
            ->select(DB::raw('DATE(data_inicio) as data'), DB::raw('COUNT(*) as total'))
            ->groupBy('data')
            ->pluck('total', 'data')
            ->toArray();
    }

    /**
     * Avança para o próximo mês no calendário.
     */
    public function proximoMes()
    {
        if ($this->mesSelecionado === 12) {
            $this->mesSelecionado = 1;
            $this->anoSelecionado++;
        } else {
            $this->mesSelecionado++;
        }
    }

    /**
     * Retrocede para o mês anterior no calendário.
     */
    public function mesAnterior()
    {
        if ($this->mesSelecionado === 1) {
            $this->mesSelecionado = 12;
            $this->anoSelecionado--;
        } else {
            $this->mesSelecionado--;
        }
    }

    /**
     * Define a data selecionada a partir do clique no calendário.
     */
    public function selecionarData(string $data)
    {
        $this->dataSelecionada = $data;
    }

    /**
     * Retorna o nome do mês atual selecionado em português.
     */
    public function obterNomeMes(): string
    {
        $meses = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro',
        ];

        return $meses[$this->mesSelecionado].' de '.$this->anoSelecionado;
    }

    /**
     * Gera a estrutura de semanas e dias para renderizar o calendário.
     */
    public function obterEstruturaCalendario(): array
    {
        $primeiroDiaMes = Carbon::create($this->anoSelecionado, $this->mesSelecionado, 1);
        $diasNoMes = $primeiroDiaMes->daysInMonth;

        // dayOfWeek no Carbon: 0 (domingo) a 6 (sábado)
        $diaSemanaInicio = $primeiroDiaMes->dayOfWeek;

        $estrutura = [];
        $semana = array_fill(0, 7, null);

        // Preenche os espaços vazios do início do mês
        for ($i = 0; $i < $diaSemanaInicio; $i++) {
            $semana[$i] = null;
        }

        $diaSemanaAtual = $diaSemanaInicio;

        $contagemPorDia = $this->obterContagemConsultasPorDia();
        $configuracao = AgendaConfiguracao::obterConfiguracao();
        $limiteDia = $configuracao->obterLimiteDia();
        $hoje = now()->format('Y-m-d');

        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $dataCompleta = Carbon::create($this->anoSelecionado, $this->mesSelecionado, $dia)->format('Y-m-d');
            $totalConsultas = $contagemPorDia[$dataCompleta] ?? 0;
            $ehPassado = $dataCompleta < $hoje;
            $disponivel = ! $ehPassado && ($totalConsultas < $limiteDia);

            $semana[$diaSemanaAtual] = [
                'numero' => $dia,
                'data' => $dataCompleta,
                'eh_hoje' => $dataCompleta === $hoje,
                'eh_passado' => $ehPassado,
                'total_consultas' => $totalConsultas,
                'disponivel' => $disponivel,
            ];

            if ($diaSemanaAtual === 6) {
                $estrutura[] = $semana;
                $semana = array_fill(0, 7, null);
                $diaSemanaAtual = 0;
            } else {
                $diaSemanaAtual++;
            }
        }

        // Adiciona a última semana caso tenha restado algum dia
        if ($diaSemanaAtual > 0) {
            $estrutura[] = $semana;
        }

        return $estrutura;
    }

    /**
     * Retorna o schema do formulário de agendamento reutilizado por criar e editar.
     */
    protected function obterSchemaAgendamento(bool $editando = false): array
    {
        return [
            Grid::make(2)->schema([
                Select::make('paciente_id')
                    ->label('Paciente Cadastrado')
                    ->options(Paciente::pluck('nome', 'id'))
                    ->searchable()
                    ->preload()
                    ->placeholder('Selecione um paciente (opcional)')
                    ->reactive()
                    ->columnSpan(2),

                TextInput::make('nome_paciente')
                    ->label('Nome do Novo Paciente')
                    ->required(fn (Get $get) => empty($get('paciente_id')))
                    ->hidden(fn (Get $get) => ! empty($get('paciente_id')))
                    ->placeholder('Digite o nome do paciente')
                    ->columnSpan(1),

                TextInput::make('whatsapp_paciente')
                    ->label('WhatsApp / Celular')
                    ->required(fn (Get $get) => empty($get('paciente_id')))
                    ->hidden(fn (Get $get) => ! empty($get('paciente_id')))
                    ->mask(RawJs::make(<<<'JS'
                        $input.replace(/\D/g, '').length <= 10 
                            ? '(99) 9999-9999' 
                            : '(99) 99999-9999'
                    JS))
                    ->maxLength(15)
                    ->tel()
                    ->columnSpan(1),

                DateTimePicker::make('data_inicio')
                    ->label('Data e Hora de Início')
                    ->required()
                    ->seconds(false)
                    ->columnSpan(1),

                Select::make('duracao')
                    ->label('Duração')
                    ->options([
                        15 => '00:15',
                        30 => '00:30',
                        45 => '00:45',
                        60 => '01:00',
                        75 => '01:15',
                        90 => '01:30',
                        105 => '01:45',
                        120 => '02:00',
                        135 => '02:15',
                        150 => '02:30',
                        165 => '02:45',
                        180 => '03:00',
                    ])
                    ->default(60)
                    ->required()
                    ->columnSpan(1),

                ...($editando ? [
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'agendada' => 'Agendada',
                            'confirmada' => 'Confirmada',
                            'cancelada' => 'Cancelada',
                            'realizada' => 'Realizada',
                        ])
                        ->required()
                        ->columnSpan(2),
                ] : []),

                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpan(2),
            ]),
        ];
    }

    /**
     * Action do Filament para criar um novo agendamento.
     */
    public function criarAgendamentoAction(): Action
    {
        return Action::make('criarAgendamento')
            ->label('Novo Agendamento')
            ->modalHeading('Agendar Consulta')
            ->modalWidth('lg')
            ->fillForm(function (array $arguments) {
                $horaString = $arguments['hora'] ?? now()->format('H:i');
                if (is_numeric($horaString)) {
                    $horaString = sprintf('%02d:00', $horaString);
                }
                [$hora, $minuto] = explode(':', $horaString);

                $dataInicio = Carbon::parse($this->dataSelecionada, Auth::user()->timezone)
                    ->setTime((int) $hora, (int) $minuto)
                    ->setTimezone(config('app.timezone'));

                return [
                    'data_inicio' => $dataInicio,
                    'duracao' => 60,
                ];
            })
            ->schema($this->obterSchemaAgendamento(editando: false))
            ->action(function (array $data) {
                $dataInicio = Carbon::parse($data['data_inicio']);
                $dataFim = $dataInicio->copy()->addMinutes((int) $data['duracao']);

                ModelAgenda::create([
                    'paciente_id' => $data['paciente_id'] ?: null,
                    'nome_paciente' => $data['nome_paciente'] ?: null,
                    'whatsapp_paciente' => $data['whatsapp_paciente'] ?: null,
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'status' => 'agendada',
                    'observacoes' => $data['observacoes'] ?: null,
                ]);

                Notification::make()
                    ->success()
                    ->title('Consulta Agendada!')
                    ->body('O agendamento foi salvo com sucesso.')
                    ->send();
            });
    }

    /**
     * Action do Filament para editar um agendamento existente.
     */
    public function editarAgendamentoAction(): Action
    {
        return Action::make('editarAgendamento')
            ->label('Editar Agendamento')
            ->modalHeading('Editar Consulta')
            ->modalWidth('lg')
            ->fillForm(function (array $arguments) {
                $agenda = ModelAgenda::findOrFail($arguments['id']);
                $duracao = $agenda->data_inicio->diffInMinutes($agenda->data_fim);

                return [
                    'paciente_id' => $agenda->paciente_id,
                    'nome_paciente' => $agenda->nome_paciente,
                    'whatsapp_paciente' => $agenda->whatsapp_paciente,
                    'data_inicio' => $agenda->data_inicio,
                    'duracao' => $duracao,
                    'observacoes' => $agenda->observacoes,
                    'status' => $agenda->status,
                ];
            })
            ->schema($this->obterSchemaAgendamento(editando: true))
            ->action(function (array $data, array $arguments) {
                $agenda = ModelAgenda::findOrFail($arguments['id']);

                $dataInicio = Carbon::parse($data['data_inicio']);
                $dataFim = $dataInicio->copy()->addMinutes((int) $data['duracao']);

                $agenda->update([
                    'paciente_id' => $data['paciente_id'] ?: null,
                    'nome_paciente' => $data['nome_paciente'] ?: null,
                    'whatsapp_paciente' => $data['whatsapp_paciente'] ?: null,
                    'data_inicio' => $dataInicio,
                    'data_fim' => $dataFim,
                    'status' => $data['status'],
                    'observacoes' => $data['observacoes'] ?: null,
                ]);

                Notification::make()
                    ->success()
                    ->title('Consulta Atualizada!')
                    ->body('Os dados do agendamento foram atualizados com sucesso.')
                    ->send();
            });
    }

    /**
     * Confirma a presença na consulta (muda status para confirmada).
     */
    public function confirmarConsulta(int $id)
    {
        $agenda = ModelAgenda::findOrFail($id);
        $agenda->update(['status' => 'confirmada']);

        Notification::make()
            ->success()
            ->title('Consulta Confirmada!')
            ->send();
    }

    /**
     * Cancela a consulta (muda status para cancelada).
     */
    public function cancelarConsulta(int $id)
    {
        $agenda = ModelAgenda::findOrFail($id);
        $agenda->update(['status' => 'cancelada']);

        Notification::make()
            ->success()
            ->title('Consulta Cancelada!')
            ->send();
    }

    /**
     * Exclui o agendamento (soft delete).
     */
    public function excluirConsulta(int $id)
    {
        $agenda = ModelAgenda::findOrFail($id);
        $agenda->delete();

        Notification::make()
            ->success()
            ->title('Agendamento Removido!')
            ->send();
    }
}
