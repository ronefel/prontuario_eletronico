<?php

namespace App\Filament\Widgets;

use App\Models\ExameLaboratorial;
use App\Models\ExameParametro;
use App\Models\ExameResultadoItem;
use Filament\Widgets\ChartWidget;
use Livewire\Attributes\Reactive;

class ExameEvolucaoChartWidget extends ChartWidget
{
    protected ?string $heading = 'Evolução Temporal dos Exames';

    protected ?string $maxHeight = '350px';

    #[Reactive]
    public int|string|null $pacienteId = null;

    public int|string|null $exameId = null;

    public int|string|null $parametroId = null;

    public function mount(int|string|null $pacienteId = null): void
    {
        $this->pacienteId = $pacienteId;
        $this->inicializarFiltros();
    }

    public function updatedExameId($val): void
    {
        $firstParam = ExameParametro::where('exame_id', $val)->first();
        $this->parametroId = $firstParam?->id;
    }

    public function inicializarFiltros(): void
    {
        if (! $this->pacienteId) {
            return;
        }

        // Buscar o primeiro exame do paciente que possui resultados
        if (! $this->exameId) {
            $primeiroResultado = ExameResultadoItem::whereHas('registro', function ($q) {
                $q->where('paciente_id', $this->pacienteId);
            })->first();

            if ($primeiroResultado) {
                $this->exameId = $primeiroResultado->registro->exame_id;
                $this->parametroId = $primeiroResultado->exame_parametro_id;
            } else {
                $firstExame = ExameLaboratorial::where('ativo', true)->first();
                if ($firstExame) {
                    $this->exameId = $firstExame->id;
                    $firstParam = $firstExame->parametros()->first();
                    $this->parametroId = $firstParam?->id;
                }
            }
        }
    }

    public function getExamesProperty()
    {
        return ExameLaboratorial::where('ativo', true)->orderBy('nome')->get();
    }

    public function getParametrosProperty()
    {
        if (! $this->exameId) {
            return collect();
        }

        return ExameParametro::where('exame_id', $this->exameId)->orderBy('nome_parametro')->get();
    }

    protected function getData(): array
    {
        if (! $this->pacienteId || ! $this->parametroId) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $parametro = ExameParametro::find($this->parametroId);

        $itens = ExameResultadoItem::with('registro')
            ->whereHas('registro', function ($q) {
                $q->where('paciente_id', $this->pacienteId);
            })
            ->where('exame_parametro_id', $this->parametroId)
            ->join('exame_registros', 'exame_resultado_itens.exame_registro_id', '=', 'exame_registros.id')
            ->orderBy('exame_registros.data_exame', 'asc')
            ->select('exame_resultado_itens.*')
            ->get();

        $labels = [];
        $valores = [];
        $minimos = [];
        $maximos = [];

        $minVal = $parametro?->valor_minimo_ideal;
        $maxVal = $parametro?->valor_maximo_ideal;

        foreach ($itens as $item) {
            $dataFormatted = $item->registro->data_exame ? $item->registro->data_exame->format('d/m/Y') : '-';
            $labels[] = $dataFormatted;
            $valores[] = (float) $item->valor_resultado;

            if ($minVal !== null) {
                $minimos[] = (float) $minVal;
            }
            if ($maxVal !== null) {
                $maximos[] = (float) $maxVal;
            }
        }

        $unidade = $parametro?->unidade_medida ? " ({$parametro->unidade_medida})" : '';
        $nomeParametro = ($parametro?->nome_parametro ?? 'Resultado') . $unidade;

        $datasets = [
            [
                'label' => $nomeParametro,
                'data' => $valores,
                'borderColor' => '#0284c7',
                'backgroundColor' => 'rgba(2, 132, 199, 0.1)',
                'fill' => true,
                'tension' => 0.3,
                'pointRadius' => 5,
                'pointHoverRadius' => 7,
            ],
        ];

        if ($minVal !== null && count($minimos) > 0) {
            $datasets[] = [
                'label' => "Mínimo Ideal ({$minVal})",
                'data' => $minimos,
                'borderColor' => '#f59e0b',
                'borderDash' => [5, 5],
                'pointRadius' => 0,
                'fill' => false,
            ];
        }

        if ($maxVal !== null && count($maximos) > 0) {
            $datasets[] = [
                'label' => "Máximo Ideal ({$maxVal})",
                'data' => $maximos,
                'borderColor' => '#ef4444',
                'borderDash' => [5, 5],
                'pointRadius' => 0,
                'fill' => false,
            ];
        }

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
