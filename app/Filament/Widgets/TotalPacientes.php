<?php

namespace App\Filament\Widgets;

use App\Models\Paciente;
use App\Models\Prontuario;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class TotalPacientes extends BaseWidget
{
    protected function getStats(): array
    {
        // Defina a data de início e fim para os últimos 30 dias
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Consulta para contar pacientes únicos por dia no último mês
        $dailyPatientCounts = Prontuario::query()
            ->select(DB::raw('DATE(data) as date'), DB::raw('COUNT(DISTINCT paciente_id) as unique_patients'))
            ->whereBetween('data', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(data)'))
            ->get();

        return [
            Stat::make('Pacientes', Paciente::query()->count()),
            Stat::make('Atendimentos (Últimos 30 Dias)', $dailyPatientCounts->sum('unique_patients')),
        ];
    }
}
