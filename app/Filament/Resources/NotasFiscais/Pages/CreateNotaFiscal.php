<?php

namespace App\Filament\Resources\NotasFiscais\Pages;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use App\Models\ConfiguracaoNotaFiscal;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateNotaFiscal extends CreateRecord
{
    protected static string $resource = NotaFiscalResource::class;

    public function mount(): void
    {
        parent::mount();

        if (! ConfiguracaoNotaFiscal::obterConfiguracaoAtiva()) {
            Notification::make()
                ->title('Configuração Fiscal Pendente')
                ->body('É necessário cadastrar as Configurações de NFS-e da clínica antes de gerar notas fiscais.')
                ->warning()
                ->persistent()
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        $proximoRps = ($configuracao?->ultimo_numero_rps ?? 0) + 1;

        $data['user_id'] = auth()->id();
        $data['numero_rps'] = $proximoRps;
        $data['serie_rps'] = $configuracao?->serie_rps ?? '1';
        $data['tipo_rps'] = 1;
        $data['status'] = 'rascunho';
        $data['data_emissao_rps'] = now();
        $data['codigo_municipio_ibge'] = $configuracao?->codigo_municipio_ibge ?? '1100049';

        if (! empty($data['item_lista_servico']) && ! empty($configuracao?->atividades)) {
            foreach ($configuracao->atividades as $chave => $act) {
                $codigoServico = is_array($act) ? ($act['item_lista_servico'] ?? (string) $chave) : (string) $chave;
                if ($codigoServico === $data['item_lista_servico']) {
                    $data['codigo_tributacao_municipio'] = is_array($act) ? ($act['codigo_tributacao_municipio'] ?? $codigoServico) : $codigoServico;
                    break;
                }
            }
        }

        if (empty($data['codigo_tributacao_municipio'])) {
            $data['codigo_tributacao_municipio'] = $data['item_lista_servico'] ?? $configuracao?->codigo_tributacao_municipio;
        }

        if ($configuracao) {
            $configuracao->increment('ultimo_numero_rps');
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
