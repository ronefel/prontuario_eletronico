<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use DOMDocument;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EmissorNfseService
{
    public function __construct(
        protected GeradorXmlRpsService $geradorXml,
        protected AssinadorXmlService $assinadorXml,
        protected ValidadorXmlService $validadorXml,
        protected ClienteNfseSoapService $clienteSoap,
    ) {}

    /**
     * Processa a emissão da NFS-e para um registro de NotaFiscal.
     *
     * @throws Exception
     */
    public function emitir(NotaFiscal $notaFiscal): NotaFiscal
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        if (! $configuracao) {
            throw new Exception('Nenhuma configuração de nota fiscal cadastrada no sistema.');
        }

        return DB::transaction(function () use ($notaFiscal, $configuracao) {
            // 1. Atribuir próximo número de RPS se ainda não tiver
            if (! $notaFiscal->numero_rps) {
                $novoNumeroRps = $configuracao->ultimo_numero_rps + 1;
                $configuracao->ultimo_numero_rps = $novoNumeroRps;
                $configuracao->save();

                $notaFiscal->numero_rps = $novoNumeroRps;
                $notaFiscal->serie_rps = $configuracao->serie_rps ?: '1';
                $notaFiscal->data_emissao_rps = now();
            }

            $notaFiscal->status = 'processando';
            $notaFiscal->mensagem_erro = null;
            $notaFiscal->codigo_erro = null;
            $notaFiscal->save();

            // 2. Gerar XML do RPS
            $xmlRps = $this->geradorXml->gerarXml($notaFiscal, $configuracao);
            $notaFiscal->xml_rps = $xmlRps;

            // 3. Assinar XML se houver certificado
            $xmlEnvio = $xmlRps;
            if ($configuracao->caminho_certificado) {
                try {
                    $xmlEnvio = $this->assinadorXml->assinar($xmlRps, 'InfDeclaracaoPrestacaoServico', 'Id');
                } catch (Exception $e) {
                    $notaFiscal->status = 'rejeitada';
                    $notaFiscal->mensagem_erro = 'Erro na assinatura digital: '.$e->getMessage();
                    $notaFiscal->save();
                    throw $e;
                }
            }
            $notaFiscal->xml_envio = $xmlEnvio;

            // 4. Validar XSD
            $validacao = $this->validadorXml->validar($xmlEnvio);
            if (! $validacao['valido']) {
                $errosTexto = implode(' | ', $validacao['erros']);
                $notaFiscal->status = 'rejeitada';
                $notaFiscal->codigo_erro = 'VALIDACAO_XSD';
                $notaFiscal->mensagem_erro = 'Erro de esquema XSD: '.$errosTexto;
                $notaFiscal->save();

                throw new Exception('XML inválido no esquema ABRASF XSD: '.$errosTexto);
            }

            // 5. Transmitir ao WebService
            try {
                $respostaXml = $this->clienteSoap->enviar('GerarNfse', $xmlEnvio);
                $notaFiscal->xml_retorno = $respostaXml;

                // 6. Processar resposta XML
                $this->processarResposta($notaFiscal, $respostaXml);
            } catch (Exception $e) {
                $notaFiscal->status = 'rejeitada';
                $notaFiscal->mensagem_erro = 'Erro de transmissão: '.$e->getMessage();
                $notaFiscal->save();

                throw $e;
            }

            return $notaFiscal;
        });
    }

    /**
     * Processa a resposta do WebService da prefeitura e atualiza o modelo NotaFiscal.
     */
    private function processarResposta(NotaFiscal $notaFiscal, string $respostaXml): void
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;

        if (! $dom->loadXML($respostaXml)) {
            $notaFiscal->status = 'rejeitada';
            $notaFiscal->mensagem_erro = 'Resposta XML inválida da prefeitura.';
            $notaFiscal->save();

            return;
        }

        // Buscar Número da NFS-e e Código de Verificação
        $nosNumero = $dom->getElementsByTagName('Numero');
        $nosCodigo = $dom->getElementsByTagName('CodigoVerificacao');
        $nosDataEmissao = $dom->getElementsByTagName('DataEmissao');
        $nosMensagemRetorno = $dom->getElementsByTagName('MensagemRetorno');

        if ($nosMensagemRetorno->length > 0) {
            /** @var \DOMElement $noMensagem */
            $noMensagem = $nosMensagemRetorno->item(0);
            $noCodigo = $noMensagem->getElementsByTagName('Codigo')->item(0);
            $noTextoMensagem = $noMensagem->getElementsByTagName('Mensagem')->item(0);

            $codigo = $noCodigo ? $noCodigo->nodeValue : 'ERRO';
            $mensagem = $noTextoMensagem ? $noTextoMensagem->nodeValue : 'Erro no processamento da NFS-e';

            $notaFiscal->status = 'rejeitada';
            $notaFiscal->codigo_erro = $codigo;
            $notaFiscal->mensagem_erro = $mensagem;
            $notaFiscal->save();

            return;
        }

        if ($nosNumero->length > 0 && $nosCodigo->length > 0) {
            $notaFiscal->numero_nfse = $nosNumero->item(0)?->nodeValue;
            $notaFiscal->codigo_verificacao = $nosCodigo->item(0)?->nodeValue;

            if ($nosDataEmissao->length > 0 && $nosDataEmissao->item(0)?->nodeValue) {
                $dataTexto = $nosDataEmissao->item(0)->nodeValue;
                try {
                    $notaFiscal->data_emissao_nfse = Carbon::parse($dataTexto);
                } catch (Exception $e) {
                    $notaFiscal->data_emissao_nfse = now();
                }
            } else {
                $notaFiscal->data_emissao_nfse = now();
            }

            $notaFiscal->status = 'autorizada';
            $notaFiscal->mensagem_erro = null;
            $notaFiscal->codigo_erro = null;
        } else {
            $notaFiscal->status = 'rejeitada';
            $notaFiscal->mensagem_erro = 'Não foi possível localizar o número e código de verificação da NFS-e no retorno.';
        }

        $notaFiscal->save();
    }
}
