<?php

namespace App\Services\NotaFiscal;

use App\Models\Cidade;
use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Carbon;

class DadosImpressaoNotaFiscalService
{
    /**
     * Extrai e formata todos os dados necessários para a impressão da Nota Fiscal.
     */
    public function obterDadosImpressao(NotaFiscal $notaFiscal): array
    {
        $configuracao = ConfiguracaoNotaFiscal::obterConfiguracaoAtiva();
        $paciente = $notaFiscal->paciente;
        $xmlLimpo = $notaFiscal->obterXmlDownload();

        $dadosXml = [];
        if (! empty($xmlLimpo)) {
            $dadosXml = $this->extrairDadosDoXml($xmlLimpo);
        }

        // 1. Informações de Cabeçalho e Emissão
        $ehRascunho = in_array($notaFiscal->status, ['rascunho', 'rejeitada']) || empty($dadosXml['numero_nfse']);
        $numeroNfse = $dadosXml['numero_nfse'] ?? $notaFiscal->numero_nfse;
        $codigoVerificacao = $dadosXml['codigo_verificacao'] ?? $notaFiscal->codigo_verificacao ?? 'Pendente';

        $dataEmissaoBr = $this->formatarDataHora(
            $dadosXml['data_emissao_nfse'] ?? $notaFiscal->data_emissao_nfse ?? $notaFiscal->data_emissao_rps
        );

        $dataRpsBr = $this->formatarData(
            $dadosXml['data_emissao_rps'] ?? $notaFiscal->data_emissao_rps
        );

        $competencia = $this->formatarCompetencia(
            $dadosXml['competencia'] ?? $notaFiscal->data_emissao_nfse ?? $notaFiscal->data_emissao_rps
        );

        $numeroRps = $dadosXml['numero_rps'] ?? $notaFiscal->numero_rps;
        $serieRps = $dadosXml['serie_rps'] ?? $notaFiscal->serie_rps ?? '1';

        $textoRps = $numeroRps
            ? "RPS número {$numeroRps} Série {$serieRps} emitido em {$dataRpsBr}"
            : 'RPS Não Emitido';

        $codigoMunicipioPrestacao = $dadosXml['codigo_municipio_prestacao']
            ?? $notaFiscal->codigo_municipio_ibge
            ?? $configuracao?->codigo_municipio_ibge
            ?? '1100049';

        $infoMunPrestacao = $this->obterInfoMunicipioPorIbge($codigoMunicipioPrestacao);
        $municipioPrestacaoTexto = "{$infoMunPrestacao['cidade']} - {$infoMunPrestacao['uf']}";

        $regimeEspecialTributacaoCodigo = $dadosXml['regime_especial_tributacao']
            ?? $configuracao?->regime_especial_tributacao
            ?? 6;

        $regimeEspecialTributacaoTexto = $this->obterDescricaoRegimeEspecial($regimeEspecialTributacaoCodigo);

        $exigibilidadeIssTexto = "Exigível em {$infoMunPrestacao['cidade']}";

        // 2. Prestador de Serviços
        $prestadorRazaoSocial = $dadosXml['prestador_razao_social']
            ?? $configuracao?->razao_social
            ?? 'KRIS CARDOSO INSTITUTO DE SAUDE INTEGRATIVA LTDA';

        $prestadorNomeFantasia = $dadosXml['prestador_nome_fantasia']
            ?? $configuracao?->nome_fantasia
            ?? '';

        $prestadorEmail = $dadosXml['prestador_email']
            ?? '';

        $prestadorCnpj = $this->formatarCpfCnpj($dadosXml['prestador_cnpj'] ?? $configuracao?->cnpj);
        $prestadorInscricaoMunicipal = $dadosXml['prestador_inscricao_municipal'] ?? $configuracao?->inscricao_municipal ?? '';
        $prestadorInscricaoEstadual = $dadosXml['prestador_inscricao_estadual'] ?? '';
        $prestadorSimplesNacional = ($configuracao?->optante_simples_nacional ?? true) ? 'Sim' : 'Não';
        $prestadorIncentivadorCultural = ($configuracao?->incentivador_cultural ?? false) ? 'Sim' : 'Não';
        $prestadorFone = $this->formatarTelefone($dadosXml['prestador_telefone'] ?? '');

        $infoMunPrestador = $this->obterInfoMunicipioPorIbge($dadosXml['prestador_codigo_municipio'] ?? $configuracao?->codigo_municipio_ibge ?? '1100049');

        $prestadorEndereco = $this->montarEnderecoTexto([
            'logradouro' => $dadosXml['prestador_logradouro'] ?? $configuracao?->logradouro,
            'numero' => $dadosXml['prestador_numero'] ?? $configuracao?->numero,
            'complemento' => $dadosXml['prestador_complemento'] ?? $configuracao?->complemento,
            'bairro' => $dadosXml['prestador_bairro'] ?? $configuracao?->bairro,
            'cep' => $dadosXml['prestador_cep'] ?? $configuracao?->cep,
            'cidade' => $dadosXml['prestador_cidade'] ?? $infoMunPrestador['cidade'],
            'uf' => $dadosXml['prestador_uf'] ?? $configuracao?->uf ?? $infoMunPrestador['uf'],
        ]);

        // 3. Tomador de Serviços
        $tomadorNome = $dadosXml['tomador_nome'] ?? $paciente?->nome ?? '';
        $tomadorCpfCnpj = $this->formatarCpfCnpj($dadosXml['tomador_cpf_cnpj'] ?? $paciente?->cpf);
        $tomadorInscricaoMunicipal = $dadosXml['tomador_inscricao_municipal'] ?? '';
        $tomadorInscricaoEstadual = $dadosXml['tomador_inscricao_estadual'] ?? '';
        $tomadorFone = $this->formatarTelefone($dadosXml['tomador_telefone'] ?? $paciente?->celular);
        $tomadorEmail = $dadosXml['tomador_email'] ?? $paciente?->email ?? '';

        $cidadeTomadorNome = $dadosXml['tomador_cidade'] ?? $paciente?->cidade?->nome;
        $ufTomador = $dadosXml['tomador_uf'] ?? $paciente?->cidade?->uf;

        if (empty($cidadeTomadorNome) && ! empty($dadosXml['tomador_codigo_municipio'])) {
            $infoMunTomador = $this->obterInfoMunicipioPorIbge($dadosXml['tomador_codigo_municipio']);
            $cidadeTomadorNome = $infoMunTomador['cidade'];
            $ufTomador = $ufTomador ?: $infoMunTomador['uf'];
        }

        $tomadorEndereco = $this->montarEnderecoTexto([
            'logradouro' => $dadosXml['tomador_logradouro'] ?? $paciente?->logradouro,
            'numero' => $dadosXml['tomador_numero'] ?? $paciente?->numero,
            'complemento' => $dadosXml['tomador_complemento'] ?? $paciente?->complemento,
            'bairro' => $dadosXml['tomador_bairro'] ?? $paciente?->bairro,
            'cep' => $dadosXml['tomador_cep'] ?? $paciente?->cep,
            'cidade' => $cidadeTomadorNome,
            'uf' => $ufTomador,
        ]);

        // 4. Serviço Prestado e Descrição
        $itemListaServico = $dadosXml['item_lista_servico'] ?? $notaFiscal->item_lista_servico ?? $configuracao?->item_lista_servico ?? '04.09';
        $codigoCnae = $dadosXml['codigo_cnae'] ?? $notaFiscal->codigo_cnae ?? ($configuracao?->cnae_principal['codigo'] ?? '8690901');
        $cnaeFormatado = preg_replace('/\D/', '', (string) $codigoCnae);

        $servicoPrestadoTexto = "{$this->formatarItemListaServico($itemListaServico)} - Terapias de qualquer espécie destinadas ao tratamento físico, orgânico e mental. CNAE: {$cnaeFormatado}.";
        $discriminacaoServicoRaw = $dadosXml['discriminacao_servico'] ?? $notaFiscal->discriminacao_servico ?? '';
        $discriminacaoServico = $this->formatarQuebrasDeLinha($discriminacaoServicoRaw);

        // 5. Impostos e Valores
        $valorServicos = (float) ($dadosXml['valor_servicos'] ?? $notaFiscal->valor_servicos ?? 0);
        $valorDeducoes = (float) ($dadosXml['valor_deducoes'] ?? $notaFiscal->valor_deducoes ?? 0);
        $valorPis = (float) ($dadosXml['valor_pis'] ?? $notaFiscal->valor_pis ?? 0);
        $valorCofins = (float) ($dadosXml['valor_cofins'] ?? $notaFiscal->valor_cofins ?? 0);
        $valorInss = (float) ($dadosXml['valor_inss'] ?? $notaFiscal->valor_inss ?? 0);
        $valorIr = (float) ($dadosXml['valor_ir'] ?? $notaFiscal->valor_ir ?? 0);
        $valorCsll = (float) ($dadosXml['valor_csll'] ?? $notaFiscal->valor_csll ?? 0);
        $outrasRetencoes = (float) ($dadosXml['outras_retencoes'] ?? $notaFiscal->outras_retencoes ?? 0);
        $descontoCondicionado = (float) ($dadosXml['desconto_condicionado'] ?? $notaFiscal->desconto_condicionado ?? 0);
        $descontoIncondicionado = (float) ($dadosXml['desconto_incondicionado'] ?? $notaFiscal->desconto_incondicionado ?? 0);
        $aliquotaIss = (float) ($dadosXml['aliquota_iss'] ?? $notaFiscal->aliquota_iss ?? $configuracao?->aliquota_iss ?? 2.0);

        $issRetidoVal = 0.0;
        $valorIssVal = (float) ($dadosXml['valor_iss'] ?? $notaFiscal->valor_iss ?? 0);

        $baseCalculoIssVal = max(0, $valorServicos - $valorDeducoes - $descontoIncondicionado);
        $valorLiquidoVal = max(0, $valorServicos - $valorPis - $valorCofins - $valorInss - $valorIr - $valorCsll - $outrasRetencoes - $issRetidoVal - $descontoIncondicionado);
        $valorTotalVal = $valorServicos;

        // Formatação Simples Nacional com asteriscos quando isento/optante
        $exibirAsteriscosIss = ($configuracao?->optante_simples_nacional ?? true);

        $outrasInformacoesRaw = $dadosXml['outras_informacoes'] ?? '';
        if (! empty($outrasInformacoesRaw)) {
            $outrasInformacoes = $this->formatarQuebrasDeLinha($outrasInformacoesRaw);
        } else {
            $outrasInformacoesArr = [];
            if ($configuracao?->optante_simples_nacional) {
                $outrasInformacoesArr[] = 'Optante do Simples Nacional.';
            }

            $tribAproxFed = number_format($valorServicos * 0.1345, 2, ',', '.');
            if (! empty($numeroNfse) && $numeroNfse !== 'RASCUNHO') {
                $cnpjPrestadorLimpo = preg_replace('/\D/', '', $configuracao?->cnpj ?? '');
                $outrasInformacoesArr[] = "Chave de Acesso da NFS-e Nacional: 1100049{$cnpjPrestadorLimpo}{$numeroNfse}";
            }

            $outrasInformacoes = implode('<br>', $outrasInformacoesArr);
        }

        $chaveAcesso = $dadosXml['chave_acesso'] ?? null;
        if (empty($chaveAcesso) && ! empty($outrasInformacoesRaw)) {
            if (preg_match('/Chave de Acesso(?:\s+da\s+NFS-e\s+Nacional)?:?\s*(\d{40,50})/', $outrasInformacoesRaw, $matches)) {
                $chaveAcesso = $matches[1];
            }
        }
        if (empty($chaveAcesso) && ! empty($numeroNfse) && $numeroNfse !== 'RASCUNHO') {
            $cnpjPrestadorLimpo = preg_replace('/\D/', '', $configuracao?->cnpj ?? '');
            $chaveAcesso = "1100049{$cnpjPrestadorLimpo}{$numeroNfse}";
        }

        $urlQrcode = null;
        $qrcodeBase64 = null;
        if (! empty($chaveAcesso)) {
            $urlQrcode = "https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave={$chaveAcesso}";
        } elseif (! empty($codigoVerificacao) && $codigoVerificacao !== 'Pendente') {
            $urlQrcode = "https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave={$codigoVerificacao}";
        }

        if (! empty($urlQrcode)) {
            try {
                $opcoesQr = new \chillerlan\QRCode\QROptions([
                    'outputType' => \chillerlan\QRCode\QRCode::OUTPUT_MARKUP_SVG,
                    'eccLevel' => \chillerlan\QRCode\QRCode::ECC_L,
                    'scale' => 5,
                    'imageBase64' => true,
                ]);
                $qrcodeBase64 = (new \chillerlan\QRCode\QRCode($opcoesQr))->render($urlQrcode);
            } catch (\Throwable $e) {
                $qrcodeBase64 = null;
            }
        }

        $visualizadoEm = Carbon::now()->format('d/m/Y H:i:s');

        return [
            'eh_rascunho' => $ehRascunho,
            'status' => $notaFiscal->status,
            'numero_nfse' => $numeroNfse ?: 'RASCUNHO',
            'codigo_verificacao' => $codigoVerificacao,
            'chave_acesso' => $chaveAcesso,
            'url_qrcode' => $urlQrcode,
            'qrcode_base64' => $qrcodeBase64,
            'titulo_nota' => $ehRascunho ? 'RASCUNHO DE NOTA FISCAL DE SERVIÇOS ELETRÔNICA - NFS-e' : 'NOTA FISCAL DE SERVIÇOS ELETRÔNICA - NFS-e',
            'rps_texto' => $textoRps,
            'data_emissao' => $dataEmissaoBr,
            'competencia' => $competencia,
            'municipio_prestacao' => $municipioPrestacaoTexto,
            'regime_especial_tributacao' => $regimeEspecialTributacaoTexto,
            'exigibilidade_iss' => $exigibilidadeIssTexto,

            // Prestador
            'prestador_razao_social' => $prestadorRazaoSocial,
            'prestador_nome_fantasia' => $prestadorNomeFantasia,
            'prestador_email' => $prestadorEmail,
            'prestador_cnpj' => $prestadorCnpj,
            'prestador_inscricao_municipal' => $prestadorInscricaoMunicipal,
            'prestador_inscricao_estadual' => $prestadorInscricaoEstadual,
            'prestador_simples_nacional' => $prestadorSimplesNacional,
            'prestador_incentivador_cultural' => $prestadorIncentivadorCultural,
            'prestador_telefone' => $prestadorFone,
            'prestador_endereco' => $prestadorEndereco,

            // Tomador
            'tomador_nome' => $tomadorNome,
            'tomador_cpf_cnpj' => $tomadorCpfCnpj,
            'tomador_inscricao_municipal' => $tomadorInscricaoMunicipal,
            'tomador_inscricao_estadual' => $tomadorInscricaoEstadual,
            'tomador_telefone' => $tomadorFone,
            'tomador_email' => $tomadorEmail,
            'tomador_endereco' => $tomadorEndereco,

            // Serviço Prestado
            'servico_prestado_texto' => $servicoPrestadoTexto,
            'discriminacao_servicos' => $discriminacaoServico,

            // Tributos Federais
            'valor_inss' => number_format($valorInss, 2, ',', '.'),
            'valor_ir' => number_format($valorIr, 2, ',', '.'),
            'valor_pis' => number_format($valorPis, 2, ',', '.'),
            'valor_cofins' => number_format($valorCofins, 2, ',', '.'),
            'valor_csll' => number_format($valorCsll, 2, ',', '.'),
            'outras_retencoes' => number_format($outrasRetencoes, 2, ',', '.'),

            // Valores
            'valor_deducoes' => number_format($valorDeducoes, 2, ',', '.'),
            'desconto_condicionado' => number_format($descontoCondicionado, 2, ',', '.'),
            'desconto_incondicionado' => number_format($descontoIncondicionado, 2, ',', '.'),
            'base_calculo_iss' => $exibirAsteriscosIss ? '*****' : number_format($baseCalculoIssVal, 2, ',', '.'),
            'aliquota_iss' => number_format($aliquotaIss, 4, ',', '.'),
            'valor_servicos' => number_format($valorServicos, 2, ',', '.'),
            'valor_iss' => $exibirAsteriscosIss ? '*****' : number_format($valorIssVal, 2, ',', '.'),
            'iss_retido' => $exibirAsteriscosIss ? '*****' : number_format($issRetidoVal, 2, ',', '.'),
            'valor_liquido' => number_format($valorLiquidoVal, 2, ',', '.'),
            'valor_total' => number_format($valorTotalVal, 2, ',', '.'),

            // Outras Informações & Rodapé
            'outras_informacoes' => $outrasInformacoes,
            'visualizado_em' => $visualizadoEm,
            'url_validacao' => 'http://cacoalro.webiss.com.br/externo/nfse/validar',
        ];
    }

