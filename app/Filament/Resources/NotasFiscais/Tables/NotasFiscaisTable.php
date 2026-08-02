<?php

namespace App\Filament\Resources\NotasFiscais\Tables;

use App\Models\NotaFiscal;
use App\Services\NotaFiscal\EmissorNfseService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotasFiscaisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('numero_rps')
                    ->label('RPS / Série')
                    ->formatStateUsing(fn($record) => $record->numero_rps ? "{$record->numero_rps}/{$record->serie_rps}" : '-')
                    ->sortable(),

                TextColumn::make('numero_nfse')
                    ->label('Nº NFS-e')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('paciente.nome')
                    ->label('Tomador (Paciente)')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('valor_servicos')
                    ->label('Valor (R$)')
                    ->money('BRL')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'autorizada' => 'success',
                        'processando' => 'warning',
                        'rejeitada' => 'danger',
                        'cancelada' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'autorizada' => 'Autorizada',
                        'processando' => 'Processando',
                        'rejeitada' => 'Rejeitada',
                        'cancelada' => 'Cancelada',
                        default => 'Rascunho',
                    }),

                TextColumn::make('data_emissao_nfse')
                    ->label('Data Emissão')
                    ->dateTime('d/m/Y H:i', timezone: auth()->user()?->timezone)
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->defaultSort('numero_nfse', direction: 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'rascunho' => 'Rascunho',
                        'processando' => 'Processando',
                        'autorizada' => 'Autorizada',
                        'rejeitada' => 'Rejeitada',
                        'cancelada' => 'Cancelada',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()
                    ->hiddenLabel()
                    ->tooltip('Visualizar Nota Fiscal'),
                Action::make('imprimir')
                    ->hiddenLabel()
                    ->icon('heroicon-o-printer')
                    ->color('secondary')
                    ->tooltip('Imprimir Nota Fiscal')
                    ->url(fn (NotaFiscal $record): string => route('notas-fiscais.impressao', ['id' => $record->id]))
                    ->openUrlInNewTab(),
                EditAction::make()
                    ->hiddenLabel()
                    ->tooltip('Editar Nota Fiscal')
                    ->visible(fn(NotaFiscal $record): bool => in_array($record->status, ['rascunho', 'rejeitada'])),
                DeleteAction::make()
                    ->hiddenLabel()
                    ->tooltip('Excluir Nota Fiscal')
                    ->visible(fn(NotaFiscal $record): bool => in_array($record->status, ['rascunho', 'rejeitada'])),
            ]);
    }
}
