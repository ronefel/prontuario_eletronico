<?php

namespace App\Filament\Resources\TratamentoResource\RelationManagers;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('lote_id')
                    ->label('Lote')
                    ->relationship(
                        'lote',
                        'numero_lote',
                        fn ($query) => $query
                            ->where('status', 'ativo')
                            ->where('data_validade', '>=', now())
                            ->whereRaw('(SELECT SUM(quantidade) FROM movimentacoes WHERE lote_id = lotes.id AND deleted_at IS NULL) > 0')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Lote $lote) => "{$lote->produto->nome} - Lote: {$lote->numero_lote} (Venc: {$lote->data_validade?->format('d/m/Y')}) - Estoque: {$lote->quantidade_atual}")
                    ->searchable(['numero_lote', 'produto.nome'])
                    ->reactive()
                    ->required(),

                Forms\Components\TextInput::make('quantidade')
                    ->label('Quantidade')
                    ->numeric()
                    ->minValue(1)
                    ->required()
                    ->rules([
                        'integer',
                        'min:1',
                        // fn ($get) => function ($attribute, $value, $fail) use ($get) {
                        //     $lote = Lote::find($get('lote_id'));
                        //     if ($lote && $value > $lote->quantidade_atual) {
                        //         $fail("Estoque insuficiente. Disponível: {$lote->quantidade_atual}");
                        //     }
                        // },
                    ]),

                Forms\Components\DateTimePicker::make('data_aplicacao')
                    ->label('Data/Hora da Aplicação')
                    ->required()
                    ->default(now()),

                Forms\Components\RichEditor::make('observacoes')
                    ->label('Observações')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('data_aplicacao')
                    ->label('Data/Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('lote.produto.nome')
                    ->label('Produto'),

                Tables\Columns\TextColumn::make('lote.numero_lote')
                    ->label('Lote'),

                Tables\Columns\TextColumn::make('quantidade')
                    ->label('Qtd.'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agendada' => 'warning',
                        'aplicada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('aplicar')
                    ->label('Aplicar Agora')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => "Aplicar #{$record->id}")
                    ->modalDescription(fn ($record) => "Confirmar aplicação de {$record->quantidade} unidade(s) do lote {$record->lote->numero_lote}?")
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

                            // if ($record->quantidade > $lote->quantidade_atual) {
                            //     Notification::make()
                            //         ->danger()
                            //         ->title('Estoque insuficiente')
                            //         ->body("Disponível: {$lote->quantidade_atual}")
                            //         ->send();

                            //     return;
                            // }

                            // Atualiza status
                            $record->update(['status' => 'aplicada']);

                            // Usa o MovimentacaoService para criar saída
                            MovimentacaoService::criarSaida([
                                'produto_id' => $lote->produto_id,
                                'lote_id' => $lote->id,
                                'quantidade' => $record->quantidade,
                                'data_movimentacao' => $record->data_aplicacao,
                                'motivo' => "Aplicação clínica #{$record->id} - Tratamento #{$record->tratamento_id}",
                                'documento' => "APL-{$record->id}",
                                'user_id' => $record->aplicador_id,
                                'valor_unitario' => $lote->valor_unitario,
                            ]);

                            Notification::make()
                                ->success()
                                ->title('Aplicação realizada!')
                                ->body("Saída registrada: {$record->quantidade} unidade(s) do lote {$lote->numero_lote}")
                                ->send();
                        });
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
