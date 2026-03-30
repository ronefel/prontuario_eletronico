<?php

namespace App\Filament\Resources\Tratamentos\Tables;

use App\Models\Tratamento;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TratamentosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('nome')
                    ->label('Tratamento')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('data_fim')
                    ->label('Data de Fim')
                    ->date('d/m/Y'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'planejado' => 'gray',
                        'em_andamento' => 'warning',
                        'concluido' => 'success',
                        'excluido' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('progresso')
                    ->label('Progresso')
                    ->tooltip('Aplicada/Total')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('observacao')
                    ->label('Observações')
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column content exceeds the length limit.
                        return $state;
                    }),
            ])
            ->defaultSort('data_inicio')
            ->filters([
                TrashedFilter::make()->native(false),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'planejado' => 'Planejado',
                        'em_andamento' => 'Em Andamento',
                        'concluido' => 'Concluído',
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['value'])) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'planejado' => $query->where(function ($q) {
                                $q->doesntHave('aplicacoes')
                                    ->orWhereDoesntHave('aplicacoes', fn ($sq) => $sq->where('status', 'aplicada'));
                            }),
                            'concluido' => $query->whereHas('aplicacoes', fn ($q) => $q->where('status', 'aplicada'))
                                ->whereDoesntHave('aplicacoes', fn ($q) => $q->where('status', '!=', 'aplicada')),
                            'em_andamento' => $query->whereHas('aplicacoes', fn ($q) => $q->where('status', 'aplicada'))
                                ->whereHas('aplicacoes', fn ($q) => $q->where('status', '!=', 'aplicada')),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar'),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn (Tratamento $record) => $record->status === 'planejado'),
                RestoreAction::make()
                    ->hiddenLabel()
                    ->tooltip('Restaurar'),
                ForceDeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir Definitivamente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->paginated(false);
    }
}
