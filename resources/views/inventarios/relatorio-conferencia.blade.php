<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Conferência de Inventário #{{ $inventario->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            background: #333333;
        }

        .container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 10mm;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .info {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }

        th {
            font-weight: bold;
        }

        .col-produto {
            width: 35%;
        }

        .col-vencimento {
            width: 12%;
        }

        .col-registrada {
            width: 10%;
        }

        .col-contagem {
            width: 15%;
        }

        .col-motivo {
            width: 28%;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                margin: 0;
                padding: 0;
            }

            .container {
                padding: 0;
                margin: 0;
                width: unset;
                min-height: unset;
                box-shadow: unset
            }
        }

        .btn-print {
            background: #22c55e;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Relatório de Conferência de Inventário</h1>
        </div>

        <div class="info">
            <div>
                <strong>Inventário ID:</strong> #{{ $inventario->id }}<br>
                <strong>Tipo:</strong> {{ ucfirst($inventario->tipo) }}<br>
                <strong>Data do Inventário:</strong> {{ $inventario->data_inventario->format('d/m/Y') }}
            </div>
            <div style="text-align: right;">
                <strong>Usuário:</strong> {{ $inventario->user->name ?? 'N/A' }}<br>
                <strong>Impresso em:</strong> {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="col-produto">Produto / Lote</th>
                    <th class="col-vencimento">Vencimento</th>
                    <th class="col-registrada" style="text-align: center;">Qtd Atual</th>
                    <th class="col-contagem" style="text-align: center;">Qtd Contada</th>
                    <th class="col-motivo" style="text-align: center;">Motivo Diferença</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itens as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->lote->produto->nome }}</strong><br>
                            <small>Lote: {{ $item->lote->numero_lote }}</small>
                        </td>
                        <td>{{ $item->lote->data_validade?->format('d/m/Y') ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->quantidade_registrada }}</td>
                        <td style="text-align: center;"></td>
                        <td>
                            @php
                                $diferenca = ($item->quantidade_contada ?? 0) - $item->quantidade_registrada;
                            @endphp
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div style="margin-top: 50px;">
        <div style="display: flex; justify-content: space-around;">
            <div style="text-align: center; width: 40%;">
                <div style="border-top: 1px solid #333; padding-top: 5px;">
                    Assinatura do Responsável
                </div>
            </div>
            <div style="text-align: center; width: 40%;">
                <div style="border-top: 1px solid #333; padding-top: 5px;">
                    Data e Hora da Finalização
                </div>
            </div>
        </div>
    </div> --}}
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>
