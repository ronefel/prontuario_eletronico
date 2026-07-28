<?php

namespace Tests\Unit;

use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use App\Models\Paciente;
use App\Services\NotaFiscal\GeradorXmlRpsService;
use App\Services\NotaFiscal\ValidadorXmlService;
use Tests\TestCase;

class NotaFiscalTest extends TestCase
{
    public function test_geracao_xml_rps_abrasf_v2()
    {
        $gerador = new GeradorXmlRpsService;

        $paciente = (new Paciente)->forceFill([
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'email' => 'joao@exemplo.com',
            'celular' => '69999998888',
            'logradouro' => 'Av. Porto Velho',
            'numero' => '1234',
            'bairro' => 'Centro',
            'cep' => '76960000',
        ]);

        $notaFiscal = (new NotaFiscal)->forceFill([
            'numero_rps' => 101,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => new \DateTime('2026-07-27 10:00:00'),
            'valor_servicos' => 250.00,
            'valor_deducoes' => 0.00,
            'valor_pis' => 0.00,
            'valor_cofins' => 0.00,
            'valor_inss' => 0.00,
            'valor_ir' => 0.00,
            'valor_csll' => 0.00,
            'valor_iss' => 5.00,
            'aliquota_iss' => 2.00,
            'desconto_incondicionado' => 0.00,
            'desconto_condicionado' => 0.00,
            'item_lista_servico' => '04.01',
            'codigo_cnae' => '8630503',
            'discriminacao_servico' => 'Consulta Médica de Rotina',
            'codigo_municipio_ibge' => '1100049',
        ]);
        $notaFiscal->setRelation('paciente', $paciente);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'cnpj' => '00000000000191',
            'inscricao_municipal' => '12345',
            'razao_social' => 'Clínica Médica Cacoal LTDA',
            'codigo_municipio_ibge' => '1100049',
            'uf' => 'RO',
            'regime_especial_tributacao' => 0,
            'optante_simples_nacional' => true,
            'incentivador_cultural' => false,
            'item_lista_servico' => '04.01',
            'aliquota_iss' => 2.00,
        ]);

        $xml = $gerador->gerarXml($notaFiscal, $configuracao);

        $this->assertStringContainsString('<GerarNfseEnvio', $xml);
        $this->assertStringContainsString('<Numero>101</Numero>', $xml);
        $this->assertStringContainsString('<ValorServicos>250.00</ValorServicos>', $xml);
        $this->assertStringContainsString('<CodigoCnae>8630503</CodigoCnae>', $xml);
        $this->assertStringContainsString('<Cnpj>00000000000191</Cnpj>', $xml);
        $this->assertStringContainsString('<Cpf>12345678901</Cpf>', $xml);
        $this->assertStringContainsString('<RazaoSocial>João da Silva</RazaoSocial>', $xml);

        // Validação estrita contra o esquema XSD (nfse-v2-02.xsd)
        $validador = new ValidadorXmlService;
        $validacao = $validador->validar($xml);

        $this->assertTrue(
            $validacao['valido'],
            'O XML gerado falhou na validação XSD: '.implode(' | ', $validacao['erros'] ?? [])
        );
    }
}
