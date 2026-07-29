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

        // Tentar desempacotar XML interno se a resposta for um envelope SOAP contendo o XML codificado em entidades HTML (ex: <outputXML>)
        $domProcessamento = $dom;
        $tagsEnvelopeRetorno = ['outputXML', 'outputXml', 'GerarNfseResult', 'return', 'output'];

        foreach ($tagsEnvelopeRetorno as $tag) {
            $nos = $dom->getElementsByTagName($tag);
            if ($nos->length > 0) {
                $conteudoInterno = $nos->item(0)->nodeValue;
                if (! empty($conteudoInterno) && (str_contains($conteudoInterno, '<') || str_contains($conteudoInterno, '&lt;'))) {
                    $xmlDesempacotado = htmlspecialchars_decode(html_entity_decode($conteudoInterno, ENT_QUOTES | ENT_XML1, 'UTF-8'));
                    $domInterno = new DOMDocument;
                    if ($domInterno->loadXML($xmlDesempacotado)) {
                        $domProcessamento = $domInterno;
                        break;
                    }
                }
            }
        }

        // Verificar erros SOAP Fault
        $nosFault = $domProcessamento->getElementsByTagName('Fault');
        if ($nosFault->length === 0 && $domProcessamento !== $dom) {
            $nosFault = $dom->getElementsByTagName('Fault');
        }

        if ($nosFault->length > 0) {
            /** @var \DOMElement $noFault */
            $noFault = $nosFault->item(0);
            $faultCode = $noFault->getElementsByTagName('faultcode')->item(0)?->nodeValue ?? 'SOAP_FAULT';
            $faultString = $noFault->getElementsByTagName('faultstring')->item(0)?->nodeValue ?? 'Erro na chamada SOAP do WebService.';

            $notaFiscal->status = 'rejeitada';
            $notaFiscal->codigo_erro = $faultCode;
            $notaFiscal->mensagem_erro = "Erro SOAP: {$faultString}";
            $notaFiscal->save();

            return;
        }

        // Buscar Mensagens de Retorno da Prefeitura (Alertas / Erros de Validação da Prefeitura)
        $nosMensagemRetorno = $domProcessamento->getElementsByTagName('MensagemRetorno');
        if ($nosMensagemRetorno->length === 0 && $domProcessamento !== $dom) {
            $nosMensagemRetorno = $dom->getElementsByTagName('MensagemRetorno');
        }

        if ($nosMensagemRetorno->length > 0) {
            $errosDetalhados = [];
            $primeiroCodigo = null;

            for ($i = 0; $i < $nosMensagemRetorno->length; $i++) {
                /** @var \DOMElement $noMensagem */
                $noMensagem = $nosMensagemRetorno->item($i);
                $noCodigo = $noMensagem->getElementsByTagName('Codigo')->item(0);
                $noTexto = $noMensagem->getElementsByTagName('Mensagem')->item(0);
                $noCorrecao = $noMensagem->getElementsByTagName('Correcao')->item(0);

                $codigo = $noCodigo ? trim($noCodigo->nodeValue) : 'ERRO';
                $mensagem = $noTexto ? trim($noTexto->nodeValue) : 'Erro no processamento da NFS-e';
                $correcao = $noCorrecao ? trim($noCorrecao->nodeValue) : null;

                if (! $primeiroCodigo) {
                    $primeiroCodigo = $codigo;
                }

                $textoFormatado = "[{$codigo}] {$mensagem}";
                if ($correcao) {
                    $textoFormatado .= " (Correção: {$correcao})";
                }

                $errosDetalhados[] = $textoFormatado;
            }

            $notaFiscal->status = 'rejeitada';
            $notaFiscal->codigo_erro = $primeiroCodigo ?: 'ERRO';
            $notaFiscal->mensagem_erro = implode(' | ', $errosDetalhados);
            $notaFiscal->save();

            return;
        }

        // Buscar Número da NFS-e e Código de Verificação
        $nosNumero = $domProcessamento->getElementsByTagName('Numero');
        $nosCodigo = $domProcessamento->getElementsByTagName('CodigoVerificacao');
        $nosDataEmissao = $domProcessamento->getElementsByTagName('DataEmissao');

        if ($nosNumero->length === 0 && $domProcessamento !== $dom) {
            $nosNumero = $dom->getElementsByTagName('Numero');
            $nosCodigo = $dom->getElementsByTagName('CodigoVerificacao');
            $nosDataEmissao = $dom->getElementsByTagName('DataEmissao');
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
            $notaFiscal->mensagem_erro = 'Não foi possível localizar o número e código de verificação da NFS-e no retorno da prefeitura.';
        }

        $notaFiscal->save();
    }
}
