<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Relatório de Tratamentos</h2>
    </div>

    @php
        $groupedRecords = $records->groupBy(fn($r) => $r->paciente->nome ?? 'Sem Paciente');
        $totalCobrado = $records->sum('valor_cobrado');
        $totalCusto = $records->sum(fn($r) => $r->custo_total);
        $totalSaldo = $records->sum(fn($r) => $r->saldo);
    @endphp

    <table>
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Data Início</th>
                <th>Status</th>
                @if ($includeApplications)
                    <th>Aplicações</th>
                @endif
                <th>Cobrado</th>
                <th>Custo</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($groupedRecords as $pacienteName => $group)
                <tr style="background-color: #e6e6e6;">
                    <td colspan="{{ $includeApplications ? 7 : 6 }}">
                        <strong>{{ $pacienteName }}</strong>
                    </td>
                </tr>
                @foreach ($group as $record)
                    <tr>
                        <td>{{ $record->paciente->nome ?? 'N/A' }}</td>
                        <td>{{ $record->data_inicio?->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($record->status) }}</td>
                        @if ($includeApplications)
                            <td style="font-size: 10px;">
                                @forelse($record->aplicacoes as $app)
                                    <div style="margin-bottom: 4px;">
                                        <strong>{{ $app->data_aplicacao?->format('d/m/Y') }}</strong>
                                        ({{ ucfirst($app->status) }})
                                        <br>
                                        <span style="color: #666;">
                                            {{ $app->lotes->map(fn($l) => $l->produto->nome . ' (' . $l->pivot->quantidade . ')')->join(', ') }}
                                        </span>
                                    </div>
                                @empty
                                    <span style="color: #999;">Nenhuma</span>
                                @endforelse
                            </td>
                        @endif
                        <td>R$ {{ number_format($record->valor_cobrado, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($record->custo_total, 2, ',', '.') }}</td>
                        <td>R$ {{ number_format($record->saldo, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
                @php
                    $groupTotalCobrado = $group->sum('valor_cobrado');
                    $groupTotalCusto = $group->sum(fn($r) => $r->custo_total);
                    $groupTotalSaldo = $group->sum(fn($r) => $r->saldo);
                @endphp
                <tr style="background-color: #f9f9f9; font-size: 11px;">
                    <td colspan="{{ $includeApplications ? 4 : 3 }}"
                        style="text-align: right; border-top: 1px solid #ccc;"><b><i>Subtotal
                                {{ $pacienteName }}:</i></b>
                    </td>
                    <td style="border-top: 1px solid #ccc;"><b><i>R$
                                {{ number_format($groupTotalCobrado, 2, ',', '.') }}</i></b></td>
                    <td style="border-top: 1px solid #ccc;"><b><i>R$
                                {{ number_format($groupTotalCusto, 2, ',', '.') }}</i></b></td>
                    <td style="border-top: 1px solid #ccc;"><b><i>R$
                                {{ number_format($groupTotalSaldo, 2, ',', '.') }}</i></b></td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #f2f2f2;">
                <td colspan="{{ $includeApplications ? 4 : 3 }}" style="text-align: right;"><b>TOTAL GERAL:</b></td>
                <td><b>R$ {{ number_format($totalCobrado, 2, ',', '.') }}</b></td>
                <td><b>R$ {{ number_format($totalCusto, 2, ',', '.') }}</b></td>
                <td><b>R$ {{ number_format($totalSaldo, 2, ',', '.') }}</b></td>
            </tr>
        </tfoot>
    </table>
</body>

</html>