    /**
     * Faz a leitura de nós relevantes em um XML ABRASF v2.
     */
    private function extrairDadosDoXml(string $xmlContent): array
    {
        $dados = [];
        libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        if (! @$dom->loadXML($xmlContent)) {
            return $dados;
        }

        $obterTextoNo = function (DOMDocument $d, string $tagName): ?string {
            $nos = $d->getElementsByTagName($tagName);
            if ($nos->length > 0) {
                return trim($nos->item(0)->nodeValue);
            }

            return null;
        };

        $dados['numero_nfse'] = $obterTextoNo($dom, 'Numero');
        $dados['codigo_verificacao'] = $obterTextoNo($dom, 'CodigoVerificacao');
        $dados['data_emissao_nfse'] = $obterTextoNo($dom, 'DataEmissao');
        $dados['competencia'] = $obterTextoNo($dom, 'Competencia');
        $dados['outras_informacoes'] = $obterTextoNo($dom, 'OutrasInformacoes');

        // Valores
        $dados['valor_servicos'] = $obterTextoNo($dom, 'ValorServicos');
        $dados['valor_deducoes'] = $obterTextoNo($dom, 'ValorDeducoes');
        $dados['valor_pis'] = $obterTextoNo($dom, 'ValorPis');
        $dados['valor_cofins'] = $obterTextoNo($dom, 'ValorCofins');
        $dados['valor_inss'] = $obterTextoNo($dom, 'ValorInss');
        $dados['valor_ir'] = $obterTextoNo($dom, 'ValorIr');
        $dados['valor_csll'] = $obterTextoNo($dom, 'ValorCsll');
        $dados['valor_iss'] = $obterTextoNo($dom, 'ValorIss');
        $dados['aliquota_iss'] = $obterTextoNo($dom, 'Aliquota');
        $dados['desconto_incondicionado'] = $obterTextoNo($dom, 'DescontoIncondicionado');
        $dados['desconto_condicionado'] = $obterTextoNo($dom, 'DescontoCondicionado');
        $dados['item_lista_servico'] = $obterTextoNo($dom, 'ItemListaServico');
        $dados['codigo_cnae'] = $obterTextoNo($dom, 'CodigoCnae');
        $dados['discriminacao_servico'] = $obterTextoNo($dom, 'Discriminacao');
        $dados['codigo_municipio_prestacao'] = $obterTextoNo($dom, 'CodigoMunicipio');

        $extrairBlocoEndereco = function (DOMElement $container): array {
            $nosEndereco = $container->getElementsByTagName('Endereco');
            if ($nosEndereco->length === 0) {
                return [];
            }
            /** @var DOMElement $noEndereco */
            $noEndereco = $nosEndereco->item(0);

            $res = [];
            foreach ($noEndereco->childNodes as $child) {
                if ($child instanceof DOMElement) {
                    match ($child->nodeName) {
                        'Endereco' => $res['logradouro'] = trim($child->nodeValue),
                        'Numero' => $res['numero'] = trim($child->nodeValue),
                        'Complemento' => $res['complemento'] = trim($child->nodeValue),
                        'Bairro' => $res['bairro'] = trim($child->nodeValue),
                        'CodigoMunicipio' => $res['codigo_municipio'] = trim($child->nodeValue),
                        'Uf' => $res['uf'] = trim($child->nodeValue),
                        'Cep' => $res['cep'] = trim($child->nodeValue),
                        default => null,
                    };
                }
            }

            if (empty($res['logradouro']) && ! $noEndereco->hasChildNodes()) {
                $res['logradouro'] = trim($noEndereco->nodeValue);
            }

            return $res;
        };

        // Prestador
        $nosPrestador = $dom->getElementsByTagName('PrestadorServico');
        if ($nosPrestador->length === 0) {
            $nosPrestador = $dom->getElementsByTagName('Prestador');
        }
        if ($nosPrestador->length > 0) {
            /** @var DOMElement $pNode */
            $pNode = $nosPrestador->item(0);
            $dados['prestador_razao_social'] = $pNode->getElementsByTagName('RazaoSocial')->item(0)?->nodeValue;
            $dados['prestador_nome_fantasia'] = $pNode->getElementsByTagName('NomeFantasia')->item(0)?->nodeValue;
            $dados['prestador_cnpj'] = $pNode->getElementsByTagName('Cnpj')->item(0)?->nodeValue;
            $dados['prestador_inscricao_municipal'] = $pNode->getElementsByTagName('InscricaoMunicipal')->item(0)?->nodeValue;
            $dados['prestador_telefone'] = $pNode->getElementsByTagName('Telefone')->item(0)?->nodeValue;
            $dados['prestador_email'] = $pNode->getElementsByTagName('Email')->item(0)?->nodeValue;

            $endP = $extrairBlocoEndereco($pNode);
            if (! empty($endP['logradouro'])) {
                $dados['prestador_logradouro'] = $endP['logradouro'];
            }
            if (! empty($endP['numero'])) {
                $dados['prestador_numero'] = $endP['numero'];
            }
            if (! empty($endP['complemento'])) {
                $dados['prestador_complemento'] = $endP['complemento'];
            }
            if (! empty($endP['bairro'])) {
                $dados['prestador_bairro'] = $endP['bairro'];
            }
            if (! empty($endP['codigo_municipio'])) {
                $dados['prestador_codigo_municipio'] = $endP['codigo_municipio'];
            }
            if (! empty($endP['uf'])) {
                $dados['prestador_uf'] = $endP['uf'];
            }
            if (! empty($endP['cep'])) {
                $dados['prestador_cep'] = $endP['cep'];
            }
        }

        // Tomador
        $nosTomador = $dom->getElementsByTagName('TomadorServico');
        if ($nosTomador->length === 0) {
            $nosTomador = $dom->getElementsByTagName('Tomador');
        }
        if ($nosTomador->length > 0) {
            /** @var DOMElement $tNode */
            $tNode = $nosTomador->item(0);
            $dados['tomador_nome'] = $tNode->getElementsByTagName('RazaoSocial')->item(0)?->nodeValue;
            $dados['tomador_cpf_cnpj'] = $tNode->getElementsByTagName('Cpf')->item(0)?->nodeValue
                ?? $tNode->getElementsByTagName('Cnpj')->item(0)?->nodeValue;
            $dados['tomador_inscricao_municipal'] = $tNode->getElementsByTagName('InscricaoMunicipal')->item(0)?->nodeValue;
            $dados['tomador_telefone'] = $tNode->getElementsByTagName('Telefone')->item(0)?->nodeValue;
            $dados['tomador_email'] = $tNode->getElementsByTagName('Email')->item(0)?->nodeValue;

            $endT = $extrairBlocoEndereco($tNode);
            if (! empty($endT['logradouro'])) {
                $dados['tomador_logradouro'] = $endT['logradouro'];
            }
            if (! empty($endT['numero'])) {
                $dados['tomador_numero'] = $endT['numero'];
            }
            if (! empty($endT['complemento'])) {
                $dados['tomador_complemento'] = $endT['complemento'];
            }
            if (! empty($endT['bairro'])) {
                $dados['tomador_bairro'] = $endT['bairro'];
            }
            if (! empty($endT['codigo_municipio'])) {
                $dados['tomador_codigo_municipio'] = $endT['codigo_municipio'];
            }
            if (! empty($endT['uf'])) {
                $dados['tomador_uf'] = $endT['uf'];
            }
            if (! empty($endT['cep'])) {
                $dados['tomador_cep'] = $endT['cep'];
            }
        }

        return array_filter($dados, fn ($val) => ! is_null($val) && $val !== '');
    }

