<?php

namespace Tests\Unit;

use App\Models\Cidade;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_validar_paciente_completo_para_nota_fiscal_retorna_sem_erros()
    {
        $cidade = Cidade::forceCreate([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);

        $paciente = (new Paciente)->forceFill([
            'nome' => 'João da Silva',
            'cpf' => '52998224725',
            'email' => 'joao@exemplo.com',
            'cep' => '76960000',
            'logradouro' => 'Av. Porto Velho',
            'numero' => '1234',
            'bairro' => 'Centro',
            'cidade_id' => $cidade->id,
        ]);
        $paciente->setRelation('cidade', $cidade);

        $erros = $paciente->validarParaNotaFiscal();

        $this->assertEmpty($erros, 'Paciente completo não deveria retornar erros de validação.');
    }

    public function test_validar_paciente_sem_cpf_ou_cpf_invalido_retorna_erro()
    {
        $cidade = Cidade::forceCreate([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);

        // CPF inválido
        $pacienteInvalido = (new Paciente)->forceFill([
            'nome' => 'Maria de Oliveira',
            'cpf' => '00000000000',
            'cep' => '76960000',
            'logradouro' => 'Rua A',
            'numero' => '10',
            'bairro' => 'Centro',
            'cidade_id' => $cidade->id,
        ]);
        $pacienteInvalido->setRelation('cidade', $cidade);

        $errosCpfInvalido = $pacienteInvalido->validarParaNotaFiscal();
        $this->assertContains('CPF inválido.', $errosCpfInvalido);

        // CPF ausente
        $pacienteSemCpf = (new Paciente)->forceFill([
            'nome' => 'Carlos Santos',
            'cpf' => '',
            'cep' => '76960000',
            'logradouro' => 'Rua A',
            'numero' => '10',
            'bairro' => 'Centro',
            'cidade_id' => $cidade->id,
        ]);
        $pacienteSemCpf->setRelation('cidade', $cidade);

        $errosCpfAusente = $pacienteSemCpf->validarParaNotaFiscal();
        $this->assertContains('CPF não informado.', $errosCpfAusente);
    }

    public function test_validar_paciente_com_endereco_incompleto_retorna_erros()
    {
        $cidade = Cidade::forceCreate([
            'nome' => 'Cacoal',
            'uf' => 'RO',
            'codigo_ibge' => '1100049',
        ]);

        $pacienteIncompleto = (new Paciente)->forceFill([
            'nome' => 'Ana Paula',
            'cpf' => '12345678901',
            'cep' => null,
            'logradouro' => null,
            'numero' => null,
            'bairro' => null,
            'cidade_id' => $cidade->id,
        ]);
        $pacienteIncompleto->setRelation('cidade', $cidade);

        $erros = $pacienteIncompleto->validarParaNotaFiscal();

        $this->assertContains('CEP do endereço não informado.', $erros);
        $this->assertContains('Logradouro (endereço) não informado.', $erros);
        $this->assertContains('Número do endereço não informado.', $erros);
        $this->assertContains('Bairro do endereço não informado.', $erros);
    }

    public function test_validar_paciente_cidade_sem_codigo_ibge_retorna_erro()
    {
        $cidadeSemIbge = Cidade::forceCreate([
            'nome' => 'Município Desconhecido',
            'uf' => 'RO',
            'codigo_ibge' => null,
        ]);

        $paciente = (new Paciente)->forceFill([
            'nome' => 'Roberto Souza',
            'cpf' => '12345678901',
            'cep' => '76960000',
            'logradouro' => 'Rua B',
            'numero' => '20',
            'bairro' => 'Jardim Climax',
            'cidade_id' => $cidadeSemIbge->id,
        ]);
        $paciente->setRelation('cidade', $cidadeSemIbge);

        $erros = $paciente->validarParaNotaFiscal();

        $this->assertContains("A cidade 'Município Desconhecido' não possui Código IBGE cadastrado.", $erros);
    }
}
