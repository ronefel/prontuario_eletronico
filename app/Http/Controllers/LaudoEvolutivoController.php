<?php

namespace App\Http\Controllers;

use App\Models\ExameLaboratorial;
use App\Models\ExameRegistro;
use App\Models\Paciente;
use App\Services\PDFGeneratorService;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class LaudoEvolutivoController extends Controller
{
    public function imprimir($pacienteId, Request $requisicao)
    {
        $paciente = Paciente::findOrFail($pacienteId);

        $formatoLayout = $requisicao->query('layout', 'P');
        $tamanhoPapel = $requisicao->query('paper_size', 'A4');

        if (! in_array($formatoLayout, ['P', 'L'])) {
            throw new HttpResponseException(response()->make(
                'O parâmetro layout deve ser P (retrato) ou L (paisagem).',
                400
            ));
        }

        if (! in_array($tamanhoPapel, ['A4', 'A5', 'A5noA4'])) {
            throw new HttpResponseException(response()->make(
                'O parâmetro paper_size deve ser A4, A5 ou A5noA4.',
                400
            ));
        }

        // Buscar todas as datas distintas com exames para este paciente, ordenadas da mais recente para a mais antiga
        $datasRegistradas = ExameRegistro::where('paciente_id', $paciente->id)
            ->whereHas('itens')
            ->orderBy('data_exame', 'desc')
            ->pluck('data_exame')
            ->map(function ($data) {
                return $data instanceof Carbon ? $data->format('Y-m-d') : Carbon::parse($data)->format('Y-m-d');
            })
            ->unique()
            ->values();

        // Se o paciente não possuir nenhum registro de exame com itens
        if ($datasRegistradas->isEmpty()) {
            throw new HttpResponseException(response()->make(
                'Nenhum exame laboratorial registrado para este paciente.',
                404
            ));
        }

        // A data mais recente é a "Atual", e no máximo 4 anteriores
        $dataAtual = $datasRegistradas->first();
        $datasAnteriores = $datasRegistradas->slice(1, 4)->values();
        $todasDatasColunas = collect([$dataAtual])->concat($datasAnteriores);

        // Buscar todos os registros do paciente nas datas selecionadas com seus itens e parâmetros
        $registros = ExameRegistro::with([
            'exame.parametros',
            'itens.parametro',
        ])
            ->where('paciente_id', $paciente->id)
            ->whereIn('data_exame', $todasDatasColunas)
            ->orderBy('data_exame', 'desc')
            ->get();

        // Agrupar os itens por exame_parametro_id e por data_exame para busca rápida
        $resultadosPorParametroEData = [];
        foreach ($registros as $registro) {
            $dataChave = $registro->data_exame instanceof Carbon
                ? $registro->data_exame->format('Y-m-d')
                : Carbon::parse($registro->data_exame)->format('Y-m-d');

            foreach ($registro->itens as $item) {
                $parametroId = $item->exame_parametro_id;
                // Guarda o item mais recente se houver duplicidade na mesma data
                if (! isset($resultadosPorParametroEData[$parametroId][$dataChave])) {
                    $resultadosPorParametroEData[$parametroId][$dataChave] = $item;
                }
            }
        }

        // Buscar todos os exames envolvidos que possuem medições nessas datas
        $examesIds = $registros->pluck('exame_id')->unique()->filter()->values();
        $exames = ExameLaboratorial::with(['parametros' => function ($consulta) {
            $consulta->orderBy('id', 'asc');
        }])
            ->whereIn('id', $examesIds)
            ->orderBy('nome', 'asc')
            ->get();

        // Estruturar dados agrupados por exame e seus parâmetros
        $dadosExames = [];

        foreach ($exames as $exame) {
            $parametrosDados = [];

            foreach ($exame->parametros as $parametro) {
                // Verificar se este parâmetro tem resultado em pelo menos uma das datas
                if (! isset($resultadosPorParametroEData[$parametro->id])) {
                    continue;
                }

                // Montar a faixa de referência formatada
                $unidade = $parametro->unidade_medida ? ' '.$parametro->unidade_medida : '';
                $min = $parametro->valor_minimo_ideal;
                $max = $parametro->valor_maximo_ideal;

                $faixaReferencia = '-';
                if ($min !== null && $max !== null) {
                    $faixaReferencia = "{$min} a {$max}{$unidade}";
                } elseif ($min !== null) {
                    $faixaReferencia = ">= {$min}{$unidade}";
                } elseif ($max !== null) {
                    $faixaReferencia = "Até {$max}{$unidade}";
                }

                // Obter valor e status da data Atual
                $itemAtual = $resultadosPorParametroEData[$parametro->id][$dataAtual] ?? null;
                $resultadoAtual = [
                    'valor' => $itemAtual ? $this->formatarValorResultado($itemAtual->valor_resultado) : '*',
                    'alterado' => $itemAtual ? in_array($itemAtual->status_normalidade, ['baixo', 'alto']) : false,
                ];

                // Obter valores e status para as datas Anteriores (até 4)
                $resultadosAnteriores = [];
                foreach ($datasAnteriores as $dataAnterior) {
                    $itemAnterior = $resultadosPorParametroEData[$parametro->id][$dataAnterior] ?? null;
                    $resultadosAnteriores[] = [
                        'data' => $dataAnterior,
                        'valor' => $itemAnterior ? $this->formatarValorResultado($itemAnterior->valor_resultado) : '*',
                        'alterado' => $itemAnterior ? in_array($itemAnterior->status_normalidade, ['baixo', 'alto']) : false,
                    ];
                }

                $parametrosDados[] = [
                    'nome' => $parametro->nome_parametro,
                    'faixa_referencia' => $faixaReferencia,
                    'atual' => $resultadoAtual,
                    'anteriores' => $resultadosAnteriores,
                ];
            }

            if (! empty($parametrosDados)) {
                $dadosExames[] = [
                    'nome' => $exame->nome,
                    'parametros' => $parametrosDados,
                ];
            }
        }

        $dataAtendimento = Carbon::parse($dataAtual);

        $geradorPdf = new PDFGeneratorService(
            $tamanhoPapel,
            $formatoLayout,
            $paciente,
            $dataAtendimento
        );

        $conteudoHtml = view('pdf.laudo-evolutivo', [
            'paciente' => $paciente,
            'dataAtualFormatada' => Carbon::parse($dataAtual)->format('d/m/Y'),
            'datasAnterioresFormatadas' => $datasAnteriores->map(fn ($d) => Carbon::parse($d)->format('d/m/Y')),
            'dadosExames' => $dadosExames,
        ])->render();

        $mpdf = $geradorPdf->generatePDF($conteudoHtml);

        $nomeArquivo = str_replace(' ', '_', $paciente->nome).'_Laudo_Evolutivo_'.time().'.pdf';

        $conteudoPdf = $mpdf->Output($nomeArquivo, 'S');

        return response($conteudoPdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$nomeArquivo.'"',
        ]);
    }

    private function formatarValorResultado(?float $valor): string
    {
        if ($valor === null) {
            return '*';
        }

        // Se for um número inteiro, não deve usar casas decimais
        if (round($valor, 4) == round($valor, 0)) {
            return number_format($valor, 0, ',', '.');
        }

        // Formata com até 2 casas decimais e remove zeros desnecessários à direita (ex.: 45,5 em vez de 45,50)
        $formatado = number_format($valor, 2, ',', '.');

        return rtrim(rtrim($formatado, '0'), ',');
    }
}
