<?php

namespace App\Filament\Resources\ExamesLaboratoriais;

use App\Filament\Resources\ExamesLaboratoriais\Pages\CreateExameLaboratorial;
use App\Filament\Resources\ExamesLaboratoriais\Pages\EditExameLaboratorial;
use App\Filament\Resources\ExamesLaboratoriais\Pages\ListExamesLaboratoriais;
use App\Filament\Resources\ExamesLaboratoriais\Schemas\ExameLaboratorialForm;
use App\Filament\Resources\ExamesLaboratoriais\Tables\ExamesLaboratoriaisTable;
use App\Models\ExameLaboratorial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ExameLaboratorialResource extends Resource
{
    protected static ?string $model = ExameLaboratorial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Catálogo de Exames';

    protected static ?string $modelLabel = 'Exame Laboratorial';

    protected static ?string $pluralModelLabel = 'Exames Laboratoriais';

    protected static string|UnitEnum|null $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 106;

    public static function form(Schema $schema): Schema
    {
        return ExameLaboratorialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExamesLaboratoriaisTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExamesLaboratoriais::route('/'),
            'create' => CreateExameLaboratorial::route('/create'),
            'edit' => EditExameLaboratorial::route('/{record}/edit'),
        ];
    }
}
