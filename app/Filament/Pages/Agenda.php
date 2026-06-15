<?php

namespace App\Filament\Pages;

use App\Models\Agenda as ModelAgenda;
use App\Models\GoogleConfiguracao;
use App\Models\Paciente;
use App\Services\GoogleCalendarService;
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
use Filament\Support\RawJs;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Agenda extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $title = 'Agenda de Consultas';

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

    /**
     * Retorna a lista de consultas do dia selecionado.
     */
    public function obterConsultasDoDia(): Collection
    {
        return ModelAgenda::query()
            ->with('paciente')
            ->whereDate('data_inicio', $this->dataSelecionada)
            ->orderBy('data_inicio')
            ->get();
    }

    /**
     * Retorna a lista de consultas agrupadas por hora do dia selecionado (0 a 23).
     */
    public function obterConsultasDoDiaPorHora(): array
    {
        $consultas = $this->obterConsultasDoDia();
        $agrupadas = array_fill(0, 24, []);

        foreach ($consultas as $consulta) {
            $hora = $consulta->data_inicio->hour;
            $agrupadas[$hora][] = $consulta;
        }

        return $agrupadas;
    }

    /**
     * Retorna os dias que possuem consultas no mês selecionado.
     * Usado para desenhar os pontos de marcação no calendário.
     */
    public function obterDiasComConsulta(): array
    {
        return ModelAgenda::query()
            ->whereYear('data_inicio', $this->anoSelecionado)
            ->whereMonth('data_inicio', $this->mesSelecionado)
            ->select(DB::raw('DATE(data_inicio) as data'))
            ->pluck('data')
            ->unique()
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

        for ($dia = 1; $dia <= $diasNoMes; $dia++) {
            $dataCompleta = Carbon::create($this->anoSelecionado, $this->mesSelecionado, $dia)->format('Y-m-d');
            $semana[$diaSemanaAtual] = [
                'numero' => $dia,
                'data' => $dataCompleta,
                'eh_hoje' => $dataCompleta === now()->format('Y-m-d'),
                'tem_consulta' => in_array($dataCompleta, $this->obterDiasComConsulta()),
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
     * Action do Filament para criar um novo agendamento.
     */
    public function criarAgendamentoAction(): Action
    {
        return Action::make('criarAgendamento')
            ->label('Novo Agendamento')
            ->modalHeading('Agendar Consulta')
            ->modalWidth('lg')
            ->fillForm(function (array $arguments) {
                $hora = isset($arguments['hora']) ? (int) $arguments['hora'] : now()->hour;
                $dataInicio = Carbon::parse($this->dataSelecionada)->setTime($hora, 0);

                return [
                    'data_inicio' => $dataInicio->format('Y-m-d H:i'),
                    'duracao' => 60,
                ];
            })
            ->schema([
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

                    Textarea::make('observacoes')
                        ->label('Observações')
                        ->rows(3)
                        ->columnSpan(2),
                ]),
            ])
            ->action(function (array $data) {
                $dataInicio = Carbon::parse($data['data_inicio']);
                $dataFim = $dataInicio->copy()->addMinutes((int) $data['duracao']);

                $agenda = ModelAgenda::create([
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
                    'data_inicio' => $agenda->data_inicio->format('Y-m-d H:i'),
                    'duracao' => $duracao,
                    'observacoes' => $agenda->observacoes,
                    'status' => $agenda->status,
                ];
            })
            ->schema([
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
                        ->placeholder('Ex: (11) 99999-9999')
                        ->columnSpan(1),

                    DateTimePicker::make('data_inicio')
                        ->label('Data e Hora de Início')
                        ->required()
                        ->seconds(false)
                        ->columnSpan(1),

                    TextInput::make('duracao')
                        ->label('Duração (minutos)')
                        ->numeric()
                        ->required()
                        ->columnSpan(1),

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

                    Textarea::make('observacoes')
                        ->label('Observações')
                        ->rows(3)
                        ->columnSpan(2),
                ]),
            ])
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
     * Action do Filament para configurar as credenciais do Google API.
     */
    public function configurarGoogleAction(): Action
    {
        $configuracao = GoogleConfiguracao::obterConfiguracao();

        return Action::make('configurarGoogle')
            ->label('Configurar Integração')
            ->modalHeading('Configurações do Google Calendar')
            ->modalWidth('md')
            ->fillForm([
                'client_id' => $configuracao->client_id,
                'client_secret' => $configuracao->client_secret,
                'redirect_uri' => $configuracao->redirect_uri ?: url('/google/calendar/callback'),
                'calendario_id' => $configuracao->calendario_id ?: 'primary',
            ])
            ->schema([
                TextInput::make('client_id')
                    ->label('Client ID do Google')
                    ->required()
                    ->placeholder('Cole o Client ID da API do Google'),

                TextInput::make('client_secret')
                    ->label('Client Secret do Google')
                    ->required()
                    ->password()
                    ->revealable()
                    ->placeholder('Cole o Client Secret da API do Google'),

                TextInput::make('redirect_uri')
                    ->label('URI de Redirecionamento Autenticada')
                    ->required()
                    ->helperText('Use esta mesma URL nas configurações do seu console de APIs do Google.'),

                TextInput::make('calendario_id')
                    ->label('ID do Calendário')
                    ->required()
                    ->helperText('Use "primary" para usar o calendário principal da conta vinculada.'),
            ])
            ->action(function (array $data) {
                $configuracao = GoogleConfiguracao::obterConfiguracao();
                $configuracao->update([
                    'client_id' => $data['client_id'],
                    'client_secret' => $data['client_secret'],
                    'redirect_uri' => $data['redirect_uri'],
                    'calendario_id' => $data['calendario_id'],
                ]);

                Notification::make()
                    ->success()
                    ->title('Configurações Salvas!')
                    ->body('Credenciais salvas com sucesso. Agora você pode conectar ao Google.')
                    ->send();
            });
    }

    /**
     * Inicia o fluxo de conexão OAuth 2.0 com o Google.
     */
    public function conectarGoogle(GoogleCalendarService $servico)
    {
        $url = $servico->obterUrlAutorizacao();

        if (empty($url)) {
            Notification::make()
                ->danger()
                ->title('Erro de Configuração')
                ->body('Certifique-se de preencher o Client ID e URI de Redirecionamento nas configurações da integração.')
                ->send();

            return;
        }

        return redirect()->away($url);
    }

    /**
     * Desconecta o Google Calendar limpando os tokens do banco.
     */
    public function desconectarGoogle()
    {
        $configuracao = GoogleConfiguracao::obterConfiguracao();
        $configuracao->update([
            'token_acesso' => null,
            'token_atualizacao' => null,
            'token_expira_em' => null,
        ]);

        Notification::make()
            ->success()
            ->title('Desconectado!')
            ->body('A integração com o Google Calendar foi removida.')
            ->send();
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
