<?php

namespace App\Filament\Resources\TratamentoResource\RelationManagers;

use App\Filament\Resources\TratamentoResource;
use App\Models\Lote;
use App\Services\Estoque\MovimentacaoService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AplicacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'aplicacoes';

    protected static ?string $title = 'Aplicações';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\Select::make('lote_id')
                            ->label('Lote')
                            ->relationship(
                                'lote',
                                'numero_lote',
                                fn ($query) => $query
                                    ->join('produtos', 'lotes.produto_id', '=', 'produtos.id')
                                    ->where('lotes.status', 'ativo')
                                    ->where('lotes.data_validade', '>=', now())
                                    ->whereRaw('(SELECT SUM(quantidade) FROM movimentacoes WHERE lote_id = lotes.id AND deleted_at IS NULL) > 0')
                                    ->select('lotes.*')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Lote $lote) => "{$lote->produto->nome} - Lote: {$lote->numero_lote} (Venc: {$lote->data_validade?->format('d/m/Y')})")
                            ->searchable(['numero_lote', 'produtos.nome'])
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Forms\Set $set) => $set('saldo_lote', Lote::find($state)->quantidade_atual ?? 0))
                            ->required()
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('quantidade')
                            ->label('Quantidade')
                            ->numeric()
                            ->minValue(1)
                            ->required()
                            ->suffix('unidades')
                            ->helperText(function (Forms\Get $get) {
                                $loteId = $get('lote_id');
                                if (! $loteId) {
                                    return null;
                                }
                                $lote = Lote::find($loteId);

                                return $lote ? "Disponível: {$lote->quantidade_atual}" : null;
                            })
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('data_aplicacao')
                            ->label('Data/Hora da Aplicação')
                            ->required()
                            ->seconds(false)
                            ->default(now())
                            ->columnSpan(1)
                            ->rules([
                                fn (Forms\Get $get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    $lote = Lote::find($get('lote_id'));
                                    if ($lote && $lote->data_validade && \Illuminate\Support\Carbon::parse($value)->gt($lote->data_validade)) {
                                        $fail("A data da aplicação não pode ser superior à validade do lote ({$lote->data_validade->format('d/m/Y')}).");
                                    }
                                },
                            ]),
                    ])->columns(4),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#'),
                Tables\Columns\TextColumn::make('data_aplicacao')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lote.produto.nome')
                    ->label('Produto')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('lote.numero_lote')
                    ->label('Lote'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->suffix(' un'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agendada' => 'warning',
                        'aplicada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('observacoes')
                    ->label('Observações')
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('aplicar')
                    ->label('Aplicar Agora')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => "Aplicar {$record->lote->produto->nome}?")
                    ->modalDescription(fn ($record) => "Confirmar aplicação de {$record->quantidade} unidade(s) do produto {$record->lote->produto->nome} do lote {$record->lote->numero_lote} nesta data {$record->data_aplicacao->format('d/m/Y H:i')}?")
                    ->modalSubmitActionLabel('Sim, Aplicar')
                    ->visible(fn ($record) => $record->status === 'agendada')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            $lote = $record->lote;

                            // Validações finais
                            if ($lote->status !== 'ativo') {
                                Notification::make()
                                    ->danger()
                                    ->title('Lote inativo')
                                    ->body('Não é possivel aplicar um lote inativo.')
                                    ->send();

                                return;
                            }

                            if ($lote->data_validade < now(Auth::user()->timezone)) {
                                Notification::make()
                                    ->danger()
                                    ->title('Lote vencido')
                                    ->body('Nao é possivel aplicar um lote vencido.')
                                    ->send();

                                return;
                            }

                            // Atualiza status
                            $record->update(['status' => 'aplicada']);

                            // Usa o MovimentacaoService para criar saída
                            $urlTratamento = TratamentoResource::getUrl('edit', ['record' => $record->tratamento_id]);
                            MovimentacaoService::criarSaida([
                                'produto_id' => $lote->produto_id,
                                'lote_id' => $lote->id,
                                'quantidade' => $record->quantidade,
                                'data_movimentacao' => $record->data_aplicacao,
                                'motivo' => "<p>Aplicação clínica <b>#{$record->id}</b> - Tratamento <a href=\"{$urlTratamento}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">#{$record->tratamento_id}</a></p>",
                                'user_id' => $record->aplicador_id,
                                'valor_unitario' => $lote->valor_unitario,
                                'is_manual' => false,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Aplicação realizada!')
                                ->body("Saída registrada: {$record->quantidade} unidade(s) do lote {$lote->numero_lote}")
                                ->send();
                        });
                    }),
                Tables\Actions\Action::make('reverter')
                    ->label('Reverter Aplicação')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reverter Aplicação?')
                    ->modalDescription('Tem certeza que deseja reverter esta aplicação? O estoque será estornado e o status voltará para agendada.')
                    ->visible(fn ($record) => $record->status === 'aplicada')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Atualiza status
                            $record->update(['status' => 'agendada']);

                            // Estorno de estoque (Entrada)
                            $urlTratamento = TratamentoResource::getUrl('edit', ['record' => $record->tratamento_id]);
                            MovimentacaoService::criarEntrada([
                                'produto_id' => $record->lote->produto_id,
                                'lote_id' => $record->lote_id,
                                'quantidade' => $record->quantidade,
                                'motivo' => "<p>Estorno de aplicação <b>#{$record->id}</b> - Tratamento <a href=\"{$urlTratamento}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">#{$record->tratamento_id}</a></p>",
                                'user_id' => Auth::id(),
                                'valor_unitario' => $record->lote->valor_unitario,
                                'is_manual' => false,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Aplicação revertida!')
                                ->body('Estoque estornado e status atualizado.')
                                ->send();
                        });
                    }),
                Tables\Actions\EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar')
                    ->visible(fn ($record) => $record->status !== 'aplicada'),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn ($record) => $record->status !== 'aplicada'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->paginated(false);
    }
}
