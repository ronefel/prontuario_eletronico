<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use App\Models\NotaFiscal;
use App\Services\NotaFiscal\EmissorNfseService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ViewNotaFiscal extends ViewRecord
{
    protected static string $resource = NotaFiscalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('emitirNfse')
                ->label('Transmitir / Emitir NFS-e')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Transmitir Nota Fiscal para o WebISS?')
                ->modalDescription('A nota fiscal será assinada com o certificado A1 e enviada para processamento junto ao WebService da Prefeitura.')
                ->visible(fn (): bool => in_array($this->record->status, ['rascunho', 'rejeitada']))
                ->action(function (EmissorNfseService $emissor): void {
                    /** @var NotaFiscal $notaFiscal */
                    $notaFiscal = $this->record;

                    try {
                        $emissor->emitir($notaFiscal);

                        if ($notaFiscal->status === 'autorizada') {
                            Notification::make()
                                ->title('NFS-e Emitida com Sucesso!')
                                ->body("Nota Fiscal nº {$notaFiscal->numero_nfse} autorizada com sucesso.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('NFS-e Rejeitada pela Prefeitura')
                                ->body($notaFiscal->mensagem_erro ?? 'Ocorreu um erro no processamento da nota.')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Falha na Emissão da NFS-e')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }

                    $this->refreshFormData(['status', 'numero_nfse', 'codigo_verificacao', 'data_emissao_nfse', 'mensagem_erro']);
                }),

            Action::make('downloadXml')
                ->label('Baixar XML')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(fn (): bool => ! empty($this->record->xml_envio) || ! empty($this->record->xml_rps))
                ->action(function (): StreamedResponse {
                    /** @var NotaFiscal $notaFiscal */
                    $notaFiscal = $this->record;

                    $conteudoXml = $notaFiscal->xml_envio ?: $notaFiscal->xml_rps ?: '';
                    $nomeArquivo = 'nfse_rps_'.$notaFiscal->numero_rps.'.xml';

                    return response()->streamDownload(
                        fn () => print ($conteudoXml),
                        $nomeArquivo,
                        ['Content-Type' => 'application/xml']
                    );
                }),

            EditAction::make()
                ->visible(fn (): bool => in_array($this->record->status, ['rascunho', 'rejeitada'])),
            DeleteAction::make()
                ->visible(fn (): bool => in_array($this->record->status, ['rascunho', 'rejeitada'])),
        ];
    }
}
