<?php

namespace App\Filament\Resources\NotasFiscais;

use App\Filament\Resources\NotasFiscais\Pages\CreateNotaFiscal;
use App\Filament\Resources\NotasFiscais\Pages\EditNotaFiscal;
use App\Filament\Resources\NotasFiscais\Pages\ListNotasFiscais;
use App\Filament\Resources\NotasFiscais\Schemas\NotaFiscalForm;
use App\Filament\Resources\NotasFiscais\Tables\NotasFiscaisTable;
use App\Models\NotaFiscal;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NotaFiscalResource extends Resource
{
    protected static ?string $model = NotaFiscal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Financeiro';

    protected static ?int $navigationSort = 301;

    protected static ?string $modelLabel = 'Nota Fiscal';

    protected static ?string $pluralModelLabel = 'Notas Fiscais';

    public static function form(Schema $schema): Schema
    {
        return NotaFiscalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotasFiscaisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotasFiscais::route('/'),
            'create' => CreateNotaFiscal::route('/create'),
            'edit' => EditNotaFiscal::route('/{record}/edit'),
        ];
    }
}
