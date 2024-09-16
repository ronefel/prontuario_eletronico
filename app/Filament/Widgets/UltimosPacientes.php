<?php

namespace App\Filament\Widgets;

use App\Models\Paciente;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Lista os últimos pacientes atendidos no prontuário
 */
class UltimosPacientes extends BaseWidget
{

    public function table(Table $table): Table
    {
        return $table
            ->heading('Últimos 5 Pacientes Atendidos')
            ->query(
                Paciente::query()
                    ->select('pacientes.*', DB::raw('MAX(prontuarios.data) as data'))
                    ->join('prontuarios', 'pacientes.id', '=', 'prontuarios.paciente_id')
                    ->groupBy('pacientes.id')
                    ->orderBy('data', 'desc')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('nome')
                    ->weight(FontWeight::Bold),
                TextColumn::make('data')
                    ->label('Data Atendimento')
                    ->date('d/m/Y')
                    ->timezone(Auth::user()->timezone)
            ])->paginated(false);
    }
}
