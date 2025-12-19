<?php

namespace App\Filament\Widgets;

use App\Models\Aplicacao;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class AplicacoesDoDia extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Aplicações do Dia')
            ->query(
                Aplicacao::query()
                    ->with(['tratamento.paciente'])
                    ->whereDate('data_aplicacao', now())
                    ->orderBy('data_aplicacao')
            )
            ->columns([
                Tables\Columns\TextColumn::make('data_aplicacao')
                    ->label('Horário')
                    ->dateTime('H:i'),
                Tables\Columns\TextColumn::make('tratamento.paciente.nome')
                    ->label('Paciente'),
                Tables\Columns\TextColumn::make('tratamento.nome')
                    ->label('Tratamento'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'agendada' => 'warning',
                        'aplicada' => 'success',
                        'cancelada' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(str_replace('_', ' ', $state))),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_tratamento')
                    ->label('Ver Tratamento')
                    ->url(fn (Aplicacao $record): string => route('filament.admin.resources.tratamentos.edit', ['record' => $record->tratamento_id]))
                    ->icon('heroicon-m-eye'),
            ])
            ->emptyStateHeading('Nenhuma aplicação para hoje')
            ->paginated(false);
    }
}
