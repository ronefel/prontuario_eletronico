<?php

namespace App\Services\NotaFiscal;

use DOMDocument;

class ValidadorXmlService
{
    /**
     * Valida um XML contra o schema XSD da ABRASF v2.02.
     *
     * @param  string  $conteudoXml  Conteúdo XML a ser validado.
     * @return array Array com 'valido' (bool) e 'erros' (array de strings).
     */
    public function validar(string $conteudoXml): array
    {
        $caminhoXsd = resource_path('schemas/nfse/nfse-v2-02.xsd');

        if (! file_exists($caminhoXsd)) {
            return [
                'valido' => false,
                'erros' => ["Arquivo XSD de validação não encontrado em: {$caminhoXsd}"],
            ];
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom = new DOMDocument('1.0', 'UTF-8');
        if (! $dom->loadXML($conteudoXml)) {
            $errosXml = libxml_get_errors();
            libxml_clear_errors();

            $errosFormatados = array_map(fn ($erro) => trim($erro->message), $errosXml);

            return [
                'valido' => false,
                'erros' => array_merge(['Falha ao carregar a sintaxe do XML.'], $errosFormatados),
            ];
        }

        $valido = $dom->schemaValidate($caminhoXsd);
        $erros = [];

        if (! $valido) {
            $errosEsquema = libxml_get_errors();
            libxml_clear_errors();
            $erros = array_map(fn ($erro) => trim($erro->message), $errosEsquema);
        }

        return [
            'valido' => $valido,
            'erros' => $erros,
        ];
    }
}
