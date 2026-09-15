<?php

use App\Filament\Resources\Pacientes\Pages\ExamesPaciente;
use App\Models\ExameLaboratorial;
use App\Models\ExameParametro;
use App\Models\ExameRegistro;
use App\Models\ExameResultadoItem;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('pode criar um exame laboratorial com parametros', function () {
    $exame = ExameLaboratorial::create([
        'nome' => 'Hemograma Completo',
        'descricao' => 'Exame de sangue completo',
        'ativo' => true,
    ]);

    $parametro = ExameParametro::create([
        'exame_id' => $exame->id,
        'nome_parametro' => 'Hemoglobina',
        'unidade_medida' => 'g/dL',
        'valor_minimo_ideal' => 12.00,
        'valor_maximo_ideal' => 17.50,
    ]);

    expect($exame->parametros)->toHaveCount(1);
    expect($parametro->exame->id)->toBe($exame->id);
});

test('calcula status de normalidade corretamente', function () {
    $paciente = Paciente::create([
        'nome' => 'Paciente Teste',
        'cpf' => '12345678900',
        'nascimento' => '1990-01-01',
        'sexo' => 'M',
    ]);

    $exame = ExameLaboratorial::create([
        'nome' => 'Glicemia de Jejum',
        'ativo' => true,
    ]);

    $param = ExameParametro::create([
        'exame_id' => $exame->id,
        'nome_parametro' => 'Resultado',
        'unidade_medida' => 'mg/dL',
        'valor_minimo_ideal' => 70.00,
        'valor_maximo_ideal' => 99.00,
    ]);

    $registro = ExameRegistro::create([
        'paciente_id' => $paciente->id,
        'exame_id' => $exame->id,
        'data_exame' => now()->toDateString(),
    ]);

    // Teste Normal
    $itemNormal = ExameResultadoItem::create([
        'exame_registro_id' => $registro->id,
        'exame_parametro_id' => $param->id,
        'valor_resultado' => 85.00,
    ]);
    expect($itemNormal->status_normalidade)->toBe('normal');

    // Teste Baixo
    $itemBaixo = ExameResultadoItem::create([
        'exame_registro_id' => $registro->id,
        'exame_parametro_id' => $param->id,
        'valor_resultado' => 60.00,
    ]);
    expect($itemBaixo->status_normalidade)->toBe('baixo');

    // Teste Alto
    $itemAlto = ExameResultadoItem::create([
        'exame_registro_id' => $registro->id,
        'exame_parametro_id' => $param->id,
        'valor_resultado' => 110.00,
    ]);
    expect($itemAlto->status_normalidade)->toBe('alto');
});

test('calcula resumo de parametros e tendencia percentual em ExamesPaciente', function () {
    $paciente = Paciente::create([
        'nome' => 'Paciente Tendência',
        'cpf' => '98765432100',
        'nascimento' => '1985-05-15',
        'sexo' => 'F',
    ]);

    $exame = ExameLaboratorial::create([
        'nome' => 'Glicemia de Jejum',
        'ativo' => true,
    ]);

    $param = ExameParametro::create([
        'exame_id' => $exame->id,
        'nome_parametro' => 'Resultado',
        'unidade_medida' => 'mg/dL',
        'valor_minimo_ideal' => 70.00,
        'valor_maximo_ideal' => 99.00,
    ]);

    // Penúltima coleta (80 mg/dL)
    $reg1 = ExameRegistro::create([
        'paciente_id' => $paciente->id,
        'exame_id' => $exame->id,
        'data_exame' => now()->subDays(30)->toDateString(),
    ]);
    ExameResultadoItem::create([
        'exame_registro_id' => $reg1->id,
        'exame_parametro_id' => $param->id,
        'valor_resultado' => 80.00,
    ]);

    // Última coleta (95 mg/dL - Alta de 19%)
    $reg2 = ExameRegistro::create([
        'paciente_id' => $paciente->id,
        'exame_id' => $exame->id,
        'data_exame' => now()->toDateString(),
    ]);
    ExameResultadoItem::create([
        'exame_registro_id' => $reg2->id,
        'exame_parametro_id' => $param->id,
        'valor_resultado' => 95.00,
    ]);

    $component = Livewire::test(ExamesPaciente::class, ['record' => $paciente->id]);

    $resumo = $component->get('resumoParametros');
    expect($resumo)->toHaveCount(1);

    $itemResumo = $resumo->first();
    expect($itemResumo->ultimo_valor)->toBe(95.0);
    expect($itemResumo->penultimo_valor)->toBe(80.0);
    expect($itemResumo->status_penultimo)->toBe('normal');
    expect($itemResumo->is_exame_simples)->toBeTrue();
});

