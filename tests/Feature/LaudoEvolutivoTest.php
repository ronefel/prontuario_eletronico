<?php

use App\Filament\Resources\Pacientes\Pages\ExamesPaciente;
use App\Http\Controllers\LaudoEvolutivoController;
use App\Models\ExameLaboratorial;
use App\Models\ExameParametro;
use App\Models\ExameRegistro;
use App\Models\ExameResultadoItem;
use App\Models\Paciente;
use App\Models\User;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(SettingsSeeder::class);
});

test('rota do laudo evolutivo requer autenticacao', function () {
    $paciente = Paciente::create([
        'nome' => 'Paciente Teste Auth',
        'cpf' => '11122233344',
        'nascimento' => '1990-01-01',
        'sexo' => 'M',
    ]);

    $response = $this->get(route('laudo-evolutivo.print', ['pacienteId' => $paciente->id]));
    $response->assertRedirect(route('login'));
});

test('retorna 404 quando paciente nao possui exames registrados', function () {
    $usuario = User::factory()->create();
    $paciente = Paciente::create([
        'nome' => 'Paciente Sem Exames',
        'cpf' => '22233344455',
        'nascimento' => '1992-02-02',
        'sexo' => 'F',
    ]);

    $response = $this->actingAs($usuario)->get(route('laudo-evolutivo.print', ['pacienteId' => $paciente->id]));
    $response->assertStatus(404);
});

test('retorna 400 para parametros de layout ou paper_size invalidos', function () {
    $usuario = User::factory()->create();
    $paciente = Paciente::create([
        'nome' => 'Paciente Param Invalido',
        'cpf' => '33344455566',
        'nascimento' => '1988-03-03',
        'sexo' => 'M',
    ]);

    $response = $this->actingAs($usuario)->get(route('laudo-evolutivo.print', [
        'pacienteId' => $paciente->id,
        'layout' => 'INVALIDO',
    ]));
    $response->assertStatus(400);

    $responsePaper = $this->actingAs($usuario)->get(route('laudo-evolutivo.print', [
        'pacienteId' => $paciente->id,
        'paper_size' => 'INVALIDO',
    ]));
    $responsePaper->assertStatus(400);
});

test('gera laudo evolutivo em pdf com sucesso para paciente com exames em multiplas datas', function () {
    $usuario = User::factory()->create();

    $paciente = Paciente::create([
        'nome' => 'Paciente Evolucao Teste',
        'cpf' => '44455566677',
        'nascimento' => '1985-04-04',
        'sexo' => 'M',
    ]);

    $exame = ExameLaboratorial::create([
        'nome' => 'Hemograma Completo',
        'ativo' => true,
    ]);

    $parametro = ExameParametro::create([
        'exame_id' => $exame->id,
        'nome_parametro' => 'Hemoglobina',
        'unidade_medida' => 'g/dL',
        'valor_minimo_ideal' => 12.0,
        'valor_maximo_ideal' => 17.5,
    ]);

    // Registro anterior
    $registroAnterior = ExameRegistro::create([
        'paciente_id' => $paciente->id,
        'exame_id' => $exame->id,
        'data_exame' => '2026-08-01',
    ]);
    ExameResultadoItem::create([
        'exame_registro_id' => $registroAnterior->id,
        'exame_parametro_id' => $parametro->id,
        'valor_resultado' => 11.5, // Baixo (fora da faixa)
    ]);

    // Registro atual
    $registroAtual = ExameRegistro::create([
        'paciente_id' => $paciente->id,
        'exame_id' => $exame->id,
        'data_exame' => '2026-09-01',
    ]);
    ExameResultadoItem::create([
        'exame_registro_id' => $registroAtual->id,
        'exame_parametro_id' => $parametro->id,
        'valor_resultado' => 13.8, // Normal
    ]);

    $response = $this->actingAs($usuario)->get(route('laudo-evolutivo.print', [
        'pacienteId' => $paciente->id,
        'layout' => 'P',
        'paper_size' => 'A4',
    ]));

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});

test('componente ExamesPaciente possui action imprimirLaudoEvolutivo', function () {
    $paciente = Paciente::create([
        'nome' => 'Paciente Action Teste',
        'cpf' => '55566677788',
        'nascimento' => '1995-05-05',
        'sexo' => 'F',
    ]);

    Livewire::test(ExamesPaciente::class, ['record' => $paciente->id])
        ->assertActionExists('imprimirLaudoEvolutivo');
});

test('nao usa casas decimais quando o resultado for numero inteiro', function () {
    $controlador = new LaudoEvolutivoController;
    $metodo = new ReflectionMethod($controlador, 'formatarValorResultado');
    $metodo->setAccessible(true);

    expect($metodo->invoke($controlador, 5.0))->toBe('5');
    expect($metodo->invoke($controlador, 41.0))->toBe('41');
    expect($metodo->invoke($controlador, 120.0))->toBe('120');
    expect($metodo->invoke($controlador, 300000.0))->toBe('300.000');
    expect($metodo->invoke($controlador, 45.5))->toBe('45,5');
    expect($metodo->invoke($controlador, 4.52))->toBe('4,52');
    expect($metodo->invoke($controlador, 13.8))->toBe('13,8');
    expect($metodo->invoke($controlador, null))->toBe('*');
});
