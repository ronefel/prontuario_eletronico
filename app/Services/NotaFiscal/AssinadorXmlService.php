<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use DOMDocument;
use Exception;
use Illuminate\Support\Facades\Storage;

class AssinadorXmlService
{
    /**
     * Assina um nó XML utilizando o certificado A1 fornecido na configuração.
     *
     * @param  string  $conteudoXml  XML de entrada.
     * @param  string  $tagAlvo  Tag a ser assinada (ex: 'InfDeclaracaoPrestacaoServico' ou 'LoteRps').
     * @param  string  $atributoId  Nome do atributo ID (ex: 'Id').
     * @return string XML assinado com o bloco <Signature>.
     * @throws Exception Se o certificado não for válido ou a assinatura falhar.
     */
    public function assinar(string $conteudoXml, string $tagAlvo = 'InfDeclaracaoPrestacaoServico', string $atributoId = 'Id'): string
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();

        if (! $configuracao || ! $configuracao->caminho_certificado) {
            throw new Exception('Certificado digital não configurado.');
        }

        $conteudoCertificado = null;

        if (Storage::exists($configuracao->caminho_certificado)) {
            $conteudoCertificado = Storage::get($configuracao->caminho_certificado);
        } else {
            $caminhoAbsoluto = storage_path('app/'.ltrim($configuracao->caminho_certificado, '/'));
            if (file_exists($caminhoAbsoluto)) {
                $conteudoCertificado = file_get_contents($caminhoAbsoluto);
            } elseif (file_exists($configuracao->caminho_certificado)) {
                $conteudoCertificado = file_get_contents($configuracao->caminho_certificado);
            }
        }

        if (empty($conteudoCertificado)) {
            throw new Exception("Arquivo de certificado digital não encontrado ou vazio: {$configuracao->caminho_certificado}");
        }

        $senha = $configuracao->senha_certificado_descriptografada ?? '';
        $dadosCertificado = [];

        if (! openssl_pkcs12_read($conteudoCertificado, $dadosCertificado, $senha)) {
            throw new Exception('Não foi possível ler o certificado digital A1. Verifique a senha informada.');
        }

        $chavePrivada = $dadosCertificado['pkey'];
        $certificadoX509 = $dadosCertificado['cert'];

        // Limpar certificado X509 para envio na tag <X509Certificate>
        $certificadoLimpo = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\r|\n/', '', $certificadoX509);

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

        $noAlvo = $nosAlvo->item(0);
        $idValor = $noAlvo->getAttribute($atributoId);

        // Canonicalização C14N da tag a ser assinada
        $xmlCanonicalizado = $noAlvo->C14N(true, false);
        $digestCalculado = base64_encode(sha1($xmlCanonicalizado, true));

        // Construir SignedInfo
        $refUri = $idValor ? "#{$idValor}" : '';
        $signedInfoXml = '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">'.
            '<CanonicalizationMethod Algorithm="http://www.w3.org/TR/2001/REC-xml-c14n-20010315"/>'.
            '<SignatureMethod Algorithm="http://www.w3.org/2000/09/xmldsig#rsa-sha1"/>'.
            '<Reference URI="'.$refUri.'">'.
            '<Transforms>'.
            '<Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>'.
            '<Transform Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>'.
            '</Transforms>'.
            '<DigestMethod Algorithm="http://www.w3.org/2000/09/xmldsig#sha1"/>'.
            '<DigestValue>'.$digestCalculado.'</DigestValue>'.
            '</Reference>'.
            '</SignedInfo>';

        $domSignedInfo = new DOMDocument('1.0', 'UTF-8');
        $domSignedInfo->loadXML($signedInfoXml);
        $signedInfoCanonicalizado = $domSignedInfo->documentElement->C14N(true, false);

        // Assinar SignedInfo com chave privada
        $valorAssinatura = '';
        if (! openssl_sign($signedInfoCanonicalizado, $valorAssinatura, $chavePrivada, OPENSSL_ALGO_SHA1)) {
            throw new Exception('Falha ao gerar a assinatura digital RSA-SHA1.');
        }

        $assinaturaBase64 = base64_encode($valorAssinatura);

        // Montar a tag <Signature> completa
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

        // Anexar <Signature> ao pai do noAlvo ou no final de noAlvo
        $noAlvo->parentNode->appendChild($noSignatureImportado);

        return $dom->saveXML();
    }
}
