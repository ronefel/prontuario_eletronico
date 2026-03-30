<?php

namespace App\Filament\Resources\Movimentacoes;

use App\Filament\Resources\Movimentacoes\Pages\CreateMovimentacao;
use App\Filament\Resources\Movimentacoes\Pages\EditMovimentacao;
use App\Filament\Resources\Movimentacoes\Pages\ListMovimentacoes;
use App\Filament\Resources\Movimentacoes\Schemas\MovimentacaoForm;
use App\Filament\Resources\Movimentacoes\Tables\MovimentacoesTable;
use App\Models\Movimentacao;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MovimentacaoResource extends Resource
{
    protected static ?string $model = Movimentacao::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $modelLabel = 'Movimentação';

    protected static ?string $pluralModelLabel = 'Movimentações';

    protected static ?string $navigationLabel = 'Movimentações';

    protected static string|UnitEnum|null $navigationGroup = 'Estoque';

    protected static ?int $navigationSort = 207;

    public static function form(Schema $schema): Schema
    {
        return MovimentacaoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MovimentacoesTable::configure($table);
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
            'index' => ListMovimentacoes::route('/'),
            'create' => CreateMovimentacao::route('/create'),
            'edit' => EditMovimentacao::route('/{record}/edit'),
        ];
    }
}
