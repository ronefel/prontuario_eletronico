<?php

namespace Tests\Unit;

use App\Models\Cidade;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CidadeTest extends TestCase
{
    use RefreshDatabase;

    public function test_cidade_model_com_codigo_ibge()
    {
        $cidade = Cidade::create([
            'nome' => 'Porto Velho',
            'uf' => 'RO',
            'codigo_ibge' => '1100205',
        ]);

        $this->assertDatabaseHas('cidades', [
            'id' => $cidade->id,
            'nome' => 'Porto Velho',
            'uf' => 'RO',
            'codigo_ibge' => '1100205',
        ]);
    }

    public function test_via_cep_atualiza_codigo_ibge_de_cidade_existente()
    {
        Http::fake([
            'https://viacep.com.br/ws/*' => Http::response([
                'cep' => '76801-974',
                'logradouro' => 'Avenida Farquar',
                'bairro' => 'Centro',
                'localidade' => 'Porto Velho',
                'uf' => 'RO',
                'ibge' => '1100205',
            ], 200),
        ]);

        $cidade = Cidade::create([
            'nome' => 'Porto Velho',
            'uf' => 'RO',
            'codigo_ibge' => null,
        ]);

        $response = Http::get('https://viacep.com.br/ws/76801-974/json/')->json();

        $cidadeEncontrada = Cidade::where('uf', $response['uf'])
            ->where('nome', $response['localidade'])
            ->first();

        $cidadeEncontrada->update(['codigo_ibge' => $response['ibge']]);

        $this->assertEquals('1100205', $cidade->fresh()->codigo_ibge);
    }

    public function test_via_cep_cria_nova_cidade_automaticamente_com_codigo_ibge()
    {
        Http::fake([
            'https://viacep.com.br/ws/*' => Http::response([
                'cep' => '01001-000',
                'logradouro' => 'Praça da Sé',
                'bairro' => 'Sé',
                'localidade' => 'São Paulo',
                'uf' => 'SP',
                'ibge' => '3550308',
            ], 200),
        ]);

        $response = Http::get('https://viacep.com.br/ws/01001-000/json/')->json();

        $cidade = Cidade::create([
            'nome' => $response['localidade'],
            'uf' => $response['uf'],
            'codigo_ibge' => $response['ibge'],
        ]);

        $this->assertDatabaseHas('cidades', [
            'nome' => 'São Paulo',
            'uf' => 'SP',
            'codigo_ibge' => '3550308',
        ]);
    }
}