test('formata valores numericos em ExamesPaciente sem casas decimais para inteiros e sem zeros a direita', function () {
    $component = new ExamesPaciente;

    expect($component->formatarValorResultado(45.5))->toBe('45,5');
    expect($component->formatarValorResultado(45.52))->toBe('45,52');
    expect($component->formatarValorResultado(120.0))->toBe('120');
    expect($component->formatarValorResultado(300000.0))->toBe('300.000');
    expect($component->formatarValorResultado(5.0))->toBe('5');
    expect($component->formatarValorResultado(null))->toBe('-');
});

test('resumoParametros ordena por nome do exame e id do parametro', function () {
    $paciente = Paciente::create([
        'nome' => 'Paciente Ordem Teste',
        'cpf' => '66677788899',
        'nascimento' => '1990-01-01',
        'sexo' => 'M',
    ]);

    $exameB = ExameLaboratorial::create(['nome' => 'Perfil Lipídico', 'ativo' => true]);
    $paramB1 = ExameParametro::create(['exame_id' => $exameB->id, 'nome_parametro' => 'Colesterol Total']);
    $paramB2 = ExameParametro::create(['exame_id' => $exameB->id, 'nome_parametro' => 'Triglicerídeos']);

    $exameA = ExameLaboratorial::create(['nome' => 'Hemograma Completo', 'ativo' => true]);
    $paramA1 = ExameParametro::create(['exame_id' => $exameA->id, 'nome_parametro' => 'Hemoglobina']);
    $paramA2 = ExameParametro::create(['exame_id' => $exameA->id, 'nome_parametro' => 'Hematócrito']);

    $reg = ExameRegistro::create(['paciente_id' => $paciente->id, 'exame_id' => $exameA->id, 'data_exame' => now()->toDateString()]);
    ExameResultadoItem::create(['exame_registro_id' => $reg->id, 'exame_parametro_id' => $paramA1->id, 'valor_resultado' => 14]);
    ExameResultadoItem::create(['exame_registro_id' => $reg->id, 'exame_parametro_id' => $paramA2->id, 'valor_resultado' => 42]);

    $regB = ExameRegistro::create(['paciente_id' => $paciente->id, 'exame_id' => $exameB->id, 'data_exame' => now()->toDateString()]);
    ExameResultadoItem::create(['exame_registro_id' => $regB->id, 'exame_parametro_id' => $paramB1->id, 'valor_resultado' => 180]);
    ExameResultadoItem::create(['exame_registro_id' => $regB->id, 'exame_parametro_id' => $paramB2->id, 'valor_resultado' => 120]);

    $component = Livewire::test(ExamesPaciente::class, ['record' => $paciente->id]);
    $resumo = $component->get('resumoParametros');

    expect($resumo->pluck('exame_nome')->toArray())->toBe([
        'Hemograma Completo',
        'Hemograma Completo',
        'Perfil Lipídico',
        'Perfil Lipídico',
    ]);

    expect($resumo->pluck('parametro_id')->toArray())->toBe([
        $paramA1->id,
        $paramA2->id,
        $paramB1->id,
        $paramB2->id,
    ]);
});
