<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Models\Inventario;
use App\Models\Local;
use App\Models\Lote;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Inventários';

    protected static ?string $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 206;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações do Inventário')
                    ->schema([
                        Forms\Components\Grid::make()
                            ->columns([
                                'sm' => 1,
                                'md' => 2,
                                'lg' => 3,
                                'xl' => 4,
                                '2xl' => 5,
                            ])
                            ->schema([
                                Forms\Components\DatePicker::make('data_inventario')
                                    ->label('Data do Inventário')
                                    ->default(today())
                                    ->required(),
                                Forms\Components\ToggleButtons::make('status')
                                    ->label('Status')
                                    ->options([
                                        'pendente' => 'Pendente',
                                        'aprovado' => 'Aprovado',
                                    ])
                                    ->default('pendente')
                                    ->inline()
                                    ->disabled(),
                                Forms\Components\TextInput::make('user_id')
                                    ->label('Usuário')
                                    ->default(fn () => Auth::user()->id)
                                    ->formatStateUsing(function ($state, $record) {
                                        if ($record && $record->user) {
                                            return $record->user->name;
                                        }

                                        return Auth::user()->name;
                                    })
                                    ->readOnly()
                                    ->dehydrateStateUsing(fn ($state, $context) => Auth::user()->id),
                                Forms\Components\Select::make('tipo')
                                    ->label('Tipo')
                                    ->options([
                                        'completo' => 'Completo',
                                        'por_local' => 'Por Local',
                                        'por_produto' => 'Por Produto',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->disabled(fn ($context) => $context === 'edit')
                                    ->helperText('Selecione o tipo de inventário para carregar os lotes correspondentes.')
                                    ->afterStateUpdated(function (callable $set, $state, $context) {
                                        if ($context === 'create') {
                                            if ($state === 'completo') {
                                                $lotes = Lote::where('status', 'ativo')
                                                    ->orderBy('numero_lote')
                                                    ->get()
                                                    ->map(fn ($lote) => [
                                                        'lote_id' => $lote->id,
                                                        'numero_lote' => $lote->numero_lote,
                                                        'vencimento' => $lote->data_validade->format('d/m/y'),
                                                        'produto' => $lote->produto->nome,
                                                        'quantidade_contada' => $lote->quantidade_atual,
                                                        'quantidade_registrada' => $lote->quantidade_atual,
                                                    ])
                                                    ->toArray();
                                                $set('inventarioLotes', $lotes);
                                            } else {
                                                $set('inventarioLotes', []);
                                            }
                                        }
                                    }),
                                Forms\Components\Select::make('produto_id')
                                    ->label('Produto')
                                    ->options(Produto::pluck('nome', 'id')->toArray())
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn ($get) => $get('tipo') === 'por_produto')
                                    ->reactive()
                                    ->hidden(fn ($context) => $context === 'edit')
                                    ->afterStateUpdated(function (callable $set, $state, $context) {
                                        if ($context === 'create' && $state) {
                                            $lotes = Lote::where('produto_id', $state)
                                                ->where('status', 'ativo')
                                                ->orderBy('numero_lote')
                                                ->get()
                                                ->map(fn ($lote) => [
                                                    'lote_id' => $lote->id,
                                                    'numero_lote' => $lote->numero_lote,
                                                    'vencimento' => $lote->data_validade->format('d/m/y'),
                                                    'produto' => $lote->produto->nome,
                                                    'quantidade_contada' => $lote->quantidade_atual,
                                                    'quantidade_registrada' => $lote->quantidade_atual,
                                                ])
                                                ->toArray();
                                            $set('inventarioLotes', $lotes);
                                        } else {
                                            $set('inventarioLotes', []);
                                        }
                                    }),
                                Forms\Components\Select::make('local_id')
                                    ->label('Local')
                                    ->options(Local::pluck('nome', 'id')->toArray())
                                    ->default(fn () => \App\Models\Local::count() === 1 ? \App\Models\Local::first()->id : null)
                                    ->visible(fn ($get) => $get('tipo') === 'por_local')
                                    ->reactive()
                                    ->hidden(fn ($context) => $context === 'edit')
                                    ->afterStateUpdated(function (callable $set, $state, $context) {
                                        if ($context === 'create' && $state) {
                                            $lotes = Lote::where('local_id', $state)
                                                ->where('status', 'ativo')
                                                ->orderBy('numero_lote')
                                                ->get()
                                                ->map(fn ($lote) => [
                                                    'lote_id' => $lote->id,
                                                    // 'numero_lote' => $lote->numero_lote,
                                                    'vencimento' => $lote->data_validade->format('d/m/y'),
                                                    'produto' => $lote->produto->nome,
                                                    'quantidade_contada' => $lote->quantidade_atual,
                                                    'quantidade_registrada' => $lote->quantidade_atual,
                                                ])
                                                ->toArray();
                                            $set('inventarioLotes', $lotes);
                                        } else {
                                            $set('inventarioLotes', []);
                                        }
                                    }),
                            ]),
                    ]),
                Forms\Components\Section::make('Lotes do Inventário')
                    ->schema([
                        Forms\Components\Repeater::make('inventarioLotes')
                            ->label('Lotes')
                            ->hiddenLabel()
                            ->relationship()
                            ->addActionLabel('')
                            ->addable(false)
                            ->deletable(false)
                            ->default([])
                            ->schema([
                                Forms\Components\Group::make([
                                    Forms\Components\Group::make([
                                        Forms\Components\Hidden::make('lote_id'),
                                        Forms\Components\TextInput::make('numero_lote')
                                            ->label('Número do Lote')
                                            ->disabled()
                                            ->formatStateUsing(function ($state, $get) {
                                                $loteId = $get('lote_id');
                                                $lote = $loteId ? Lote::find($loteId) : null;

                                                return $lote ? $lote->numero_lote : $state;
                                            }),
                                        Forms\Components\TextInput::make('vencimento')
                                            ->label('Vencimento')
                                            ->disabled()
                                            ->formatStateUsing(function ($state, $get) {
                                                $loteId = $get('lote_id');
                                                $lote = $loteId ? Lote::find($loteId) : null;

                                                return $lote ? ($lote->data_validade ? $lote->data_validade->format('d/m/y') : '-') : $state;
                                            }),
                                        Forms\Components\TextInput::make('produto')
                                            ->label('Produto')
                                            ->disabled()
                                            ->formatStateUsing(function ($state, $get) {
                                                $loteId = $get('lote_id');
                                                $lote = $loteId ? Lote::find($loteId) : null;

                                                return $lote ? $lote->produto->nome : $state;
                                            }),
                                    ])->columns(3),
                                    Forms\Components\Group::make([
                                        Forms\Components\TextInput::make('quantidade_registrada')
                                            ->label('Quantidade Registrada')
                                            ->numeric()
                                            ->readOnly()
                                            ->default(0),
                                        Forms\Components\TextInput::make('quantidade_contada')
                                            ->label('Quantidade Contada')
                                            ->numeric()
                                            ->reactive()
                                            ->required()
                                            ->helperText('Insira a quantidade física encontrada no estoque.'),
                                    ])->columns(2),
                                ])->columns(2),
                                Forms\Components\TextInput::make('motivo_discrepancia')
                                    ->label('Motivo da Discrepancia')
                                    ->hidden(fn ($get) => $get('quantidade_registrada') == $get('quantidade_contada'))
                                    ->nullable()
                                    ->helperText('Justifique a diferença entre o registrado e o contado.'),
                            ])
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('data_inventario')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('lotes_count')
                    ->label('Lotes Contados')
                    ->counts('lotes'),
                Tables\Columns\TextColumn::make('discrepancia_total')
                    ->getStateUsing(fn ($record) => $record->lotes()->sum('discrepancia'))
                    ->color(fn ($state) => $state != 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status')
                    ->color(fn ($state) => $state != 'pendente' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('user.name'),
            ])
            // ->filters([
            //     Tables\Filters\SelectFilter::make('tipo')
            //         ->options([
            //             'completo' => 'Completo',
            //             'por_local' => 'Por Local',
            //             'por_produto' => 'Por Produto',
            //         ]),
            // ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pendente'),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'pendente'),
                Tables\Actions\Action::make('aprovar')
                    ->visible(fn ($record) => $record->status === 'pendente')
                    ->color('success')
                    ->icon('heroicon-s-check')
                    ->action(function (Inventario $record) {
                        foreach ($record->lotes as $lote) {
                            if ($lote->pivot->discrepancia != 0) {
                                $lote->produto->movimentacoes()->create([
                                    'tipo' => 'ajuste',
                                    'lote_id' => $lote->id,
                                    'quantidade' => $lote->pivot->discrepancia,
                                    'data_movimentacao' => now(),
                                    'motivo' => 'Ajuste via inventário #'.$record->id,
                                    'user_id' => Auth::user()->id,
                                    'valor_unitario' => $lote->valor_unitario,
                                ]);
                            }
                        }

                        $record->status = 'aprovado';
                        $record->save();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventarios::route('/'),
            'create' => Pages\CreateInventario::route('/create'),
            'view' => Pages\ViewInventario::route('/{record}'),
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        /** @var Inventario $record */
        return $record->status !== 'aprovado';
    }
}
