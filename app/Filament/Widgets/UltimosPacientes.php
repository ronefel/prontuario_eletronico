<?php

namespace App\Filament\Widgets;

use App\Models\Paciente;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Lista os últimos pacientes atendidos no prontuário
 */
class UltimosPacientes extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-users class="h-5 w-5" /> Últimos 5 Pacientes Atendidos</div>')))
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
                    ->timezone(Auth::user()->timezone),
            ])
            ->recordUrl(
                fn (Paciente $record): string => route('filament.admin.pages.consultorio.{paciente}', ['paciente' => $record->id]),
            )
            ->defaultSort('data', 'desc')
            ->paginated(false);
    }
}
