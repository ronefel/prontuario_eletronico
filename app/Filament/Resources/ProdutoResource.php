<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdutoResource\Pages;
use App\Models\Produto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProdutoResource extends Resource
{
    protected static ?string $model = Produto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Produtos';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')->required(),
                Forms\Components\Textarea::make('descricao')->nullable(),
                Forms\Components\TextInput::make('unidade_medida')->default('unidade'),
                Forms\Components\TextInput::make('valor_unitario_referencia')->numeric()->prefix('R$'),
                Forms\Components\TextInput::make('estoque_minimo')->numeric()->default(10),
                Forms\Components\Select::make('categoria_id')
                    ->relationship('categoria', 'nome')
                    ->required(),
                Forms\Components\Select::make('fornecedor_id')
                    ->relationship('fornecedor', 'nome')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
                Tables\Columns\TextColumn::make('categoria.nome'),
                Tables\Columns\TextColumn::make('estoque_total')
                    ->getStateUsing(fn ($record) => $record->lotes->sum('quantidade_atual'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor_unitario_referencia')->money('BRL'),
                Tables\Columns\TextColumn::make('estoque_minimo')
                    ->color(fn ($record) => $record->lotes->sum('quantidade_atual') < $record->estoque_minimo ? 'danger' : 'success'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categoria')->relationship('categoria', 'nome'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProdutos::route('/'),
            'create' => Pages\CreateProduto::route('/create'),
            'edit' => Pages\EditProduto::route('/{record}/edit'),
        ];
    }
}
