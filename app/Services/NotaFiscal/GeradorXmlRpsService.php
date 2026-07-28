<?php

namespace App\Services\NotaFiscal;

use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use DOMDocument;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class GeradorXmlRpsService
{
    /**
     * Gera o XML de solicitação GerarNfseEnvio no padrão ABRASF v2.02.
     *
     * @param  NotaFiscal  $notaFiscal  Modelo da Nota Fiscal.
     * @param  ConfiguracaoNotaFiscal  $configuracao  Configuração ativa do emitente.
     * @return string XML formatado.
     * @throws Exception Se algum campo obrigatório estiver ausente.
     */
    public function gerarXml(NotaFiscal $notaFiscal, ConfiguracaoNotaFiscal $configuracao): string
    {
        $paciente = $notaFiscal->paciente;
        if (! $paciente) {
            throw new Exception('A nota fiscal precisa ter um paciente (tomador) associado.');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        // Elemento Raiz GerarNfseEnvio
        $gerarNfseEnvio = $dom->createElementNS('http://www.abrasf.org.br/nfse.xsd', 'GerarNfseEnvio');
        $dom->appendChild($gerarNfseEnvio);

        $rpsContainer = $dom->createElement('Rps');
        $gerarNfseEnvio->appendChild($rpsContainer);

        $idRps = 'RPS_'.$notaFiscal->numero_rps;
        $infDeclaracao = $dom->createElement('InfDeclaracaoPrestacaoServico');
        $infDeclaracao->setAttribute('Id', $idRps);
        $rpsContainer->appendChild($infDeclaracao);

        // Bloco Rps (Identificacao)
        $rpsElement = $dom->createElement('Rps');
        $rpsElement->setAttribute('Id', $idRps);
        $infDeclaracao->appendChild($rpsElement);

        $identificacaoRps = $dom->createElement('IdentificacaoRps');
        $identificacaoRps->appendChild($dom->createElement('Numero', (string) $notaFiscal->numero_rps));
        $identificacaoRps->appendChild($dom->createElement('Serie', $notaFiscal->serie_rps ?: '1'));
        $identificacaoRps->appendChild($dom->createElement('Tipo', (string) ($notaFiscal->tipo_rps ?: 1)));
        $rpsElement->appendChild($identificacaoRps);

        $fusoHorario = Auth::check() && Auth::user()->timezone
            ? Auth::user()->timezone
            : 'America/Porto_Velho';

        $dataEmissaoCarbon = $notaFiscal->data_emissao_rps
            ? Carbon::parse($notaFiscal->data_emissao_rps)->setTimezone($fusoHorario)
            : now()->setTimezone($fusoHorario);

        $dataEmissao = $dataEmissaoCarbon->format('Y-m-d');
        $rpsElement->appendChild($dom->createElement('DataEmissao', $dataEmissao));
        $rpsElement->appendChild($dom->createElement('Status', '1')); // 1-Normal

        // Competência
        $competencia = $dataEmissaoCarbon->format('Y-m-d');
        $infDeclaracao->appendChild($dom->createElement('Competencia', $competencia));

        // Bloco Servico
        $servico = $dom->createElement('Servico');
        $infDeclaracao->appendChild($servico);

        $valores = $dom->createElement('Valores');
        $valores->appendChild($dom->createElement('ValorServicos', number_format((float) $notaFiscal->valor_servicos, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorDeducoes', number_format((float) $notaFiscal->valor_deducoes, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorPis', number_format((float) $notaFiscal->valor_pis, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorCofins', number_format((float) $notaFiscal->valor_cofins, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorInss', number_format((float) $notaFiscal->valor_inss, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorIr', number_format((float) $notaFiscal->valor_ir, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorCsll', number_format((float) $notaFiscal->valor_csll, 2, '.', '')));
        $valores->appendChild($dom->createElement('ValorIss', number_format((float) $notaFiscal->valor_iss, 2, '.', '')));

        // Alíquota em percentual decimal (ex: 2% = 0.02 ou 2.00 dependendo da validação, ABRASF v2.02 usa ex: 0.0200)
        $aliquotaDecimal = ((float) $notaFiscal->aliquota_iss) / 100;
        $valores->appendChild($dom->createElement('Aliquota', number_format($aliquotaDecimal, 4, '.', '')));

        $valores->appendChild($dom->createElement('DescontoIncondicionado', number_format((float) $notaFiscal->desconto_incondicionado, 2, '.', '')));
        $valores->appendChild($dom->createElement('DescontoCondicionado', number_format((float) $notaFiscal->desconto_condicionado, 2, '.', '')));
        $servico->appendChild($valores);

        $servico->appendChild($dom->createElement('IssRetido', '2'));
        $servico->appendChild($dom->createElement('ItemListaServico', $notaFiscal->item_lista_servico ?: $configuracao->item_lista_servico ?: '04.01'));

        $codigoCnae = $notaFiscal->codigo_cnae ?: ($configuracao->cnae_principal['codigo'] ?? null);
        if ($codigoCnae) {
            $cnaeLimpo = preg_replace('/\D/', '', $codigoCnae);
            if ($cnaeLimpo) {
                $servico->appendChild($dom->createElement('CodigoCnae', $cnaeLimpo));
            }
        }

        if ($notaFiscal->codigo_tributacao_municipio || $configuracao->codigo_tributacao_municipio) {
            $servico->appendChild($dom->createElement('CodigoTributacaoMunicipio', $notaFiscal->codigo_tributacao_municipio ?: $configuracao->codigo_tributacao_municipio));
        }

        $servico->appendChild($dom->createElement('Discriminacao', htmlspecialchars($notaFiscal->discriminacao_servico, ENT_QUOTES | ENT_XML1)));
        $codigoIbge = $notaFiscal->codigo_municipio_ibge ?: $configuracao->codigo_municipio_ibge ?: '1100049';
        $servico->appendChild($dom->createElement('CodigoMunicipio', $codigoIbge));
        $servico->appendChild($dom->createElement('ExigibilidadeISS', '1')); // 1-Exigível
        $servico->appendChild($dom->createElement('MunicipioIncidencia', $codigoIbge));

        // Bloco Prestador
        $prestador = $dom->createElement('Prestador');
        $cpfCnpjPrestador = $dom->createElement('CpfCnpj');
        $cnpjLimpo = preg_replace('/\D/', '', $configuracao->cnpj);
        $cpfCnpjPrestador->appendChild($dom->createElement('Cnpj', $cnpjLimpo));
        $prestador->appendChild($cpfCnpjPrestador);

        if ($configuracao->inscricao_municipal) {
            $prestador->appendChild($dom->createElement('InscricaoMunicipal', preg_replace('/\D/', '', $configuracao->inscricao_municipal)));
        }
        $infDeclaracao->appendChild($prestador);

        // Bloco Tomador
        $tomador = $dom->createElement('Tomador');
        $identificacaoTomador = $dom->createElement('IdentificacaoTomador');
        $cpfCnpjTomador = $dom->createElement('CpfCnpj');

        $documentoTomador = preg_replace('/\D/', '', $paciente->cpf);
        if (strlen($documentoTomador) === 11) {
            $cpfCnpjTomador->appendChild($dom->createElement('Cpf', $documentoTomador));
        } else {
            $cpfCnpjTomador->appendChild($dom->createElement('Cnpj', $documentoTomador));
        }
        $identificacaoTomador->appendChild($cpfCnpjTomador);
        $tomador->appendChild($identificacaoTomador);

        $tomador->appendChild($dom->createElement('RazaoSocial', htmlspecialchars($paciente->nome, ENT_QUOTES | ENT_XML1)));

        // Endereço do Tomador
        if ($paciente->logradouro || $paciente->cep) {
            $endereco = $dom->createElement('Endereco');
            if ($paciente->logradouro) {
                $endereco->appendChild($dom->createElement('Endereco', htmlspecialchars($paciente->logradouro, ENT_QUOTES | ENT_XML1)));
            }
            if ($paciente->numero) {
                $endereco->appendChild($dom->createElement('Numero', htmlspecialchars($paciente->numero, ENT_QUOTES | ENT_XML1)));
            }
            if ($paciente->bairro) {
                $endereco->appendChild($dom->createElement('Bairro', htmlspecialchars($paciente->bairro, ENT_QUOTES | ENT_XML1)));
            }

            $codigoCidadeTomador = $paciente->cidade ? $paciente->cidade->codigo_ibge ?? $codigoIbge : $codigoIbge;
            $endereco->appendChild($dom->createElement('CodigoMunicipio', $codigoCidadeTomador));

            $ufTomador = $paciente->cidade ? $paciente->cidade->uf ?? 'RO' : 'RO';
            $endereco->appendChild($dom->createElement('Uf', $ufTomador));

            if ($paciente->cep) {
                $endereco->appendChild($dom->createElement('Cep', preg_replace('/\D/', '', $paciente->cep)));
            }
            $tomador->appendChild($endereco);
        }

        // Contato do Tomador
        if ($paciente->celular || $paciente->email) {
            $contato = $dom->createElement('Contato');
            if ($paciente->celular) {
                $contato->appendChild($dom->createElement('Telefone', preg_replace('/\D/', '', $paciente->celular)));
            }
            if ($paciente->email) {
                $contato->appendChild($dom->createElement('Email', htmlspecialchars($paciente->email, ENT_QUOTES | ENT_XML1)));
            }
            $tomador->appendChild($contato);
        }

        $infDeclaracao->appendChild($tomador);

        // Optante Simples Nacional (1-Sim, 2-Não)
        $infDeclaracao->appendChild($dom->createElement('OptanteSimplesNacional', $configuracao->optante_simples_nacional ? '1' : '2'));

        // Incentivo Fiscal (1-Sim, 2-Não)
        $infDeclaracao->appendChild($dom->createElement('IncentivoFiscal', $configuracao->incentivador_cultural ? '1' : '2'));

        return $dom->saveXML();
    }
}
