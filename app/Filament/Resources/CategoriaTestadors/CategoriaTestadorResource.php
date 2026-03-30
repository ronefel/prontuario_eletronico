<?php

namespace App\Filament\Resources\CategoriaTestadors;

use App\Filament\Resources\CategoriaTestadors\Pages\CreateCategoriaTestador;
use App\Filament\Resources\CategoriaTestadors\Pages\EditCategoriaTestador;
use App\Filament\Resources\CategoriaTestadors\Pages\ListCategoriaTestadors;
use App\Filament\Resources\CategoriaTestadors\RelationManagers\TestadoresRelationManager;
use App\Filament\Resources\CategoriaTestadors\Schemas\CategoriaTestadorForm;
use App\Filament\Resources\CategoriaTestadors\Tables\CategoriaTestadorsTable;
use App\Models\CategoriaTestador;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CategoriaTestadorResource extends Resource
{
    protected static ?string $model = CategoriaTestador::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 102;

    protected static ?string $slug = 'categorias-testadores';

    protected static ?string $modelLabel = 'Categoria de Testadores';

    protected static ?string $pluralModelLabel = 'Categorias de Testadores';

    public static function form(Schema $schema): Schema
    {
        return CategoriaTestadorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriaTestadorsTable::configure($table);
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
            'index' => ListCategoriaTestadors::route('/'),
            'create' => CreateCategoriaTestador::route('/create'),
            'edit' => EditCategoriaTestador::route('/{record}/edit'),
        ];
    }
}
