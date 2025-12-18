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
        <p>Gerado em: {{ date('d/m/Y H:i') }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Paciente</th>
                <th>Data Início</th>
                <th>Status</th>
                <th>Cobrado</th>
                <th>Custo</th>
                <th>Saldo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record->paciente->nome ?? 'N/A' }}</td>
                    <td>{{ $record->data_inicio?->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($record->status) }}</td>
                    <td>R$ {{ number_format($record->valor_cobrado, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($record->custo_total, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($record->saldo, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
