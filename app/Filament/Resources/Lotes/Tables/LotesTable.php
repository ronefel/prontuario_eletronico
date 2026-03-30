<?php

namespace App\Filament\Resources\Lotes\Tables;

use Carbon\Carbon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class LotesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('produto.nome')
                    ->label('Produto')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(fn (string $state): ?string => mb_strlen($state) > 50 ? $state : null),
                TextColumn::make('numero_lote')
                    ->label('N° Lote')
                    ->searchable()
                    ->limit(18)
                    ->tooltip(fn (string $state): ?string => mb_strlen($state) > 18 ? $state : null),
                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y')
                    ->sortable(),
                TextColumn::make('data_validade')
                    ->label('Validade')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(function ($record) {
                        $date = Carbon::parse($record->getRawOriginal('data_validade'), Auth::user()->timezone);

                        if ($date->endOfDay()->isPast()) {
                            return 'danger';
                        }
                        if ($date->addDays(-30)->startOfDay()->isPast()) {
                            return 'warning';
                        }
                        if ($date->isFuture()) {
                            return 'success';
                        }
                    }),
                TextColumn::make('movimentacoes_sum_quantidade')
                    ->label('Qtd atual')
                    ->sortable(),
                TextColumn::make('status'),
            ])
            ->defaultSort('produto.nome')
            ->filters([
                SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
                SelectFilter::make('fornecedor')
                    ->relationship('fornecedor', 'nome'),
                SelectFilter::make('local')
                    ->relationship('local', 'nome'),
            ])
            ->recordActions([
                EditAction::make()->hiddenLabel(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
