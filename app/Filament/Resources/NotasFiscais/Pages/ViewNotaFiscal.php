<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use App\Services\NotaFiscal\EmissorNfseService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

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
                ->visible(fn(): bool => in_array($this->record->status, ['rascunho', 'rejeitada']))
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

            Action::make('cancelarNfse')
                ->label('Cancelar NFS-e')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn(): bool => $this->record->ehAutorizada())
                ->modalHeading('Cancelar Nota Fiscal de Serviço')
                ->modalDescription('Atenção: O cancelamento da NFS-e será enviado diretamente ao WebService da prefeitura.')
                ->schema([
                    Select::make('codigo_cancelamento')
                        ->label('Motivo / Código do Cancelamento')
                        ->options([
                            '1' => 'Erro na Emissão',
                            '2' => 'Serviço Não Prestado',
                            '4' => 'Duplicidade da Nota',
                        ])
                        ->default('1')
                        ->required(),
                    Textarea::make('motivo_cancelamento')
                        ->label('Justificativa do Cancelamento')
                        ->placeholder('Descreva a justificativa para o cancelamento')
                        ->rows(3),
                ])
                ->action(function (array $data, EmissorNfseService $emissor): void {
                    /** @var NotaFiscal $notaFiscal */
                    $notaFiscal = $this->record;

                    try {
                        $emissor->cancelar($notaFiscal, $data['codigo_cancelamento'], $data['motivo_cancelamento'] ?? null);

                        Notification::make()
                            ->success()
                            ->title('NFS-e Cancelada com Sucesso!')
                            ->body("Nota Fiscal nº {$notaFiscal->numero_nfse} foi cancelada.")
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Falha no Cancelamento da NFS-e')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                    }

                    $this->refreshFormData(['status', 'codigo_cancelamento', 'motivo_cancelamento', 'data_cancelamento', 'mensagem_erro']);
                }),

            Action::make('substituirNfse')
                ->label('Substituir NFS-e')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn(): bool => $this->record->ehAutorizada())
                ->modalHeading('Substituir Nota Fiscal de Serviço')
                ->modalDescription(fn(): string => "Será gerada uma nova NFS-e em substituição à NFS-e nº {$this->record->numero_nfse}.")
                ->schema([
                    Select::make('codigo_cancelamento')
                        ->label('Motivo da Substituição (Cancelamento da Nota Antiga)')
                        ->options([
                            '1' => 'Erro na Emissão',
                            '2' => 'Serviço Não Prestado',
                            '4' => 'Duplicidade da Nota',
                        ])
                        ->default('1')
                        ->required(),
                    TextInput::make('valor_servicos')
                        ->label('Novo Valor do Serviço (R$)')
                        ->numeric()
                        ->prefix('R$')
                        ->default(fn(): float => (float) $this->record->valor_servicos)
                        ->required(),
                    Textarea::make('discriminacao_servico')
                        ->label('Nova Discriminação dos Serviços')
                        ->default(fn(): string => (string) $this->record->discriminacao_servico)
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data, EmissorNfseService $emissor) {
                    /** @var NotaFiscal $notaFiscal */
                    $notaFiscal = $this->record;

                    try {
                        $novaNota = $notaFiscal->replicate([
                            'numero_rps',
                            'numero_nfse',
                            'codigo_verificacao',
                            'data_emissao_nfse',
                            'data_cancelamento',
                            'codigo_cancelamento',
                            'motivo_cancelamento',
                            'nota_fiscal_substituida_id',
                            'nota_fiscal_substituta_id',
                            'xml_rps',
                            'xml_envio',
                            'xml_retorno',
                            'codigo_erro',
                            'mensagem_erro',
                        ]);

                        $novaNota->status = 'rascunho';
                        $novaNota->valor_servicos = $data['valor_servicos'];
                        $novaNota->discriminacao_servico = $data['discriminacao_servico'];
                        $novaNota->user_id = auth()->id();

                        $emissor->substituir($notaFiscal, $novaNota, $data['codigo_cancelamento']);

                        Notification::make()
                            ->success()
                            ->title('NFS-e Substituída com Sucesso!')
                            ->body("Nova Nota Fiscal nº {$novaNota->numero_nfse} emitida em substituição à NFS-e nº {$notaFiscal->numero_nfse}.")
                            ->send();

                        return redirect(NotaFiscalResource::getUrl('view', ['record' => $novaNota->id]));
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->danger()
                            ->title('Falha na Substituição da NFS-e')
                            ->body($e->getMessage())
                            ->persistent()
                            ->send();
                    }
                }),

            Action::make('downloadXml')
                ->label('Baixar XML')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->visible(fn(): bool => !empty($this->record->xml_retorno) || !empty($this->record->xml_envio) || !empty($this->record->xml_rps))
                ->action(function (): StreamedResponse {
                    /** @var NotaFiscal $notaFiscal */
                    $notaFiscal = $this->record;

                    $conteudoXml = $notaFiscal->obterXmlDownload() ?? '';
                    $nomeArquivo = "NFSE_{$notaFiscal->numero_nfse}_RPS_{$notaFiscal->numero_rps}.xml";

                    return response()->streamDownload(
                        fn() => print ($conteudoXml),
                        $nomeArquivo,
                        ['Content-Type' => 'application/xml']
                    );
                }),

            Action::make('imprimir')
                ->label('Imprimir Nota Fiscal')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (): string => route('notas-fiscais.impressao', ['id' => $this->record->id]))
                ->openUrlInNewTab(),

            EditAction::make()
                ->visible(fn(): bool => in_array($this->record->status, ['rascunho', 'rejeitada'])),
            DeleteAction::make()
                ->visible(fn(): bool => in_array($this->record->status, ['rascunho', 'rejeitada'])),
        ];
    }
}
