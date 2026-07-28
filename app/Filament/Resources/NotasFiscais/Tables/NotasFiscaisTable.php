<?php

namespace App\Filament\Resources\NotasFiscais\Tables;

use App\Models\NotaFiscal;
use App\Services\NotaFiscal\EmissorNfseService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->formatStateUsing(fn ($record) => $record->numero_rps ? "{$record->numero_rps}/{$record->serie_rps}" : '-')
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
                    ->color(fn (string $state): string => match ($state) {
                        'autorizada' => 'success',
                        'processando' => 'warning',
                        'rejeitada' => 'danger',
                        'cancelada' => 'gray',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (NotaFiscal $record): bool => $record->status === 'rascunho'),
                Action::make('emitir')
                    ->label('Emitir NFS-e')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (NotaFiscal $record): bool => in_array($record->status, ['rascunho', 'rejeitada']))
                    ->action(function (NotaFiscal $record) {
                        try {
                            $emissor = app(EmissorNfseService::class);
                            $emissor->emitir($record);

                            if ($record->status === 'autorizada') {
                                Notification::make()
                                    ->success()
                                    ->title('NFS-e Emitida com Sucesso!')
                                    ->body("Nota Fiscal de Serviço nº {$record->numero_nfse} autorizada.")
                                    ->send();
                            } else {
                                Notification::make()
                                    ->danger()
                                    ->title('NFS-e Rejeitada pela Prefeitura')
                                    ->body($record->mensagem_erro ?? 'Erro no processamento da NFS-e.')
                                    ->persistent()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->danger()
                                ->title('Falha na Emissão da NFS-e')
                                ->body($e->getMessage())
                                ->persistent()
                                ->send();
                        }
                    }),

                Action::make('baixarXml')
                    ->label('Download XML')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->visible(fn (NotaFiscal $record): bool => ! empty($record->xml_envio) || ! empty($record->xml_retorno))
                    ->action(function (NotaFiscal $record) {
                        $conteudoXml = $record->xml_retorno ?: $record->xml_envio;
                        $nomeArquivo = "NFSE_{$record->numero_nfse}_RPS_{$record->numero_rps}.xml";

                        return response()->streamDownload(function () use ($conteudoXml) {
                            echo $conteudoXml;
                        }, $nomeArquivo, ['Content-Type' => 'text/xml']);
                    }),
            ]);
    }
}
