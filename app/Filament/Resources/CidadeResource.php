<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CidadeResource\Pages;
use App\Filament\Resources\CidadeResource\RelationManagers;
use App\Models\Cidade;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Validator;

class CidadeResource extends Resource
{
    protected static ?string $model = Cidade::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nome')
                    ->required()
                    ->unique('cidades', 'nome', fn (?Cidade $record) => $record)
                    ->dehydrateStateUsing(fn (string $state): string => ucwords(strtolower($state))), // Capitaliza a primeira letra de cada palavra
                TextInput::make('uf')
                    ->required()
                    ->dehydrateStateUsing(fn (string $state): string => strtoupper($state))
            ])->columns(['md' => 2]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uf'),
            ])
            ->defaultSort('nome', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCidades::route('/'),
        ];
    }
}