    private function formatarCpfCnpj(?string $valor): string
    {
        if (empty($valor)) {
            return '';
        }
        $limpo = preg_replace('/\D/', '', $valor);
        if (strlen($limpo) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $limpo);
        }
        if (strlen($limpo) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $limpo);
        }

        return $valor;
    }

    private function formatarTelefone(?string $valor): string
    {
        if (empty($valor)) {
            return '';
        }
        $limpo = preg_replace('/\D/', '', $valor);
        if (strlen($limpo) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $limpo);
        }
        if (strlen($limpo) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $limpo);
        }

        return $valor;
    }

    private function formatarDataHora($valor): string
    {
        if (empty($valor)) {
            return Carbon::now()->format('d/m/Y H:i:s');
        }
        try {
            return Carbon::parse($valor)->format('d/m/Y H:i:s');
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    }

    private function formatarData($valor): string
    {
        if (empty($valor)) {
            return Carbon::now()->format('d/m/Y');
        }
        try {
            return Carbon::parse($valor)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    }

    private function formatarCompetencia($valor): string
    {
        if (empty($valor)) {
            return Carbon::now()->format('m/Y');
        }
        try {
            return Carbon::parse($valor)->format('m/Y');
        } catch (\Throwable $e) {
            return (string) $valor;
        }
    }

    private function formatarItemListaServico(string $item): string
    {
        $limpo = preg_replace('/\D/', '', $item);
        if (strlen($limpo) === 4) {
            return sprintf('%04d', $limpo);
        }

        return $item;
    }

    private function montarEnderecoTexto(array $partes): string
    {
        $logradouroEBairro = [];

        $logradouro = trim($partes['logradouro'] ?? '');
        if (! empty($logradouro)) {
            $logradouroEBairro[] = $logradouro;
        }

        $numero = trim($partes['numero'] ?? '');
        if (! empty($numero)) {
            $logradouroEBairro[] = $numero;
        }

        $complemento = trim($partes['complemento'] ?? '');
        if (! empty($complemento)) {
            $logradouroEBairro[] = $complemento;
        }

        $bairro = trim($partes['bairro'] ?? '');
        if (! empty($bairro)) {
            $logradouroEBairro[] = $bairro;
        }

        $textoLogradouro = implode(', ', $logradouroEBairro);

        $partesFinais = [];
        if (! empty($textoLogradouro)) {
            $partesFinais[] = $textoLogradouro;
        }

        $cep = trim($partes['cep'] ?? '');
        if (! empty($cep)) {
            $cepLimpo = preg_replace('/\D/', '', $cep);
            $cepFmt = strlen($cepLimpo) === 8 ? preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cepLimpo) : $cep;
            $partesFinais[] = "CEP: {$cepFmt}";
        }

        $cidade = trim($partes['cidade'] ?? '');
        if (! empty($cidade)) {
            $cidadeLimpa = preg_replace('/\s*-\s*[A-Z]{2}\s*$/i', '', $cidade);
            $partesFinais[] = $cidadeLimpa;
        }

        $uf = trim($partes['uf'] ?? '');
        if (! empty($uf)) {
            $partesFinais[] = strtoupper($uf);
        }

        return implode(' - ', array_filter($partesFinais));
    }

    private function obterInfoMunicipioPorIbge(?string $ibge): array
    {
        if (! empty($ibge)) {
            $cidadeModel = Cidade::where('codigo_ibge', $ibge)->first();
            if ($cidadeModel) {
                return [
                    'cidade' => $cidadeModel->nome,
                    'uf' => $cidadeModel->uf,
                ];
            }
        }

        return [
            'cidade' => 'Cacoal',
            'uf' => 'RO',
        ];
    }

    private function obterDescricaoRegimeEspecial($codigo): string
    {
        return match ((int) $codigo) {
            1 => 'Microempresa Municipal',
            2 => 'Estimativa',
            3 => 'Sociedade de Profissionais',
            4 => 'Cooperativa',
            5 => 'Microempresário Individual (MEI)',
            6 => 'Microempresário e Empresa de Pequeno Porte (ME EPP)',
            default => 'Microempresário e Empresa de Pequeno Porte (ME EPP)',
        };
    }

    private function formatarQuebrasDeLinha(?string $texto): string
    {
        if (empty($texto)) {
            return '';
        }

        $limpo = e($texto);
        $limpo = str_replace(['\s\n', '\r\n', '\n', '\r'], '<br/>', $limpo);
        $limpo = preg_replace('/(\\\\s)?[ \t]*[\r\n]+/', '<br/>', $limpo);

        return $limpo;
    }
}
