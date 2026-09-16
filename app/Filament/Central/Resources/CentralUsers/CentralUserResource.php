<?php

namespace App\Filament\Central\Resources\CentralUsers;

use App\Filament\Central\Resources\CentralUsers\Pages\CreateCentralUser;
use App\Filament\Central\Resources\CentralUsers\Pages\EditCentralUser;
use App\Filament\Central\Resources\CentralUsers\Pages\ListCentralUsers;
use App\Filament\Central\Resources\CentralUsers\Schemas\CentralUserForm;
use App\Filament\Central\Resources\CentralUsers\Tables\CentralUsersTable;
use App\Models\CentralUser;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CentralUserResource extends Resource
{
    protected static ?string $model = CentralUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Administradores';

    protected static ?string $pluralModelLabel = 'Administradores';

    protected static ?string $modelLabel = 'Administrador';

    protected static string|UnitEnum|null $navigationGroup = 'Gestão SaaS';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CentralUserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CentralUsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCentralUsers::route('/'),
            'create' => CreateCentralUser::route('/create'),
            'edit' => EditCentralUser::route('/{record}/edit'),
        ];
    }
}
