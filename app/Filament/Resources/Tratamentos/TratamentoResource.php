<?php

namespace App\Filament\Resources\Tratamentos;

use App\Filament\Resources\Tratamentos\Pages\CreateTratamento;
use App\Filament\Resources\Tratamentos\Pages\EditTratamento;
use App\Filament\Resources\Tratamentos\Pages\ListTratamentos;
use App\Filament\Resources\Tratamentos\RelationManagers\AplicacoesRelationManager;
use App\Filament\Resources\Tratamentos\Schemas\TratamentoForm;
use App\Filament\Resources\Tratamentos\Tables\TratamentosTable;
use App\Models\Tratamento;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TratamentoResource extends Resource
{
    protected static ?string $model = Tratamento::class;

    protected static bool $isGloballySearchable = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getBreadcrumb(): string
    {
        return '';
    }

    public static function getGlobalSearchLabel(): ?string
    {
        return null;
    }

    public static function getLabel(): ?string
    {
        return ' ';
    }

    public static function form(Schema $schema): Schema
    {
        return TratamentoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TratamentosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AplicacoesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTratamentos::route('/'),
            'list' => ListTratamentos::route('/{pacienteId}'),
            'create' => CreateTratamento::route('/create/{pacienteId}'),
            'edit' => EditTratamento::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
