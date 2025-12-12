<?php

namespace App\Filament\Resources\TratamentoResource\RelationManagers;

use App\Filament\Resources\TratamentoResource;
use App\Models\Lote;
use App\Services\Estoque\MovimentacaoService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
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
                Forms\Components\DateTimePicker::make('data_aplicacao')
                    ->label('Data/Hora da Aplicação')
                    ->required()
                    ->seconds(false)
                    ->default(now())
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),

                Forms\Components\Repeater::make('itens')
                    ->label('Itens')
                    ->relationship('itens')
                    ->schema([
                        Forms\Components\Select::make('lote_id')
                            ->label('Produto')
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
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
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
                    ])
                    ->addActionLabel('Adicionar Item')
                    ->addActionAlignment(Alignment::Start)
                    ->columns(3)
                    ->columnSpanFull()
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data) {
                        return $data;
                    }),
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

                Tables\Columns\TextColumn::make('itens.lote.produto.nome')
                    ->label('Produtos')
                    ->listWithLineBreaks()
                    ->bulleted()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('itens.lote.numero_lote')
                    ->label('Lotes')
                    ->listWithLineBreaks(),

                Tables\Columns\TextColumn::make('itens.quantidade')
                    ->label('Qtd.')
                    ->listWithLineBreaks()
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
                    ->modalHeading(fn ($record) => "Confirmar Aplicação #{$record->id}?")
                    ->modalDescription(function ($record) {
                        $itens = $record->itens->map(fn ($item) => "{$item->quantidade}x {$item->lote->produto->nome} (Lote: {$item->lote->numero_lote})")->implode(', ');

                        return "Confirmar aplicação dos itens: {$itens}? A data registrada será {$record->data_aplicacao->format('d/m/Y H:i')}.";
                    })
                    ->visible(fn ($record) => $record->status === 'agendada')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            // Validações
                            foreach ($record->itens as $item) {
                                $lote = $item->lote;
                                if ($lote->status !== 'ativo') {
                                    Notification::make()
                                        ->danger()
                                        ->title("Lote {$lote->numero_lote} inativo")
                                        ->body('Não é possivel aplicar um lote inativo.')
                                        ->send();

                                    return;
                                }

                                if ($lote->data_validade < now(Auth::user()->timezone)) {
                                    Notification::make()
                                        ->danger()
                                        ->title("Lote {$lote->numero_lote} vencido")
                                        ->body('Nao é possivel aplicar um lote vencido.')
                                        ->send();

                                    return;
                                }
                            }

                            // Atualiza status
                            $record->update(['status' => 'aplicada']);

                            // Registra saídas
                            $urlTratamento = TratamentoResource::getUrl('edit', ['record' => $record->tratamento_id]);
                            foreach ($record->itens as $item) {
                                MovimentacaoService::criarSaida([
                                    'produto_id' => $item->lote->produto_id,
                                    'lote_id' => $item->lote_id,
                                    'quantidade' => $item->quantidade,
                                    'data_movimentacao' => $record->data_aplicacao,
                                    'motivo' => "<p>Aplicação clínica <b>#{$record->id}</b> - Tratamento <a href=\"{$urlTratamento}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">#{$record->tratamento_id}</a></p>",
                                    'user_id' => $record->aplicador_id ?? Auth::id(), // Fallback to auth if aplicador_id is null/not present
                                    'valor_unitario' => $item->lote->valor_unitario,
                                    'is_manual' => false,
                                ]);
                            }

                            Notification::make()
                                ->success()
                                ->title('Aplicação realizada!')
                                ->body("Estoque atualizado para {$record->itens->count()} item(ns).")
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
                            foreach ($record->itens as $item) {
                                MovimentacaoService::criarEntrada([
                                    'produto_id' => $item->lote->produto_id,
                                    'lote_id' => $item->lote_id,
                                    'quantidade' => $item->quantidade,
                                    'motivo' => "<p>Estorno de aplicação <b>#{$record->id}</b> - Tratamento <a href=\"{$urlTratamento}\" target=\"_blank\" class=\"text-primary-600 hover:underline\">#{$record->tratamento_id}</a></p>",
                                    'user_id' => Auth::id(),
                                    'valor_unitario' => $item->lote->valor_unitario,
                                    'is_manual' => false,
                                ]);
                            }

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
