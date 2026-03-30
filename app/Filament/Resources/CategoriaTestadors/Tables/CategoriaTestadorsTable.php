<?php

namespace App\Filament\Resources\CategoriaTestadors\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriaTestadorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nome')
                    ->searchable(),
                TextColumn::make('ordem')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('ordem')
            ->recordActions([
                EditAction::make()->hiddenLabel(),
            ]);
    }
}
