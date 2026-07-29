<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use Exception;

class ClienteNfseSoapService
{
    public function __construct(
        protected LeitorCertificadoService $leitorCertificado,
    ) {}

    /**
     * Transmite a mensagem SOAP para o WebService de NFS-e do município.
     *
     * @param  string  $operacao  Nome da operação SOAP (ex: 'GerarNfse', 'CancelarNfse').
     * @param  string  $xmlDados  Conteúdo do XML de dados.
     * @return string Resposta XML devolvida pelo WebService.
     *
     * @throws Exception Em caso de erro de conexão ou transmissão HTTP/SOAP.
     */
    public function enviar(string $operacao, string $xmlDados): string
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        if (! $configuracao) {
            throw new Exception('Configurações de Nota Fiscal não encontradas.');
        }

        $urlWebService = $configuracao->ambiente === 'producao'
            ? $configuracao->url_webservice_producao
            : $configuracao->url_webservice_homologacao;

        if (empty($urlWebService)) {
            // Em ambiente de teste/desenvolvimento sem servidor real, simular resposta de sucesso para Cacoal/RO
            if ($configuracao->ambiente === 'homologacao') {
                return $this->simularRespostaHomologacao($xmlDados);
            }

            throw new Exception("URL do WebService de {$configuracao->ambiente} não configurada.");
        }

        $xmlCabecalho = '<?xml version="1.0" encoding="UTF-8"?><cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.02"><versaoDados>2.02</versaoDados></cabecalho>';

        // Montar envelope SOAP 1.1 conforme nfse.wsdl
        $envelopeSoap = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://nfse.abrasf.org.br">'.
            '<soapenv:Header/>'.
            '<soapenv:Body>'.
            "<ws:{$operacao}Request>".
            '<nfseCabecMsg>'.htmlspecialchars($xmlCabecalho).'</nfseCabecMsg>'.
            '<nfseDadosMsg>'.htmlspecialchars($xmlDados).'</nfseDadosMsg>'.
            "</ws:{$operacao}Request>".
            '</soapenv:Body>'.
            '</soapenv:Envelope>';

        $headers = [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://nfse.abrasf.org.br/'.$operacao.'"',
            'Content-Length: '.strlen($envelopeSoap),
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlWebService);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $envelopeSoap);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        // Se houver certificado A1 configurado, extrair PEM temporário e aplicar mTLS no cURL
        $caminhoPemTemporario = null;
        if ($configuracao->caminho_certificado) {
            $caminhoPemTemporario = $this->leitorCertificado->criarArquivoPemTemporario($configuracao);

            if ($caminhoPemTemporario && file_exists($caminhoPemTemporario)) {
                curl_setopt($ch, CURLOPT_SSLCERT, $caminhoPemTemporario);
                curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            }
        }

        $respostaSoap = curl_exec($ch);
        $erroCurl = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($caminhoPemTemporario && file_exists($caminhoPemTemporario)) {
            @unlink($caminhoPemTemporario);
        }

        if ($erroCurl) {
            throw new Exception("Erro de comunicação cURL WebService: {$erroCurl}");
        }

        if ($httpCode !== 200) {
            throw new Exception("WebService retornou código HTTP {$httpCode}. Resposta: {$respostaSoap}");
        }

        return $respostaSoap;
    }

    /**
     * Simulação de resposta de homologação para testes locais sem ambiente real conectado.
     */
    private function simularRespostaHomologacao(string $xmlDados): string
    {
        $numeroNfse = rand(100000, 999999);
        $codigoVerificacao = strtoupper(substr(md5(uniqid()), 0, 9));
        $dataEmissao = date('Y-m-d\TH:i:s');

        return '<?xml version="1.0" encoding="UTF-8"?>'.
            '<GerarNfseResposta xmlns="http://www.abrasf.org.br/nfse.xsd">'.
            '<CompNfse>'.
            '<Nfse>'.
            '<InfNfse>'.
            '<Numero>'.$numeroNfse.'</Numero>'.
            '<CodigoVerificacao>'.$codigoVerificacao.'</CodigoVerificacao>'.
            '<DataEmissao>'.$dataEmissao.'</DataEmissao>'.
            '</InfNfse>'.
            '</Nfse>'.
            '</CompNfse>'.
            '</GerarNfseResposta>';
    }
}
