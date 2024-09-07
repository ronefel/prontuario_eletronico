<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Impressão de Prontuário</title>
    <style>
        /* Estilos específicos para impressão */
        @media print {

            /* Estilos para impressão */
            body {
                font-family: Arial, sans-serif;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="document-content">
        {!! $prontuario->descricao !!}
    </div>
</body>

</html>
