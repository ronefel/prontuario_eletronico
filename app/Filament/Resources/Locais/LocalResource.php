<?php

namespace App\Filament\Resources\Locais;

use App\Filament\Resources\Locais\Pages\CreateLocal;
use App\Filament\Resources\Locais\Pages\EditLocal;
use App\Filament\Resources\Locais\Pages\ListLocais;
use App\Filament\Resources\Locais\Schemas\LocaisForm;
use App\Filament\Resources\Locais\Tables\LocaisTable;
use App\Models\Local;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class LocalResource extends Resource
{
    protected static ?string $model = Local::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Estoque';

    protected static ?string $modelLabel = 'Local';

    protected static ?string $pluralModelLabel = 'Locais';

    protected static ?string $navigationLabel = 'Locais';

    protected static ?int $navigationSort = 203;

    public static function form(Schema $schema): Schema
    {
        return LocaisForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LocaisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLocais::route('/'),
            'create' => CreateLocal::route('/create'),
            'edit' => EditLocal::route('/{record}/edit'),
        ];
    }
}
