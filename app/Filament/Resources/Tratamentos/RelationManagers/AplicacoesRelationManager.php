<?php

namespace App\Filament\Resources\Tratamentos\RelationManagers;

use App\Filament\Forms\Components\RepeaterInline;
use App\Filament\Resources\Tratamentos\TratamentoResource;
use App\Models\Aplicacao;
use App\Models\Kit;
use App\Models\Lote;
use App\Services\Estoque\MovimentacaoService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AplicacoesRelationManager extends RelationManager
{
    protected static string $relationship = 'aplicacoes';

    protected static ?string $title = 'Aplicações';

    protected function getEloquentQuery(): Builder
    {
        // @phpstan-ignore staticMethod.notFound
        return parent::getEloquentQuery()->with(['lotes.produto']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('data_aplicacao')
                    ->label('Data/Hora da Aplicação')
                    ->required()
                    ->seconds(false)
                    ->default(now())
                    ->columnSpanFull(),

                Textarea::make('observacoes')
                    ->label('Observações')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('kit_id')
                    ->label('Carregar Kit')
                    ->options(Kit::where('ativo', true)->pluck('nome', 'id'))
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->dehydrated(false)
                    ->placeholder('Selecione um Kit para preencher os itens...')
                    ->columnSpanFull()
                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                        if (! $state) {
                            return;
                        }

                        $kit = Kit::with('itens.produto')->find($state);
                        if (! $kit) {
                            return;
                        }

                        $novosItens = [];
                        $produtosFaltantes = [];

                        // Para cada item do kit
                        foreach ($kit->itens as $item) {
                            $produto = $item->produto;
                            if (! $produto) {
                                continue;
                            } // Should not happen if foreign key integrity

                            $quantidadeNecessaria = $item->quantidade;
                            $produtoId = $produto->id;

                            // Buscar lotes VÁLIDOS (ativos, não vencidos, com saldo > 0)
                            // Ordenados por validade (FIFO)
                            $lotes = Lote::where('produto_id', $produtoId)
                                ->where('status', 'ativo')
                                ->where('data_validade', '>=', now())
                                ->get()
                                ->filter(fn ($lote) => $lote->quantidade_atual > 0)
                                ->sortBy('data_validade');

                            $quantidadeAtendida = 0;

                            foreach ($lotes as $lote) {
                                if ($quantidadeAtendida >= $quantidadeNecessaria) {
                                    break;
                                }

                                $saldoLote = $lote->quantidade_atual;
                                $quantidadeFaltante = $quantidadeNecessaria - $quantidadeAtendida;

                                $quantidadeParaPegar = min($saldoLote, $quantidadeFaltante);

                                $novosItens[] = [
                                    'lote_id' => $lote->id,
                                    'quantidade' => $quantidadeParaPegar,
                                    // 'saldo_lote' => $saldoLote, // Opcional, se o campo existir no repeater, mas é dinâmico
                                ];

                                $quantidadeAtendida += $quantidadeParaPegar;
                            }

                            if ($quantidadeAtendida < $quantidadeNecessaria) {
                                $produtosFaltantes[] = "{$produto->nome} (Faltam ".($quantidadeNecessaria - $quantidadeAtendida).')';
                            }
                        }

                        // Atualiza o repeater
                        // Mantém itens existentes? O usuário pediu para "selecionar o kit EM VEZ DE selecionar lote por lote"
                        // Assumo que substitui ou adiciona. Vamos adicionar aos existentes para segurança, mas se estiver vazio, preenche.
                        $itensAtuais = $get('itens') ?? [];
                        $set('itens', array_merge($itensAtuais, $novosItens));

                        // Notifica se faltar algo
                        if (! empty($produtosFaltantes)) {
                            Notification::make()
                                ->warning()
                                ->title('Estoque Insuficiente para o Kit')
                                ->body('Os seguintes produtos não possuem estoque suficiente para completar o kit:<br>'.implode('<br>', $produtosFaltantes))
                                ->persistent()
                                ->send();
                        }

                        // Limpa seleção do kit para permitir selecionar outro
                        $set('kit_id', null);
                    }),

                RepeaterInline::make('itens')
                    ->label('Itens')
                    ->relationship('itens')
                    ->schema([
                        Select::make('lote_id')
                            ->label('Produto')
                            ->hiddenLabel()
                            ->relationship(
                                'lote',
                                'numero_lote',
                                fn ($query, Get $get) => $query
                                    ->join('produtos', 'lotes.produto_id', '=', 'produtos.id')
                                    ->where('lotes.status', 'ativo')
                                    ->where('lotes.data_validade', '>=', now())
                                    ->whereRaw('(SELECT SUM(quantidade) FROM movimentacoes WHERE lote_id = lotes.id AND deleted_at IS NULL) > 0')
                                    ->whereNotIn('lotes.id', collect($get('../../itens'))
                                        ->pluck('lote_id')
                                        ->filter(fn ($id) => $id !== $get('lote_id')) // Mantém o atual
                                        ->toArray()
                                    )
                                    ->select('lotes.*')
                            )
                            ->getOptionLabelFromRecordUsing(fn (Lote $lote) => "{$lote->produto->nome} - Lote: {$lote->numero_lote} (Venc: {$lote->data_validade?->format('d/m/Y')})")
                            ->searchable(['numero_lote', 'produtos.nome'])
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, Set $set) => $set('saldo_lote', Lote::find($state)->quantidade_atual ?? 0))
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('quantidade')
                            ->label('Quantidade')
                            ->hiddenLabel()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->suffix('un')
                            ->required()
                            ->helperText(function (Get $get) {
                                $loteId = $get('lote_id');
                                if (! $loteId) {
                                    return 'Disponível:';
                                }
                                $lote = Lote::find($loteId);

                                return $lote ? "Disponível: {$lote->quantidade_atual}" : null;
                            })
                            ->columnSpan(1),
                    ])
                    ->addActionLabel('Adicionar Item')
                    ->addActionAlignment(Alignment::Start)
                    ->defaultItems(0)
                    ->columns(4)
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
                    ->dateTime('d/m/Y H:i', Auth::user()->timezone)
                    ->sortable(),

                Tables\Columns\TextColumn::make('lotes_produtos')
                    ->label('Produtos')
                    ->listWithLineBreaks()
                    ->weight('bold')
                    ->getStateUsing(fn ($record) => $record->lotes->map(fn ($lote) => $lote->produto->nome))
                    ->limit(50),

                Tables\Columns\TextColumn::make('lotes_quantidades')
                    ->label('Qtd.')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => $record->lotes->map(fn ($lote) => $lote->pivot->quantidade.' un')),

                Tables\Columns\TextColumn::make('lotes_valores')
                    ->label('Valor Itens')
                    ->money('BRL')
                    ->listWithLineBreaks()
                    ->getStateUsing(fn ($record) => $record->lotes->map(fn ($lote) => (float) ($lote->pivot->quantidade * $lote->valor_unitario))),

                Tables\Columns\TextColumn::make('valor_total')
                    ->label('Total')
                    ->money('BRL')
                    ->weight('bold'),

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
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('aplicar')
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
                Action::make('reverter')
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
                ReplicateAction::make()
                    ->hiddenLabel()
                    ->tooltip('Duplicar')
                    ->excludeAttributes(['data_aplicacao', 'status', 'created_at', 'updated_at', 'deleted_at'])
                    ->beforeReplicaSaved(function (Aplicacao $replica) {
                        $replica->status = 'agendada';
                        $replica->data_aplicacao = now();
                        $replica->created_at = now();
                        $replica->updated_at = now();
                    })
                    ->after(function (Aplicacao $original, Aplicacao $replica) {
                        foreach ($original->itens as $item) {
                            $replica->itens()->create([
                                'lote_id' => $item->lote_id,
                                'quantidade' => $item->quantidade,
                            ]);
                        }

                        Notification::make()
                            ->success()
                            ->title('Aplicação duplicada com sucesso')
                            ->body('A nova aplicação foi criada como "agendada".')
                            ->send();
                    }),
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar')
                    ->visible(fn ($record) => $record->status !== 'aplicada'),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn ($record) => $record->status !== 'aplicada'),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ])
            ->paginated(false);
    }
}
