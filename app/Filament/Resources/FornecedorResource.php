<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FornecedorResource\Pages;
use App\Models\Fornecedor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FornecedorResource extends Resource
{
    protected static ?string $model = Fornecedor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Fornecedores';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')->required(),
                Forms\Components\TextInput::make('email')->email()->nullable(),
                Forms\Components\TextInput::make('telefone')->nullable(),
                Forms\Components\Textarea::make('endereco')->nullable(),
                Forms\Components\TextInput::make('prazo_entrega')->numeric()->default(7),
                Forms\Components\Select::make('status')
                    ->options(['ativo' => 'Ativo', 'inativo' => 'Inativo'])
                    ->default('ativo'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('telefone'),
                Tables\Columns\TextColumn::make('status'),
            ])
            ->filters([])
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
            'index' => Pages\ListFornecedores::route('/'),
            'create' => Pages\CreateFornecedor::route('/create'),
            'edit' => Pages\EditFornecedor::route('/{record}/edit'),
        ];
    }
}
