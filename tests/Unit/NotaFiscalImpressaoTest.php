<?php

namespace Tests\Unit;

use App\Models\Cidade;
use App\Models\ConfiguracaoNotaFiscal;
use App\Models\NotaFiscal;
use App\Models\Paciente;
use App\Models\User;
use App\Services\NotaFiscal\DadosImpressaoNotaFiscalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaFiscalImpressaoTest extends TestCase
{
    use RefreshDatabase;

    public function test_extração_de_dados_de_impressão_quando_rascunho()
    {
        $cidade = (new Cidade)->forceFill([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);
        $cidade->save();

        $paciente = (new Paciente)->forceFill([
            'nome' => 'Carlos Eduardo',
            'cpf' => '12345678909',
            'nascimento' => '1990-01-01',
            'sexo' => 'M',
            'email' => 'carlos@exemplo.com',
            'celular' => '69999991111',
            'logradouro' => 'Rua A',
            'numero' => '100',
            'bairro' => 'Centro',
            'cep' => '76960000',
            'cidade_id' => $cidade->id,
        ]);
        $paciente->save();

        ConfiguracaoNotaFiscal::create([
            'cnpj' => '64897206000149',
            'razao_social' => 'KRIS CARDOSO INSTITUTO DE SAUDE INTEGRATIVA LTDA',
            'nome_fantasia' => 'KRYS CARDOSO SAUDE INTEGRATIVA',
            'codigo_municipio_ibge' => '1100049',
            'uf' => 'RO',
            'optante_simples_nacional' => true,
            'aliquota_iss' => 2.00,
        ]);

        $notaFiscal = (new NotaFiscal)->forceFill([
            'paciente_id' => $paciente->id,
            'numero_rps' => 12,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => new \DateTime('2026-08-01 10:00:00'),
            'valor_servicos' => 500.00,
            'aliquota_iss' => 2.00,
            'item_lista_servico' => '04.09',
            'discriminacao_servico' => 'Consulta Integrativa de Teste',
            'status' => 'rascunho',
            'codigo_municipio_ibge' => '1100049',
        ]);
        $notaFiscal->save();

        $servico = new DadosImpressaoNotaFiscalService();
        $dados = $servico->obterDadosImpressao($notaFiscal);

        $this->assertTrue($dados['eh_rascunho']);
        $this->assertEquals('RASCUNHO', $dados['numero_nfse']);
        $this->assertStringContainsString('RASCUNHO DE NOTA FISCAL DE SERVIÇOS ELETRÔNICA', $dados['titulo_nota']);
        $this->assertEquals('Carlos Eduardo', $dados['tomador_nome']);
        $this->assertEquals('123.456.789-09', $dados['tomador_cpf_cnpj']);
        $this->assertEquals('500,00', $dados['valor_servicos']);
        $this->assertEquals('Consulta Integrativa de Teste', $dados['discriminacao_servicos']);
    }

    public function test_extração_de_dados_de_impressão_a_partir_do_xml_de_retorno()
    {
        $cidade = (new Cidade)->forceFill([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);
        $cidade->save();

        $paciente = (new Paciente)->forceFill([
            'nome' => 'Gabriel Souza',
            'cpf' => '03198817250',
            'nascimento' => '1990-01-01',
            'sexo' => 'M',
            'cidade_id' => $cidade->id,
        ]);
        $paciente->save();

        $xmlRetorno = '<?xml version="1.0" encoding="utf-8"?>
        <CompNfse xmlns="http://www.abrasf.org.br/nfse.xsd">
            <Nfse versao="2.02">
                <InfNfse Id="NFSe2026000000011">
                    <Numero>2026000000011</Numero>
                    <CodigoVerificacao>2WR3-2WDX</CodigoVerificacao>
                    <DataEmissao>2026-07-29T23:53:33</DataEmissao>
                    <ValoresNfse>
                        <ValorServicos>830.00</ValorServicos>
                        <ValorDeducoes>0.00</ValorDeducoes>
                        <ValorIss>16.60</ValorIss>
                        <Aliquota>2.00</Aliquota>
                    </ValoresNfse>
                    <Servico>
                        <ItemListaServico>04.09</ItemListaServico>
                        <Discriminacao>Consulta Biomédica em Saúde</Discriminacao>
                    </Servico>
                    <PrestadorServico>
                        <RazaoSocial>KRIS CARDOSO INSTITUTO DE SAUDE INTEGRATIVA LTDA</RazaoSocial>
                        <Cnpj>64897206000149</Cnpj>
                    </PrestadorServico>
                    <TomadorServico>
                        <RazaoSocial>Gabriel Souza Da Silva</RazaoSocial>
                        <CpfCnpj><Cpf>03198817250</Cpf></CpfCnpj>
                    </TomadorServico>
                </InfNfse>
            </Nfse>
        </CompNfse>';

        $notaFiscal = (new NotaFiscal)->forceFill([
            'paciente_id' => $paciente->id,
            'numero_rps' => 8,
            'serie_rps' => '1',
            'data_emissao_rps' => new \DateTime('2026-07-29 23:53:33'),
            'numero_nfse' => '2026000000011',
            'codigo_verificacao' => '2WR3-2WDX',
            'valor_servicos' => 830.00,
            'item_lista_servico' => '04.09',
            'discriminacao_servico' => 'Consulta Biomédica em Saúde',
            'status' => 'autorizada',
            'xml_retorno' => $xmlRetorno,
        ]);
        $notaFiscal->save();

        $servico = new DadosImpressaoNotaFiscalService();
        $dados = $servico->obterDadosImpressao($notaFiscal);

        $this->assertFalse($dados['eh_rascunho']);
        $this->assertEquals('2026000000011', $dados['numero_nfse']);
        $this->assertEquals('2WR3-2WDX', $dados['codigo_verificacao']);
        $this->assertEquals('Gabriel Souza Da Silva', $dados['tomador_nome']);
        $this->assertEquals('031.988.172-50', $dados['tomador_cpf_cnpj']);
        $this->assertEquals('830,00', $dados['valor_servicos']);
    }

    public function test_rota_http_de_impressão_da_nota_fiscal()
    {
        $usuario = User::factory()->create();

        $paciente = Paciente::forceCreate([
            'nome' => 'Paciente Rota Impressao',
            'cpf' => '99988877766',
            'nascimento' => '1990-01-01',
            'sexo' => 'M',
        ]);

        $notaFiscal = NotaFiscal::forceCreate([
            'paciente_id' => $paciente->id,
            'numero_rps' => 99,
            'serie_rps' => '1',
            'tipo_rps' => 1,
            'data_emissao_rps' => now(),
            'valor_servicos' => 120.00,
            'aliquota_iss' => 2.00,
            'item_lista_servico' => '04.01',
            'discriminacao_servico' => 'Atendimento',
            'status' => 'rascunho',
        ]);

        $resposta = $this->actingAs($usuario)->get(route('notas-fiscais.impressao', ['id' => $notaFiscal->id]));

        $resposta->assertStatus(200);
        $resposta->assertSee('RASCUNHO DE NOTA FISCAL DE SERVIÇOS ELETRÔNICA');
        $resposta->assertSee('Paciente Rota Impressao');
        $resposta->assertSee('120,00');
    }

    public function test_formatação_de_endereço_no_padrão_solicitado()
    {
        $servico = new DadosImpressaoNotaFiscalService();
        $reflector = new \ReflectionClass($servico);
        $metodo = $reflector->getMethod('montarEnderecoTexto');
        $metodo->setAccessible(true);

        $resultado = $metodo->invoke($servico, [
            'logradouro' => 'Rua da Homologação',
            'numero' => '9999',
            'bairro' => 'Bairro da Homologação',
            'cep' => '99999999',
            'cidade' => 'Cidade da Homologação',
            'uf' => 'HM',
        ]);

        $this->assertEquals(
            'Rua da Homologação, 9999, Bairro da Homologação - CEP: 99999-999 - Cidade da Homologação - HM',
            $resultado
        );
    }

    public function test_substituição_de_quebra_de_linha_literal_e_real_por_br()
    {
        $servico = new DadosImpressaoNotaFiscalService();
        $reflector = new \ReflectionClass($servico);
        $metodo = $reflector->getMethod('formatarQuebrasDeLinha');
        $metodo->setAccessible(true);

        $textoComBarraSN = "Chave de Acesso\s\nOptante do Simples\s\nSubstitui nota 2026";
        $resultado = $metodo->invoke($servico, $textoComBarraSN);

        $this->assertEquals(
            'Chave de Acesso<br/>Optante do Simples<br/>Substitui nota 2026',
            $resultado
        );
    }
}
