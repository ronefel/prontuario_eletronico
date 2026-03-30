<?php

namespace App\Filament\Resources\Inventarios\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class InventariosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('data_inventario')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->size('md')
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completo' => 'Completo',
                        'por_local' => 'Por Local',
                        'por_produto' => 'Por Produto',
                        default => $state,
                    }),
                TextColumn::make('lotes_count')
                    ->label('Lotes Contados')
                    ->counts('lotes'),
                TextColumn::make('discrepancia_total')
                    ->getStateUsing(fn ($record) => $record->inventarioLotes()->sum(DB::raw('ABS(discrepancia)')))
                    ->color(fn ($state) => $state != 0 ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->badge()
                    ->size('md')
                    ->color(fn (string $state): string => match ($state) {
                        'pendente' => 'warning',
                        'aprovado' => 'success',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'pendente' => 'heroicon-m-clock',
                        'aprovado' => 'heroicon-m-check-badge',
                        default => 'heroicon-m-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        default => $state,
                    }),
                TextColumn::make('user.name'),
            ])
            ->filters([
                // TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pendente'),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'pendente'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
