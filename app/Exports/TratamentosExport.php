<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TratamentosExport implements FromCollection, WithHeadings, WithMapping
{
    protected $records;

    public function __construct(Collection $records)
    {
        $this->records = $records;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'Paciente',
            'Data Início',
            'Status',
            'Valor Cobrado',
            'Custo Total',
            'Saldo',
        ];
    }

    public function map($row): array
    {
        return [
            $row->paciente->nome ?? 'N/A',
            $row->data_inicio?->format('d/m/Y'),
            ucfirst($row->status),
            $row->valor_cobrado,
            $row->custo_total,
            $row->saldo,
        ];
    }
}
