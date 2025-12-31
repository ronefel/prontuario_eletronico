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
            ->query(function () {
                $atendimentos = DB::table('prontuarios')
                    ->select('paciente_id', 'data as data_atendimento')
                    ->unionAll(
                        DB::table('exames')
                            ->select('paciente_id', 'data as data_atendimento')
                    )
                    ->unionAll(
                        DB::table('aplicacoes')
                            ->join('tratamentos', 'aplicacoes.tratamento_id', '=', 'tratamentos.id')
                            ->select('tratamentos.paciente_id', 'aplicacoes.data_aplicacao as data_atendimento')
                    );

                return Paciente::query()
                    ->select('pacientes.*', DB::raw('MAX(atendimentos.data_atendimento) as data'))
                    ->joinSub($atendimentos, 'atendimentos', 'pacientes.id', '=', 'atendimentos.paciente_id')
                    ->groupBy('pacientes.id')
                    ->orderBy('data', 'desc')
                    ->limit(5);
            })
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
