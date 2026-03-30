<?php

namespace App\Filament\Resources\Movimentacoes\Tables;

use App\Filament\Resources\Movimentacoes\Pages\EditMovimentacao;
use App\Models\Movimentacao;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MovimentacoesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('tipo'),
                TextColumn::make('produto.nome')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lote.numero_lote')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('quantidade')
                    ->colors([
                        'danger' => fn ($record) => $record->quantidade < 0,
                        'success' => fn ($record) => $record->quantidade >= 0,
                    ]),
                TextColumn::make('data_movimentacao')
                    ->dateTime('d/m/Y H:i', Auth::user()->timezone)
                    ->sortable(),
                TextColumn::make('motivo')
                    ->html()
                    ->wrap(),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('tipo')
                    ->options([
                        'entrada' => 'Entrada',
                        'saida' => 'Saída',
                        'ajuste' => 'Ajuste',
                        'transferencia' => 'Transferência',
                    ]),
                SelectFilter::make('produto')
                    ->relationship('produto', 'nome'),
            ])
            ->recordActions([
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar')
                    ->visible(fn ($record) => $record->is_manual),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir')
                    ->visible(fn ($record) => $record->is_manual),
            ])
            ->recordUrl(fn (Movimentacao $record): ?string => $record->is_manual ? EditMovimentacao::getUrl(['record' => $record]) : null);
    }
}
