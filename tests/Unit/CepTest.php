<?php

namespace Tests\Unit;

use App\Forms\Components\Cep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CepTest extends TestCase
{
    use RefreshDatabase;

    public function test_obter_resultados_busca_endereco_com_parametros_invalidos_retorna_vazio()
    {
        $resultadoUfVazia = Cep::obterResultadosBuscaEndereco('', 'Porto Velho', 'Avenida');
        $this->assertEquals(['opcoes' => [], 'descricoes' => []], $resultadoUfVazia);

        $resultadoCidadeCurta = Cep::obterResultadosBuscaEndereco('RO', 'PV', 'Avenida');
        $this->assertEquals(['opcoes' => [], 'descricoes' => []], $resultadoCidadeCurta);

        $resultadoLogradouroCurto = Cep::obterResultadosBuscaEndereco('RO', 'Porto Velho', 'Av');
        $this->assertEquals(['opcoes' => [], 'descricoes' => []], $resultadoLogradouroCurto);
    }

    public function test_obter_resultados_busca_endereco_com_sucesso()
    {
        Http::fake([
            'https://viacep.com.br/ws/RO/Cacoal/Anapolis/json/' => Http::response([
                [
                    'cep' => '76960-000',
                    'logradouro' => 'Rua Anápolis',
                    'bairro' => 'Centro',
                    'localidade' => 'Cacoal',
                    'uf' => 'RO',
                    'ibge' => '1100049',
                    'complemento' => 'de 100 a 200',
                ],
            ], 200),
        ]);

        $resultado = Cep::obterResultadosBuscaEndereco('RO', 'Cacoal', 'Anapolis');

        $this->assertArrayHasKey('76960-000', $resultado['opcoes']);
        $this->assertStringContainsString('76960-000', $resultado['opcoes']['76960-000']);
        $this->assertStringContainsString('Rua Anápolis', $resultado['opcoes']['76960-000']);
        $this->assertStringContainsString('Bairro Centro', $resultado['descricoes']['76960-000']);
    }

    public function test_obter_resultados_busca_endereco_com_erro_api_retorna_vazio()
    {
        Http::fake([
            'https://viacep.com.br/ws/RO/Cacoal/Inexistente/json/' => Http::response(['erro' => true], 200),
        ]);

        $resultado = Cep::obterResultadosBuscaEndereco('RO', 'Cacoal', 'Inexistente');

        $this->assertEquals(['opcoes' => [], 'descricoes' => []], $resultado);
    }

    public function test_requisicao_real_via_cep_por_cep()
    {
        // Requisição HTTP real ao serviço ViaCEP para validar integração e funcionamento
        $resposta = Http::withoutVerifying()->get('https://viacep.com.br/ws/01001000/json/');

        $this->assertTrue($resposta->successful(), 'A API do ViaCEP deve responder com status 200.');
        $dados = $resposta->json();

        $this->assertArrayHasKey('cep', $dados);
        $this->assertArrayHasKey('logradouro', $dados);
        $this->assertArrayHasKey('bairro', $dados);
        $this->assertArrayHasKey('localidade', $dados);
        $this->assertArrayHasKey('uf', $dados);
        $this->assertArrayHasKey('ibge', $dados);

        $this->assertEquals('01001-000', $dados['cep']);
        $this->assertEquals('São Paulo', $dados['localidade']);
        $this->assertEquals('SP', $dados['uf']);
        $this->assertEquals('3550308', $dados['ibge']);
    }

    public function test_requisicao_real_via_cep_busca_por_endereco()
    {
        // Requisição HTTP real ao serviço ViaCEP buscando por logradouro
        $resposta = Http::withoutVerifying()->get('https://viacep.com.br/ws/SP/Sao%20Paulo/Paulista/json/');

        $this->assertTrue($resposta->successful(), 'A API de busca por endereço do ViaCEP deve responder com status 200.');
        $dados = $resposta->json();

        $this->assertIsArray($dados);
        $this->assertNotEmpty($dados, 'A busca deve retornar ao menos um resultado para a Avenida Paulista.');

        $primeiroResultado = $dados[0];
        $this->assertArrayHasKey('cep', $primeiroResultado);
        $this->assertArrayHasKey('logradouro', $primeiroResultado);
        $this->assertArrayHasKey('localidade', $primeiroResultado);
        $this->assertEquals('São Paulo', $primeiroResultado['localidade']);
        $this->assertEquals('SP', $primeiroResultado['uf']);
    }
}
