<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InventarioResource\Pages;
use App\Models\Inventario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class InventarioResource extends Resource
{
    protected static ?string $model = Inventario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Inventários';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('data_inventario')
                    ->default(today())
                    ->required(),
                Forms\Components\Select::make('tipo')
                    ->options([
                        'completo' => 'Completo',
                        'ciclico' => 'Cíclico',
                        'por_produto' => 'Por Produto',
                    ])
                    ->required(),
                Forms\Components\Select::make('produto_id')
                    ->relationship('produto', 'nome')
                    ->nullable(),
                Forms\Components\Select::make('lote_id')
                    ->relationship('lote', 'numero_lote', fn ($query, $get) => $query->where('produto_id', $get('produto_id')))
                    ->nullable(),
                Forms\Components\TextInput::make('quantidade_contada')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('quantidade_registrada')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('motivo_discrepancia')
                    ->nullable(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(Auth::id()),
                Forms\Components\Select::make('status')
                    ->options([
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                    ])
                    ->default('pendente'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('data_inventario')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo'),
                Tables\Columns\TextColumn::make('produto.nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('lote.numero_lote')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantidade_contada'),
                Tables\Columns\TextColumn::make('quantidade_registrada'),
                Tables\Columns\TextColumn::make('discrepancia')
                    ->color(fn ($record) => $record->discrepancia != 0 ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('user.name'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo')
                    ->options([
                        'completo' => 'Completo',
                        'ciclico' => 'Cíclico',
                        'por_produto' => 'Por Produto',
                    ]),
                Tables\Filters\SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('aprovar')
                    ->visible(fn ($record) => $record->status === 'pendente')
                    ->action(function (Inventario $record) {
                        $record->status = 'aprovado';
                        $record->save();

                        if ($record->lote_id && $record->discrepancia != 0) {
                            $lote = $record->lote;
                            $lote->save();

                            $record->produto->movimentacoes()->create([
                                'tipo' => 'ajuste',
                                'lote_id' => $lote->id,
                                'quantidade' => $record->discrepancia,
                                'data_movimentacao' => now(),
                                'motivo' => 'Ajuste via inventário #'.$record->id,
                                'user_id' => Auth::id(),
                                'valor_unitario' => $record->produto->valor_unitario_referencia,
                            ]);
                        }
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
            'edit' => Pages\EditInventario::route('/{record}/edit'),
        ];
    }
}
