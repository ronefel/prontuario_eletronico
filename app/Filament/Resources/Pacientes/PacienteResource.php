<?php

namespace App\Filament\Resources\Pacientes;

use App\Filament\Resources\Pacientes\Pages\CreatePaciente;
use App\Filament\Resources\Pacientes\Pages\EditPaciente;
use App\Filament\Resources\Pacientes\Pages\ListPacientes;
use App\Filament\Resources\Pacientes\Pages\ProntuarioPaciente;
use App\Filament\Resources\Pacientes\Schemas\PacienteForm;
use App\Filament\Resources\Pacientes\Tables\PacientesTable;
use App\Models\Paciente;
use BackedEnum;
use Carbon\Carbon;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = null;

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['nome', 'cpf', 'nascimento'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        /** @var Paciente $record */
        return $record->nome.' ('.$record->nascimento->format('d/m/Y').')';
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        /** @var Paciente $record */
        return route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $record->id]);
    }

    // Personalizar a query de busca global
    public static function modifyGlobalSearchQuery(Builder $query, string $search): void
    {
        // Formata CPF no banco para somente números
        $query->orWhereRaw("regexp_replace(cpf, '\D', '', 'g') ILIKE ?", ["%{$search}%"]);

        // Formata data no banco para DDMMYYYY
        $query->orWhereRaw("to_char(nascimento, 'DDMMYYYY') ILIKE ?", ["%{$search}%"]);

        // Tentar converter a entrada para data no formato d/m/Y
        try {
            $date = Carbon::createFromFormat('d/m/Y', $search);
            if ($date) {
                $query->orWhereDate('nascimento', $date->format('Y-m-d'));
            }
        } catch (\Exception $e) {
            // Ignorar se não for uma data válida
        }
    }

    public static function form(Schema $schema): Schema
    {
        return PacienteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PacientesTable::configure($table);
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
            'index' => ListPacientes::route('/'),
            'create' => CreatePaciente::route('/create'),
            'edit' => EditPaciente::route('/{record}/edit'),
            'prontuario' => ProntuarioPaciente::route('/{record}/prontuario'),
        ];
    }
}
