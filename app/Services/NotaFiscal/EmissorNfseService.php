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
                $notaFiscal->data_emissao_rps = Carbon::now();
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
                $notaFiscal->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml) ?: $respostaXml;

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
            $notaFiscal->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml, $notaFiscal->numero_nfse) ?: $respostaXml;
        } else {
            $notaFiscal->status = 'rejeitada';
            $notaFiscal->mensagem_erro = 'Não foi possível localizar o número e código de verificação da NFS-e no retorno da prefeitura.';
        }

        $notaFiscal->save();
    }

    /**
     * Processa o cancelamento de uma NFS-e já autorizada.
     *
     * @throws Exception
     */
    public function cancelar(NotaFiscal $notaFiscal, string $codigoCancelamento = '1', ?string $motivo = null): NotaFiscal
    {
        if (! $notaFiscal->ehAutorizada()) {
            throw new Exception('Apenas notas fiscais autorizadas podem ser canceladas.');
        }

        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();
        if (! $configuracao) {
            throw new Exception('Nenhuma configuração de nota fiscal cadastrada no sistema.');
        }

        return DB::transaction(function () use ($notaFiscal, $configuracao, $codigoCancelamento, $motivo) {
            $xmlCancelamento = $this->geradorXml->gerarXmlCancelamento($notaFiscal, $configuracao, $codigoCancelamento);
            $xmlEnvio = $xmlCancelamento;

            if ($configuracao->caminho_certificado) {
                $xmlEnvio = $this->assinadorXml->assinar($xmlCancelamento, 'InfPedidoCancelamento', 'Id');
            }

            $notaFiscal->xml_envio = $xmlEnvio;

            $validacao = $this->validadorXml->validar($xmlEnvio);
            if (! $validacao['valido']) {
                $errosTexto = implode(' | ', $validacao['erros']);
                throw new Exception('XML de cancelamento inválido no esquema XSD: '.$errosTexto);
            }

            $respostaXml = $this->clienteSoap->enviar('CancelarNfse', $xmlEnvio);
            $notaFiscal->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml) ?: $respostaXml;

            $this->processarRespostaCancelamento($notaFiscal, $respostaXml, $codigoCancelamento, $motivo);

            return $notaFiscal;
        });
    }

    /**
     * Processa a substituição de uma NFS-e autorizada por uma nova Nota Fiscal.
     *
     * @throws Exception
     */
    public function substituir(NotaFiscal $notaFiscalAntiga, NotaFiscal $novaNota, string $codigoCancelamento = '1'): NotaFiscal
    {
        if (! $notaFiscalAntiga->ehAutorizada()) {
            throw new Exception('Apenas notas fiscais autorizadas podem ser substituídas.');
        }

        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();
        if (! $configuracao) {
            throw new Exception('Nenhuma configuração de nota fiscal cadastrada no sistema.');
        }

        return DB::transaction(function () use ($notaFiscalAntiga, $novaNota, $configuracao, $codigoCancelamento) {
            if (! $novaNota->numero_rps) {
                $novoNumeroRps = $configuracao->ultimo_numero_rps + 1;
                $configuracao->ultimo_numero_rps = $novoNumeroRps;
                $configuracao->save();

                $novaNota->numero_rps = $novoNumeroRps;
                $novaNota->serie_rps = $configuracao->serie_rps ?: '1';
                $novaNota->data_emissao_rps = Carbon::now();
            }

            $novaNota->status = 'processando';
            $novaNota->save();

            $xmlSubstituicao = $this->geradorXml->gerarXmlSubstituicao($notaFiscalAntiga, $novaNota, $configuracao, $codigoCancelamento);
            $xmlEnvio = $xmlSubstituicao;

            if ($configuracao->caminho_certificado) {
                $xmlEnvio = $this->assinadorXml->assinar($xmlEnvio, 'InfPedidoCancelamento', 'Id');
                $xmlEnvio = $this->assinadorXml->assinar($xmlEnvio, 'InfDeclaracaoPrestacaoServico', 'Id');
                $xmlEnvio = $this->assinadorXml->assinar($xmlEnvio, 'SubstituicaoNfse', 'Id');
            }

            $novaNota->xml_envio = $xmlEnvio;

            $validacao = $this->validadorXml->validar($xmlEnvio);
            if (! $validacao['valido']) {
                $errosTexto = implode(' | ', $validacao['erros']);
                throw new Exception('XML de substituição inválido no esquema XSD: '.$errosTexto);
            }

            $respostaXml = $this->clienteSoap->enviar('SubstituirNfse', $xmlEnvio);
            $novaNota->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml) ?: $respostaXml;

            $this->processarRespostaSubstituicao($notaFiscalAntiga, $novaNota, $respostaXml, $codigoCancelamento);

            return $novaNota;
        });
    }

    private function processarRespostaCancelamento(NotaFiscal $notaFiscal, string $respostaXml, string $codigoCancelamento, ?string $motivo): void
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        if (! $dom->loadXML($respostaXml)) {
            throw new Exception('Resposta XML inválida da prefeitura no cancelamento.');
        }

        $domProcessamento = $dom;
        $tagsEnvelopeRetorno = ['outputXML', 'outputXml', 'CancelarNfseResult', 'return', 'output'];
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

        $nosMensagemRetorno = $domProcessamento->getElementsByTagName('MensagemRetorno');
        if ($nosMensagemRetorno->length === 0 && $domProcessamento !== $dom) {
            $nosMensagemRetorno = $dom->getElementsByTagName('MensagemRetorno');
        }

        if ($nosMensagemRetorno->length > 0) {
            $errosDetalhados = [];
            for ($i = 0; $i < $nosMensagemRetorno->length; $i++) {
                $noMensagem = $nosMensagemRetorno->item($i);
                $codigo = $noMensagem->getElementsByTagName('Codigo')->item(0)?->nodeValue ?? 'ERRO';
                $mensagem = $noMensagem->getElementsByTagName('Mensagem')->item(0)?->nodeValue ?? 'Erro no cancelamento';
                $errosDetalhados[] = "[{$codigo}] {$mensagem}";
            }
            throw new Exception('Erro da prefeitura ao cancelar NFS-e: '.implode(' | ', $errosDetalhados));
        }

        $notaFiscal->status = 'cancelada';
        $notaFiscal->codigo_cancelamento = $codigoCancelamento;
        $notaFiscal->motivo_cancelamento = $motivo;
        $notaFiscal->data_cancelamento = now();
        $notaFiscal->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml, $notaFiscal->numero_nfse) ?: $respostaXml;
        $notaFiscal->save();
    }

    private function processarRespostaSubstituicao(NotaFiscal $notaFiscalAntiga, NotaFiscal $novaNota, string $respostaXml, string $codigoCancelamento): void
    {
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        if (! $dom->loadXML($respostaXml)) {
            $novaNota->status = 'rejeitada';
            $novaNota->mensagem_erro = 'Resposta XML de substituição inválida.';
            $novaNota->save();
            throw new Exception('Resposta XML de substituição inválida da prefeitura.');
        }

        $domProcessamento = $dom;
        $tagsEnvelopeRetorno = ['outputXML', 'outputXml', 'SubstituirNfseResult', 'return', 'output'];
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

        $nosMensagemRetorno = $domProcessamento->getElementsByTagName('MensagemRetorno');
        if ($nosMensagemRetorno->length === 0 && $domProcessamento !== $dom) {
            $nosMensagemRetorno = $dom->getElementsByTagName('MensagemRetorno');
        }

        if ($nosMensagemRetorno->length > 0) {
            $errosDetalhados = [];
            for ($i = 0; $i < $nosMensagemRetorno->length; $i++) {
                $noMensagem = $nosMensagemRetorno->item($i);
                $codigo = $noMensagem->getElementsByTagName('Codigo')->item(0)?->nodeValue ?? 'ERRO';
                $mensagem = $noMensagem->getElementsByTagName('Mensagem')->item(0)?->nodeValue ?? 'Erro na substituição';
                $errosDetalhados[] = "[{$codigo}] {$mensagem}";
            }

            $novaNota->status = 'rejeitada';
            $novaNota->mensagem_erro = implode(' | ', $errosDetalhados);
            $novaNota->save();

            throw new Exception('Substituição rejeitada pela prefeitura: '.implode(' | ', $errosDetalhados));
        }

        $nosSubstituidora = $domProcessamento->getElementsByTagName('NfseSubstituidora');
        if ($nosSubstituidora->length === 0 && $domProcessamento !== $dom) {
            $nosSubstituidora = $dom->getElementsByTagName('NfseSubstituidora');
        }

        $noSub = $nosSubstituidora->length > 0 ? $nosSubstituidora->item(0) : $domProcessamento;

        $nosNumero = $noSub->getElementsByTagName('Numero');
        $nosCodigo = $noSub->getElementsByTagName('CodigoVerificacao');

        if ($nosNumero->length > 0 && $nosCodigo->length > 0) {
            $novaNota->numero_nfse = $nosNumero->item(0)?->nodeValue;
            $novaNota->codigo_verificacao = $nosCodigo->item(0)?->nodeValue;
            $novaNota->data_emissao_nfse = now();
            $novaNota->status = 'autorizada';
            $novaNota->nota_fiscal_substituida_id = $notaFiscalAntiga->id;
            $novaNota->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml, $novaNota->numero_nfse) ?: $respostaXml;
            $novaNota->save();

            $notaFiscalAntiga->status = 'cancelada';
            $notaFiscalAntiga->codigo_cancelamento = $codigoCancelamento;
            $notaFiscalAntiga->motivo_cancelamento = "Substituída pela NFS-e nº {$novaNota->numero_nfse}";
            $notaFiscalAntiga->data_cancelamento = now();
            $notaFiscalAntiga->nota_fiscal_substituta_id = $novaNota->id;
            $notaFiscalAntiga->xml_retorno = NotaFiscal::extrairXmlLimpo($respostaXml, $notaFiscalAntiga->numero_nfse) ?: $notaFiscalAntiga->xml_retorno;
            $notaFiscalAntiga->save();
        } else {
            $novaNota->status = 'rejeitada';
            $novaNota->mensagem_erro = 'Não foi possível identificar o número da nova NFS-e substituta no retorno.';
            $novaNota->save();

            throw new Exception('Substituição falhou: número da nova NFS-e não retornado.');
        }
    }
}
