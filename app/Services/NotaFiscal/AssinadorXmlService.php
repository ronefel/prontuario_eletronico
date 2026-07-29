<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use DOMDocument;
use Exception;

class AssinadorXmlService
{
    public function __construct(
        protected LeitorCertificadoService $leitorCertificado,
    ) {}

    /**
     * Assina um nó XML utilizando o certificado A1 fornecido na configuração conforme padrão ABRASF / WebISS / W3C XMLDSig.
     *
     * @param  string  $conteudoXml  XML de entrada.
     * @param  string  $tagAlvo  Tag a ser assinada (ex: 'InfDeclaracaoPrestacaoServico' ou 'LoteRps').
     * @param  string  $atributoId  Nome do atributo ID (ex: 'Id').
     * @return string XML assinado com o bloco <Signature>.
     *
     * @throws Exception Se o certificado não for válido ou a assinatura falhar.
     */
    public function assinar(string $conteudoXml, string $tagAlvo = 'InfDeclaracaoPrestacaoServico', string $atributoId = 'Id'): string
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        if (! $configuracao || ! $configuracao->caminho_certificado) {
            throw new Exception('Certificado digital não configurado.');
        }

        $dadosCertificado = $this->leitorCertificado->obterDadosCertificado($configuracao);

        $chavePrivada = $dadosCertificado['pkey'];
        $certificadoX509 = $dadosCertificado['cert'];

        // Limpar certificado X509 removendo cabeçalhos, rodapés, quebras de linha e espaços
        $certificadoLimpo = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s/', '', $certificadoX509);

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        if (! $dom->loadXML($conteudoXml)) {
            throw new Exception('XML inválido fornecido para a assinatura.');
        }

        $nosAlvo = $dom->getElementsByTagName($tagAlvo);
        if ($nosAlvo->length === 0) {
            throw new Exception("Tag alvo '<{$tagAlvo}>' não encontrada no XML.");
        }

        /** @var \DOMElement $noAlvo */
        $noAlvo = $nosAlvo->item(0);
        $idValor = $noAlvo->getAttribute($atributoId);

        if ($idValor) {
            $noAlvo->setIdAttribute($atributoId, true);
        }

        // Canonicalização C14N Padrão Inclusive (http://www.w3.org/TR/2001/REC-xml-c14n-20010315) do nó alvo
        $xmlCanonicalizado = $noAlvo->C14N(false, false);
        $digestCalculado = base64_encode(sha1($xmlCanonicalizado, true));

        // Construir elemento SignedInfo no padrão oficial ABRASF v2.02 / WebISS
        $refUri = $idValor ? "#{$idValor}" : '';
        $signedInfoXml = '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">'.
            '<CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'.
            '<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>'.
            '<Reference URI="'.$refUri.'">'.
            '<Transforms>'.
            '<Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'.
            '</Transforms>'.
            '<DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'.
            '<DigestValue>'.$digestCalculado.'</DigestValue>'.
            '</Reference>'.
            '</SignedInfo>';

        $domSignedInfo = new DOMDocument('1.0', 'UTF-8');
        $domSignedInfo->loadXML($signedInfoXml);
        $signedInfoCanonicalizado = $domSignedInfo->documentElement->C14N(false, false);

        // Assinar o SignedInfo com a chave privada usando RSA-SHA1
        $valorAssinatura = '';
        if (! openssl_sign($signedInfoCanonicalizado, $valorAssinatura, $chavePrivada, OPENSSL_ALGO_SHA1)) {
            throw new Exception('Falha ao gerar a assinatura digital RSA-SHA1.');
        }

        $assinaturaBase64 = base64_encode($valorAssinatura);

        // Montar o bloco <Signature> completo
        $signatureXml = '<Signature xmlns="http://www.w3.org/2000/09/xmldsig#">'.
            $signedInfoXml.
            '<SignatureValue>'.$assinaturaBase64.'</SignatureValue>'.
            '<KeyInfo>'.
            '<X509Data>'.
            '<X509Certificate>'.$certificadoLimpo.'</X509Certificate>'.
            '</X509Data>'.
            '</KeyInfo>'.
            '</Signature>';

        $domSignature = new DOMDocument('1.0', 'UTF-8');
        $domSignature->loadXML($signatureXml);
        $noSignatureImportado = $dom->importNode($domSignature->documentElement, true);

        // Anexar <Signature> no elemento pai do nó alvo (conforme estrutura ABRASF tcDeclaracaoPrestacaoServico)
        $noAlvo->parentNode->appendChild($noSignatureImportado);

        return $dom->saveXML();
    }
}
