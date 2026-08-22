<?php

namespace App\Filament\Resources\Pacientes\Pages;

use App\Models\ExameLaboratorial;
use App\Models\ExameParametro;
use App\Models\ExameRegistro;
use App\Models\ExameResultadoItem;
use App\Models\Paciente;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExamesPaciente extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.resources.pacientes.pages.exames-paciente';

    public Paciente|int|string|null $paciente = null;

    public function mount(int|string|null $record = null): void
    {
        if ($record) {
            $this->paciente = Paciente::findOrFail($record);
        }
    }

    public function lancarResultadoAction(): Action
    {
        return Action::make('lancarResultado')
            ->label('Lançar Resultado de Exame')
            ->icon('heroicon-o-plus-circle')
            ->color('primary')
            ->modalHeading('Lançar Resultado de Exame Laboratorial')
            ->modalSubmitActionLabel('Salvar Resultado')
            ->modalWidth('3xl')
            ->schema([
                Select::make('exame_id')
                    ->label('Exame Laboratorial')
                    ->placeholder('Selecione um exame do catálogo...')
                    ->options(ExameLaboratorial::where('ativo', true)->orderBy('nome')->pluck('nome', 'id'))
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (! $state) {
                            $set('itens', []);

                            return;
                        }

                        $exame = ExameLaboratorial::with('parametros')->find($state);
                        if (! $exame) {
                            $set('itens', []);

                            return;
                        }

                        $itens = [];
                        foreach ($exame->parametros as $param) {
                            $unidade = $param->unidade_medida ? " ({$param->unidade_medida})" : '';
                            $faixa = '';
                            if ($param->valor_minimo_ideal !== null || $param->valor_maximo_ideal !== null) {
                                $min = $param->valor_minimo_ideal ?? 'N/A';
                                $max = $param->valor_maximo_ideal ?? 'N/A';
                                $faixa = " [Faixa ideal: {$min} a {$max}]";
                            }

                            $itens[] = [
                                'exame_parametro_id' => $param->id,
                                'nome_parametro' => $param->nome_parametro . $unidade . $faixa,
                                'valor_resultado' => null,
                            ];
                        }

                        $set('itens', $itens);
                    }),
                DatePicker::make('data_exame')
                    ->label('Data da Coleta/Exame')
                    ->default(now())
                    ->maxDate(now())
                    ->required(),
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->placeholder('Anotações médicas, nome do laboratório, etc.')
                    ->rows(2)
                    ->columnSpanFull(),
                Repeater::make('itens')
                    ->label('Preenchimento dos Parâmetros do Exame')
                    ->schema([
                        Hidden::make('exame_parametro_id'),
                        TextInput::make('nome_parametro')
                            ->label('Parâmetro')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(2),
                        TextInput::make('valor_resultado')
                            ->label('Resultado Numérico')
                            ->numeric()
                            ->step('0.01')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                if (! $this->paciente) {
                    Notification::make()->title('Paciente não identificado.')->danger()->send();

                    return;
                }

                $registro = ExameRegistro::create([
                    'paciente_id' => $this->paciente->id,
                    'exame_id' => $data['exame_id'],
                    'data_exame' => $data['data_exame'],
                    'observacoes' => $data['observacoes'] ?? null,
                ]);

                if (isset($data['itens']) && is_array($data['itens'])) {
                    foreach ($data['itens'] as $item) {
                        if (isset($item['exame_parametro_id']) && isset($item['valor_resultado'])) {
                            ExameResultadoItem::create([
                                'exame_registro_id' => $registro->id,
                                'exame_parametro_id' => $item['exame_parametro_id'],
                                'valor_resultado' => $item['valor_resultado'],
                            ]);
                        }
                    }
                }

                Notification::make()
                    ->title('Resultado de exame salvo com sucesso!')
                    ->success()
                    ->send();
            });
    }

    public function excluirRegistro(int $registroId): void
    {
        $registro = ExameRegistro::where('paciente_id', $this->paciente->id)->find($registroId);
        if ($registro) {
            $registro->delete();
            Notification::make()
                ->title('Registro de exame excluído.')
                ->success()
                ->send();
        }
    }

    public function getRegistrosProperty()
    {
        if (! $this->paciente) {
            return collect();
        }

        return ExameRegistro::with(['exame', 'itens.parametro'])
            ->where('paciente_id', $this->paciente->id)
            ->orderBy('data_exame', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
