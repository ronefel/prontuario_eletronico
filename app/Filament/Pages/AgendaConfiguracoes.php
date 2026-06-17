<?php

namespace App\Filament\Pages;

use App\Models\AgendaConfiguracao;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class AgendaConfiguracoes extends Page
{
    protected string $view = 'filament.pages.agenda-configuracoes';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog;

    protected static ?string $title = 'Configurações da Agenda';

    protected static string|\UnitEnum|null $navigationGroup = 'Sistema';

    public ?array $dados = [];

    public function mount(): void
    {
        $this->dados = $this->getRecord()->attributesToArray();
        $this->form->fill($this->dados);
    }

    public function form(Schema $schema): Schema
    {
        // Gera lista de horas (00:00 às 23:00)
        $opcoesHoras = [];
        for ($i = 0; $i < 24; $i++) {
            $horaFormatada = sprintf('%02d:00', $i);
            $opcoesHoras[$horaFormatada] = $horaFormatada;
        }

        return $schema
            ->components([
                Form::make([
                    Grid::make(2)->schema([
                        Fieldset::make('Horários de Atendimento')
                            ->schema([
                                Select::make('hora_inicio')
                                    ->label('Hora Inicial')
                                    ->options($opcoesHoras)
                                    ->default('08:00')
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('hora_fim')
                                    ->label('Hora Final')
                                    ->options($opcoesHoras)
                                    ->default('18:00')
                                    ->required()
                                    ->columnSpan(1),

                                Select::make('intervalo')
                                    ->label('Intervalo de Atendimento')
                                    ->options([
                                        10 => '10 minutos',
                                        15 => '15 minutos',
                                        20 => '20 minutos',
                                        30 => '30 minutos',
                                        45 => '45 minutos',
                                        60 => '1 hora',
                                    ])
                                    ->default(30)
                                    ->required()
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),

                        Fieldset::make('Intervalo / Pausa')
                            ->schema([
                                Select::make('pausa_inicio')
                                    ->label('Início da Pausa')
                                    ->options($opcoesHoras)
                                    ->placeholder('Sem pausa')
                                    ->helperText('Ex: horário de almoço')
                                    ->columnSpan(1),

                                Select::make('pausa_fim')
                                    ->label('Fim da Pausa')
                                    ->options($opcoesHoras)
                                    ->placeholder('Sem pausa')
                                    ->columnSpan(1),
                            ])
                            ->columnSpan(2),

                        Fieldset::make('Limite de Consultas por Dia')
                            ->schema([
                                Select::make('modo_limite')
                                    ->label('Modo de Cálculo')
                                    ->options([
                                        'slots' => 'Por Slots Disponíveis (automático)',
                                        'manual' => 'Limite Manual',
                                    ])
                                    ->default('slots')
                                    ->required()
                                    ->live()
                                    ->helperText('Define como o sistema calcula se o dia está lotado.')
                                    ->columnSpan(2),

                                TextInput::make('limite_consultas_dia')
                                    ->label('Limite de Consultas por Dia')
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('Ex: 10')
                                    ->visible(fn (Get $get): bool => $get('modo_limite') === 'manual')
                                    ->requiredIf('modo_limite', 'manual')
                                    ->columnSpan(2),
                            ])
                            ->columnSpan(2),
                    ]),
                ])
                    ->livewireSubmitHandler('salvar')
                    ->footer([
                        Actions::make([
                            Action::make('salvar')
                                ->label('Salvar Configurações')
                                ->submit('salvar')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->record($this->getRecord())
            ->statePath('dados');
    }

    public function salvar(): void
    {
        $dadosForm = $this->form->getState();
        $configuracao = $this->getRecord();
        
        $configuracao->update($dadosForm);

        Notification::make()
            ->success()
            ->title('Configurações Salvas!')
            ->body('As configurações da agenda foram atualizadas com sucesso.')
            ->send();
    }

    public function getRecord(): AgendaConfiguracao
    {
        return AgendaConfiguracao::obterConfiguracao();
    }
}
