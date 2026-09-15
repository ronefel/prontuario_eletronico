<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ str_replace(' ', '_', $paciente->nome) . '_Laudo_Evolutivo_' . time() . '.pdf' }}</title>
    <style>
        .titulo-laudo {
            font-size: 16pt;
            font-weight: bold;
            color: #111827;
            margin-top: 0;
            margin-bottom: 12px;
            font-family: 'roboto', sans-serif;
            text-align: center;
        }

        .tabela-laudo {
            width: 100%;
            border-collapse: collapse;
            font-family: 'roboto', sans-serif;
            font-size: 9pt;
            table-layout: fixed;
        }

        .tabela-laudo th,
        .tabela-laudo td {
            border: 1px solid #9ca3af;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .tabela-laudo th {
            background-color: #e5e7eb;
            color: #1f2937;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }

        .tabela-laudo th.coluna-exame-header {
            text-align: left;
            padding-left: 8px;
        }

        .grupo-exame {
            background-color: #e5e7eb;
            font-weight: bold;
            text-transform: uppercase;
            color: #111827;
            text-align: left;
            padding: 4px 8px;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }

        .parametro-nome {
            text-align: left;
            padding-left: 10px;
            color: #1f2937;
            white-space: nowrap;
        }

        .coluna-resultado {
            text-align: center;
            color: #1f2937;
        }

        .tabela-laudo th.coluna-atual,
        .tabela-laudo td.coluna-atual {
            font-weight: bold;
            border-left: 2px solid #7c828d;
            border-right: 2px solid #7c828d;
        }

        .resultado-alterado {
            color: #dc2626 !important;
            font-weight: bold;
        }

        .coluna-referencia {
            text-align: center;
            color: #1f2937;
            font-weight: 500;
            font-size: 8.5pt;
        }
    </style>
</head>

<body>
    <div class="document-content">
        <div class="titulo-laudo">Laudo Evolutivo</div>

        <table class="tabela-laudo">
            <thead>
                @php $qtdAnteriores = $datasAnterioresFormatadas->count();
                @endphp
                <tr>
                    <th rowspan="2" class="coluna-exame-header">
                        Data do Exame
                    </th>
                    <th class="coluna-atual" style="width: 10%;">
                        Atual
                    </th>
                    @if ($qtdAnteriores > 0)
                        <th colspan="{{ $qtdAnteriores }}">
                            Resultados Anteriores
                        </th>
                    @endif
                    <th rowspan="2" style="width: 22%;">
                        Valores de Referência
                    </th>
                </tr>
                <tr>
                    <th class="coluna-atual">
                        {{ $dataAtualFormatada }}
                    </th>
                    @foreach ($datasAnterioresFormatadas as $dataAnterior)
                        <th style="width: 10%;">
                            {{ $dataAnterior }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($dadosExames as $exame)
                    <tr>
                        <td colspan="{{ 2 + $qtdAnteriores + 1 }}" class="grupo-exame">
                            {{ mb_strtoupper($exame['nome']) }}
                        </td>
                    </tr>
                    @foreach ($exame['parametros'] as $parametro)
                        <tr>
                            <td class="parametro-nome">
                                - {{ $parametro['nome'] }}
                            </td>
                            <td
                                class="coluna-resultado coluna-atual {{ $parametro['atual']['alterado'] ? 'resultado-alterado' : '' }}">
                                {{ $parametro['atual']['valor'] }}
                            </td>
                            @foreach ($parametro['anteriores'] as $anterior)
                                <td class="coluna-resultado {{ $anterior['alterado'] ? 'resultado-alterado' : '' }}">
                                    {{ $anterior['valor'] }}
                                </td>
                            @endforeach <td class="coluna-referencia">
                                {{ $parametro['faixa_referencia'] }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>