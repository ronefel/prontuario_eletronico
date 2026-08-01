<?php

namespace Tests\Unit;

use App\Filament\Resources\NotasFiscais\NotaFiscalResource;
use App\Models\Cidade;
use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use App\Models\Paciente;
use App\Services\NotaFiscal\AssinadorXmlService;
use App\Services\NotaFiscal\ClienteNfseSoapService;
use App\Services\NotaFiscal\EmissorNfseService;
use App\Services\NotaFiscal\GeradorXmlRpsService;
use App\Services\NotaFiscal\ValidadorXmlService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class NotaFiscalTest extends TestCase
{
    use RefreshDatabase;

    public function test_geracao_xml_rps_abrasf_v2()
    {
        $gerador = new GeradorXmlRpsService;

        $cidade = (new Cidade)->forceFill([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);

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
        $paciente->setRelation('cidade', $cidade);

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

    public function test_falha_geracao_xml_sem_codigo_ibge_do_tomador()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('O código IBGE da cidade do tomador');

        $gerador = new GeradorXmlRpsService;

        $paciente = (new Paciente)->forceFill([
            'nome' => 'Maria de Oliveira',
            'cpf' => '98765432100',
            'logradouro' => 'Rua das Flores',
            'numero' => '456',
        ]);

        $notaFiscal = (new NotaFiscal)->forceFill([
            'numero_rps' => 102,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'valor_servicos' => 150.00,
            'discriminacao_servico' => 'Consulta',
        ]);
        $notaFiscal->setRelation('paciente', $paciente);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'cnpj' => '00000000000191',
        ]);

        $gerador->gerarXml($notaFiscal, $configuracao);
    }

    public function test_processamento_resposta_xml_com_output_xml_e_erro_detalhado()
    {
        $paciente = (new Paciente)->forceFill([
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'nascimento' => '1990-01-01',
            'sexo' => 'M',
        ]);
        $paciente->save();

        $notaFiscal = (new NotaFiscal)->forceFill([
            'paciente_id' => $paciente->id,
            'numero_rps' => 101,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => now(),
            'valor_servicos' => 100.00,
            'aliquota_iss' => 2.00,
            'item_lista_servico' => '04.01',
            'discriminacao_servico' => 'Consulta',
            'codigo_municipio_ibge' => '1100049',
        ]);
        $notaFiscal->save();

        $respostaSoap = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body><GerarNfseResponse xmlns="http://nfse.abrasf.org.br"><outputXML xmlns="">&lt;?xml version="1.0" encoding="utf-8"?&gt;&lt;GerarNfseResposta xmlns="http://www.abrasf.org.br/nfse.xsd"&gt;&lt;ListaMensagemRetorno&gt;&lt;MensagemRetorno&gt;&lt;Codigo&gt;E324&lt;/Codigo&gt;&lt;Mensagem&gt;Assinatura do RPS inválida.&lt;/Mensagem&gt;&lt;Correcao&gt;O RPS deve conter assinatura digital vinculada a certificado digital padrão ICP Brasil.&lt;/Correcao&gt;&lt;/MensagemRetorno&gt;&lt;/ListaMensagemRetorno&gt;&lt;/GerarNfseResposta&gt;</outputXML></GerarNfseResponse></soap:Body></soap:Envelope>';

        $emissor = new EmissorNfseService(
            $this->createMock(GeradorXmlRpsService::class),
            $this->createMock(AssinadorXmlService::class),
            $this->createMock(ValidadorXmlService::class),
            $this->createMock(ClienteNfseSoapService::class)
        );

        $metodo = new ReflectionMethod(EmissorNfseService::class, 'processarResposta');
        $metodo->setAccessible(true);
        $metodo->invoke($emissor, $notaFiscal, $respostaSoap);

        $this->assertEquals('rejeitada', $notaFiscal->status);
        $this->assertEquals('E324', $notaFiscal->codigo_erro);
        $this->assertStringContainsString('E324', $notaFiscal->mensagem_erro);
        $this->assertStringContainsString('Assinatura do RPS inválida', $notaFiscal->mensagem_erro);
        $this->assertStringContainsString('Correção: O RPS deve conter assinatura digital', $notaFiscal->mensagem_erro);
    }

    public function test_permissao_edicao_e_exclusao_nota_fiscal_rejeitada()
    {
        $notaRascunho = (new NotaFiscal)->forceFill(['status' => 'rascunho']);
        $notaRejeitada = (new NotaFiscal)->forceFill(['status' => 'rejeitada']);
        $notaAutorizada = (new NotaFiscal)->forceFill(['status' => 'autorizada']);

        $this->assertTrue(NotaFiscalResource::canEdit($notaRascunho));
        $this->assertTrue(NotaFiscalResource::canEdit($notaRejeitada));
        $this->assertFalse(NotaFiscalResource::canEdit($notaAutorizada));

        $this->assertTrue(NotaFiscalResource::canDelete($notaRascunho));
        $this->assertTrue(NotaFiscalResource::canDelete($notaRejeitada));
        $this->assertFalse(NotaFiscalResource::canDelete($notaAutorizada));
    }

    public function test_discriminacao_servico_padrao_configuracao_nota_fiscal()
    {
        $config = ConfiguracaoNotaFiscal::create([
            'cnpj' => '00000000000191',
            'razao_social' => 'Clínica Exemplo',
            'codigo_municipio_ibge' => '1100049',
            'uf' => 'RO',
            'discriminacao_servico' => 'Consulta Médica Especializada e Exames Clínicos',
        ]);

        $this->assertEquals('Consulta Médica Especializada e Exames Clínicos', $config->discriminacao_servico);
        $this->assertEquals('Consulta Médica Especializada e Exames Clínicos', ConfiguracaoNotaFiscal::obterConfiguracaoAtiva()->discriminacao_servico);
    }

    public function test_geracao_xml_cancelamento_e_validacao_xsd()
    {
        $gerador = new GeradorXmlRpsService;

        $notaFiscal = (new NotaFiscal)->forceFill([
            'numero_nfse' => '202600000000123',
            'codigo_municipio_ibge' => '1100049',
        ]);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'cnpj' => '00000000000191',
            'inscricao_municipal' => '12345',
            'codigo_municipio_ibge' => '1100049',
        ]);

        $xml = $gerador->gerarXmlCancelamento($notaFiscal, $configuracao, '1');

        $this->assertStringContainsString('<CancelarNfseEnvio', $xml);
        $this->assertStringContainsString('<Numero>202600000000123</Numero>', $xml);
        $this->assertStringContainsString('<CodigoCancelamento>1</CodigoCancelamento>', $xml);
        $this->assertStringContainsString('<Cnpj>00000000000191</Cnpj>', $xml);

        $validador = new ValidadorXmlService;
        $validacao = $validador->validar($xml);

        $this->assertTrue(
            $validacao['valido'],
            'O XML de cancelamento gerado falhou na validação XSD: '.implode(' | ', $validacao['erros'] ?? [])
        );
    }

    public function test_geracao_xml_substituicao_e_validacao_xsd()
    {
        $gerador = new GeradorXmlRpsService;

        $cidade = (new Cidade)->forceFill([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);

        $paciente = (new Paciente)->forceFill([
            'nome' => 'João da Silva',
            'cpf' => '12345678901',
            'logradouro' => 'Av. Porto Velho',
            'numero' => '1234',
            'bairro' => 'Centro',
            'cep' => '76960000',
        ]);
        $paciente->setRelation('cidade', $cidade);

        $notaAntiga = (new NotaFiscal)->forceFill([
            'numero_nfse' => '202600000000123',
            'codigo_municipio_ibge' => '1100049',
        ]);

        $novaNota = (new NotaFiscal)->forceFill([
            'numero_rps' => 105,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => new \DateTime('2026-07-30 10:00:00'),
            'valor_servicos' => 300.00,
            'valor_deducoes' => 0.00,
            'valor_pis' => 0.00,
            'valor_cofins' => 0.00,
            'valor_inss' => 0.00,
            'valor_ir' => 0.00,
            'valor_csll' => 0.00,
            'valor_iss' => 6.00,
            'aliquota_iss' => 2.00,
            'desconto_incondicionado' => 0.00,
            'desconto_condicionado' => 0.00,
            'item_lista_servico' => '04.01',
            'discriminacao_servico' => 'Consulta Reavaliada',
            'codigo_municipio_ibge' => '1100049',
        ]);
        $novaNota->setRelation('paciente', $paciente);

        $configuracao = (new ConfiguracaoNotaFiscal)->forceFill([
            'cnpj' => '00000000000191',
            'inscricao_municipal' => '12345',
            'razao_social' => 'Clínica Médica Cacoal LTDA',
            'codigo_municipio_ibge' => '1100049',
            'uf' => 'RO',
            'optante_simples_nacional' => true,
            'incentivador_cultural' => false,
            'item_lista_servico' => '04.01',
            'aliquota_iss' => 2.00,
        ]);

        $xml = $gerador->gerarXmlSubstituicao($notaAntiga, $novaNota, $configuracao, '1');

        $this->assertStringContainsString('<SubstituirNfseEnvio', $xml);
        $this->assertStringContainsString('<SubstituicaoNfse', $xml);
        $this->assertStringContainsString('<Numero>202600000000123</Numero>', $xml);
        $this->assertStringContainsString('<Numero>105</Numero>', $xml);

        $validador = new ValidadorXmlService;
        $validacao = $validador->validar($xml);

        $this->assertTrue(
            $validacao['valido'],
            'O XML de substituição gerado falhou na validação XSD: '.implode(' | ', $validacao['erros'] ?? [])
        );
    }

    public function test_aplicacao_codigo_ibge_configuracao_ao_criar_e_editar_nota_fiscal()
    {
        $configuracao = ConfiguracaoNotaFiscal::create([
            'cnpj' => '00000000000191',
            'razao_social' => 'Clínica Exemplo LTDA',
            'codigo_municipio_ibge' => '3550308', // São Paulo
            'uf' => 'SP',
            'discriminacao_servico' => 'Consulta Médica',
        ]);

        $paciente = Paciente::forceCreate([
            'nome' => 'Paciente Teste',
            'cpf' => '12345678901',
            'nascimento' => '1990-01-01',
            'sexo' => 'M',
        ]);

        $notaFiscal = new NotaFiscal([
            'paciente_id' => $paciente->id,
            'numero_rps' => 1,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => now(),
            'valor_servicos' => 100.00,
            'aliquota_iss' => 2.00,
            'item_lista_servico' => '04.01',
            'discriminacao_servico' => 'Consulta Médica',
            'status' => 'rascunho',
        ]);

        $notaFiscal->save();

        $this->assertEquals('3550308', $notaFiscal->codigo_municipio_ibge);

        // Altera o IBGE na configuração ativa
        $configuracao->update(['codigo_municipio_ibge' => '3304557']); // Rio de Janeiro

        // Salva a nota fiscal (como em uma edição)
        $notaFiscal->valor_servicos = 150.00;
        $notaFiscal->save();

        $this->assertEquals('3304557', $notaFiscal->fresh()->codigo_municipio_ibge);
    }
}
