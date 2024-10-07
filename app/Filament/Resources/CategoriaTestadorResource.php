<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoriaTestadorResource\Pages;
use App\Filament\Resources\CategoriaTestadorResource\RelationManagers;
use App\Filament\Resources\CategoriaTestadorResource\RelationManagers\TestadoresRelationManager;
use App\Models\CategoriaTestador;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoriaTestadorResource extends Resource
{
    protected static ?string $model = CategoriaTestador::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'categorias-testadores';

    protected static ?string $modelLabel = 'Categoria de Testadores';

    protected static ?string $pluralModelLabel = 'Categorias de Testadores';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('ordem')
                    ->required()
                    ->default(fn() => str_pad(\App\Models\CategoriaTestador::max('ordem') + 1, 2, '0', STR_PAD_LEFT))
                    ->helperText('Ordem de exibição. Exemplo: 01')
                    ->numeric(),
                Forms\Components\RichEditor::make('nota')
                    ->helperText('Observação sobre a categoria de testadores')
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'blockquote',
                        'bulletList',
                        'orderedList',
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ordem')
                    ->numeric()
                    ->sortable(),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // Tables\Columns\TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            TestadoresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategoriaTestadors::route('/'),
            'create' => Pages\CreateCategoriaTestador::route('/create'),
            'edit' => Pages\EditCategoriaTestador::route('/{record}/edit'),
        ];
    }
}
