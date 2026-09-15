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
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ExamesPaciente extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected string $view = 'filament.resources.pacientes.pages.exames-paciente';

    public Paciente|int|string|null $paciente = null;

    public int|string|null $selectedParametroId = null;

    public bool $modalHistoricoAberto = false;

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
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if (!$state) {
                            $set('itens', []);

                            return;
                        }

                        $exame = ExameLaboratorial::with('parametros')->find($state);
                        if (!$exame) {
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
                if (!$this->paciente) {
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
                        if (isset($item['exame_parametro_id']) && isset($item['valor_resultado']) && $item['valor_resultado'] !== '') {
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

    public function editarItemAction(): Action
    {
        return Action::make('editarItem')
            ->label('')
            ->icon('heroicon-o-pencil-square')
            ->color('primary')
            ->iconButton()
            ->tooltip('Editar medição')
            ->modalHeading('Editar Medição Histórica')
            ->modalSubmitActionLabel('Salvar Alterações')
            ->fillForm(function (array $arguments) {
                $item = ExameResultadoItem::with('registro')->find($arguments['itemId'] ?? null);
                if (!$item) {
                    return [];
                }

                return [
                    'item_id' => $item->id,
                    'valor_resultado' => $item->valor_resultado,
                    'data_exame' => $item->registro->data_exame?->format('Y-m-d'),
                    'observacoes' => $item->registro->observacoes,
                ];
            })
            ->schema([
                Hidden::make('item_id'),
                TextInput::make('valor_resultado')
                    ->label('Resultado Numérico')
                    ->numeric()
                    ->step('0.01')
                    ->required(),
                DatePicker::make('data_exame')
                    ->label('Data da Coleta/Exame')
                    ->maxDate(now())
                    ->required(),
                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(2)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data) {
                $item = ExameResultadoItem::with('registro')->find($data['item_id'] ?? null);
                if (!$item) {
                    return;
                }

                $item->update([
                    'valor_resultado' => $data['valor_resultado'],
                ]);

                if ($item->registro) {
                    $item->registro->update([
                        'data_exame' => $data['data_exame'],
                        'observacoes' => $data['observacoes'] ?? null,
                    ]);
                }

                Notification::make()
                    ->title('Medição atualizada com sucesso!')
                    ->success()
                    ->send();
            });
    }

    public function abrirHistoricoEvolucao(int $parametroId): void
    {
        $this->selectedParametroId = $parametroId;
        $this->modalHistoricoAberto = true;
        $this->dispatch('open-modal', id: 'modal-historico-evolucao');
    }

    public function fecharHistoricoEvolucao(): void
    {
        $this->dispatch('close-modal', id: 'modal-historico-evolucao');
        $this->modalHistoricoAberto = false;
        $this->selectedParametroId = null;
    }

    public function excluirItem(int $itemId): void
    {
        $item = ExameResultadoItem::with('registro')->find($itemId);
        if ($item) {
            $registro = $item->registro;
            $item->delete();

            if ($registro && $registro->itens()->count() === 0) {
                $registro->delete();
            }

            Notification::make()
                ->title('Medição excluída com sucesso.')
                ->success()
                ->send();
        }
    }

    public function getResumoParametrosProperty()
    {
        if (!$this->paciente) {
            return collect();
        }

        $parametros = ExameParametro::with('exame')
            ->whereHas('resultadoItens.registro', function ($q) {
                $q->where('paciente_id', $this->paciente->id);
            })
            ->get();

        $resumo = collect();

        foreach ($parametros as $param) {
            $itens = ExameResultadoItem::with('registro')
                ->whereHas('registro', function ($q) {
                    $q->where('paciente_id', $this->paciente->id);
                })
                ->where('exame_parametro_id', $param->id)
                ->join('exame_registros', 'exame_resultado_itens.exame_registro_id', '=', 'exame_registros.id')
                ->orderBy('exame_registros.data_exame', 'desc')
                ->orderBy('exame_resultado_itens.id', 'desc')
                ->select('exame_resultado_itens.*')
                ->get();

            if ($itens->isEmpty()) {
                continue;
            }

            /** @var ExameResultadoItem $ultimo */
            $ultimo = $itens->first();
            $ultimo->setRelation('parametro', $param);

            /** @var ExameResultadoItem|null $penultimo */
            $penultimo = $itens->skip(1)->first();
            if ($penultimo) {
                $penultimo->setRelation('parametro', $param);
            }

            $qtdParametros = $param->exame?->parametros()->count() ?? 1;
            $isExameSimples = ($qtdParametros === 1) || (strtolower(trim($param->nome_parametro)) === 'resultado');

            $min = $param->valor_minimo_ideal;
            $max = $param->valor_maximo_ideal;
            $unidade = $param->unidade_medida ? ' ' . $param->unidade_medida : '';

            $faixaIdeal = '-';
            if ($min !== null && $max !== null) {
                $faixaIdeal = "{$min} a {$max}{$unidade}";
            } elseif ($min !== null) {
                $faixaIdeal = ">= {$min}{$unidade}";
            } elseif ($max !== null) {
                $faixaIdeal = "<= {$max}{$unidade}";
            }

            $resumo->push((object) [
                'parametro_id' => $param->id,
                'exame_nome' => $param->exame->nome ?? 'Exame',
                'parametro_nome' => $param->nome_parametro,
                'unidade_medida' => $param->unidade_medida,
                'is_exame_simples' => $isExameSimples,
                'faixa_ideal' => $faixaIdeal,
                'ultimo_valor' => $ultimo->valor_resultado,
                'status_normalidade' => $ultimo->status_normalidade,
                'data_ultima_coleta' => $ultimo->registro?->data_exame,
                'penultimo_valor' => $penultimo?->valor_resultado,
                'status_penultimo' => $penultimo ? $penultimo->status_normalidade : null,
                'data_penultima_coleta' => $penultimo?->registro?->data_exame,
                'total_medicoes' => $itens->count(),
            ]);
        }

        return $resumo->sortBy(['exame_nome', 'parametro_nome']);
    }

    public function getHistoricoParametroSelecionadoProperty()
    {
        if (!$this->paciente || !$this->selectedParametroId) {
            return null;
        }

        $parametro = ExameParametro::with('exame')->find($this->selectedParametroId);
        if (!$parametro) {
            return null;
        }

        $itens = ExameResultadoItem::with('registro')
            ->whereHas('registro', function ($q) {
                $q->where('paciente_id', $this->paciente->id);
            })
            ->where('exame_parametro_id', $this->selectedParametroId)
            ->join('exame_registros', 'exame_resultado_itens.exame_registro_id', '=', 'exame_registros.id')
            ->orderBy('exame_registros.data_exame', 'desc')
            ->orderBy('exame_resultado_itens.id', 'desc')
            ->select('exame_resultado_itens.*')
            ->get();

        return (object) [
            'parametro' => $parametro,
            'itens' => $itens,
        ];
    }
}
