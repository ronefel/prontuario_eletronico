<?php

namespace Database\Seeders;

use App\Models\ExameLaboratorial;
use App\Models\ExameParametro;
use Illuminate\Database\Seeder;

class ExameLaboratorialSeeder extends Seeder
{
    public function run(): void
    {
        $exames = [
            [
                'nome' => 'Hemograma Completo',
                'descricao' => 'Avaliação das células sanguíneas (série vermelha, branca e plaquetas).',
                'ativo' => true,
                'parametros' => [
                    ['nome_parametro' => 'Hemoglobina', 'unidade_medida' => 'g/dL', 'valor_minimo_ideal' => 12.00, 'valor_maximo_ideal' => 17.50],
                    ['nome_parametro' => 'Hematócrito', 'unidade_medida' => '%', 'valor_minimo_ideal' => 36.00, 'valor_maximo_ideal' => 53.00],
                    ['nome_parametro' => 'Leucócitos Total', 'unidade_medida' => '/mm³', 'valor_minimo_ideal' => 4000.00, 'valor_maximo_ideal' => 11000.00],
                    ['nome_parametro' => 'Plaquetas', 'unidade_medida' => '/mm³', 'valor_minimo_ideal' => 150000.00, 'valor_maximo_ideal' => 450000.00],
                ],
            ],
            [
                'nome' => 'Perfil Lipídico',
                'descricao' => 'Painel de dosagem dos lipídios plasmáticos.',
                'ativo' => true,
                'parametros' => [
                    ['nome_parametro' => 'Colesterol Total', 'unidade_medida' => 'mg/dL', 'valor_minimo_ideal' => null, 'valor_maximo_ideal' => 190.00],
                    ['nome_parametro' => 'Colesterol HDL', 'unidade_medida' => 'mg/dL', 'valor_minimo_ideal' => 40.00, 'valor_maximo_ideal' => null],
                    ['nome_parametro' => 'Colesterol LDL', 'unidade_medida' => 'mg/dL', 'valor_minimo_ideal' => null, 'valor_maximo_ideal' => 130.00],
                    ['nome_parametro' => 'Triglicerídeos', 'unidade_medida' => 'mg/dL', 'valor_minimo_ideal' => null, 'valor_maximo_ideal' => 150.00],
                ],
            ],
            [
                'nome' => 'Glicemia de Jejum',
                'descricao' => 'Dosagem da glicose no sangue em jejum.',
                'ativo' => true,
                'parametros' => [
                    ['nome_parametro' => 'Resultado', 'unidade_medida' => 'mg/dL', 'valor_minimo_ideal' => 70.00, 'valor_maximo_ideal' => 99.00],
                ],
            ],
            [
                'nome' => 'TSH Basal',
                'descricao' => 'Hormônio Estimulante da Tireoide.',
                'ativo' => true,
                'parametros' => [
                    ['nome_parametro' => 'Resultado', 'unidade_medida' => 'mUI/L', 'valor_minimo_ideal' => 0.40, 'valor_maximo_ideal' => 4.50],
                ],
            ],
            [
                'nome' => 'Vitamina D 25-Hidroxi',
                'descricao' => 'Avaliação dos níveis corporais de Vitamina D.',
                'ativo' => true,
                'parametros' => [
                    ['nome_parametro' => 'Resultado', 'unidade_medida' => 'ng/mL', 'valor_minimo_ideal' => 30.00, 'valor_maximo_ideal' => 100.00],
                ],
            ],
        ];

        foreach ($exames as $dadosExame) {
            $parametros = $dadosExame['parametros'];
            unset($dadosExame['parametros']);

            $exame = ExameLaboratorial::updateOrCreate(
                ['nome' => $dadosExame['nome']],
                $dadosExame
            );

            foreach ($parametros as $param) {
                ExameParametro::updateOrCreate(
                    [
                        'exame_id' => $exame->id,
                        'nome_parametro' => $param['nome_parametro'],
                    ],
                    $param
                );
            }
        }
    }
}
