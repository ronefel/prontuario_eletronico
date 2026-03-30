<?php

namespace App\Filament\Resources\Produtos\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProdutosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->sortable()
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn (string $state): ?string => mb_strlen($state) > 50 ? $state : null),
                TextColumn::make('categoria.nome'),
                TextColumn::make('movimentacoes_sum_quantidade')
                    ->label('Estoque Total')
                    ->sortable(),
                TextColumn::make('valor_unitario_referencia')->money('BRL'),
                TextColumn::make('estoque_minimo')
                    ->color(fn ($record) => $record->movimentacoes_sum_quantidade < $record->estoque_minimo ? 'danger' : 'success'),
            ])
            ->defaultSort('nome')
            ->filters([
                SelectFilter::make('categoria')->relationship('categoria', 'nome'),
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
