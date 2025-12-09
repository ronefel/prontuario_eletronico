<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocalResource\Pages;
use App\Models\Local;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LocalResource extends Resource
{
    protected static ?string $model = Local::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $modelLabel = 'Local';

    protected static ?string $pluralModelLabel = 'Locais';

    protected static ?string $navigationLabel = 'Locais';

    protected static ?string $navigationGroup = 'Estoque';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')->required(),
                Forms\Components\Textarea::make('endereco')->nullable(),
                Forms\Components\TextInput::make('capacidade')->numeric()->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')->searchable(),
                Tables\Columns\TextColumn::make('capacidade'),
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
            'index' => Pages\ListLocais::route('/'),
            'create' => Pages\CreateLocal::route('/create'),
            'edit' => Pages\EditLocal::route('/{record}/edit'),
        ];
    }
}
