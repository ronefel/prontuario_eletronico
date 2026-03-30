<?php

namespace App\Filament\Widgets;

use App\Models\Aplicacao;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

class AplicacoesDoDia extends TableWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(new HtmlString(Blade::render('<div class="flex items-center gap-2"><x-heroicon-o-calendar class="h-5 w-5" /> Aplicações do Dia</div>')))
            ->query(
                Aplicacao::query()
                    ->with(['tratamento.paciente'])
                    ->whereDate('data_aplicacao', now())
                    ->orderBy('data_aplicacao')
            )
            ->columns([
                TextColumn::make('data_aplicacao')
                    ->label('Horário')
                    ->timezone(Auth::user()->timezone)
                    ->dateTime('H:i'),
                TextColumn::make('tratamento.paciente.nome')
                    ->label('Paciente'),
                TextColumn::make('tratamento.nome')
                    ->label('Tratamento'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agendada' => 'warning',
                        'aplicada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
            ])
            ->recordUrl(fn (Aplicacao $record): string => route('filament.admin.resources.tratamentos.edit', ['record' => $record->tratamento_id]))
            ->emptyStateHeading('Nenhuma aplicação para hoje')
            ->paginated(false);
    }
}
