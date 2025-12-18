<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TratamentosExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $records;

    protected $includeApplications;

    public function __construct(Collection $records, bool $includeApplications = false)
    {
        $this->records = $records;
        $this->includeApplications = $includeApplications;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        $headers = [
            'Paciente',
            'Data Início',
            'Status',
            'Valor Cobrado',
            'Custo Total',
            'Saldo',
        ];

        if ($this->includeApplications) {
            $headers[] = 'Aplicações (Data - Status - Itens)';
        }

        return $headers;
    }

    public function map($row): array
    {
        $data = [
            $row->paciente->nome ?? 'N/A',
            $row->data_inicio?->format('d/m/Y'),
            ucfirst($row->status),
            $row->valor_cobrado,
            $row->custo_total,
            $row->saldo,
        ];

        if ($this->includeApplications) {
            $apps = $row->aplicacoes->map(function ($app) {
                $itens = $app->lotes->map(fn ($l) => "{$l->produto->nome} ({$l->pivot->quantidade})")->join(', ');

                return "{$app->data_aplicacao?->format('d/m/Y')} - {$app->status} - [{$itens}]";
            })->join("\n");

            $data[] = $apps;
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'G' => ['alignment' => ['wrapText' => true]], // Assumindo que a coluna de aplicações é a G (7ª coluna)
        ];
    }
}
