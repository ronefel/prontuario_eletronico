<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ str_replace(' ', '_', $prontuario->paciente->nome) . ' _ ' . time() . '.pdf' }}</title>
</head>

<body>
    <div class="document-content">
        {!! $prontuario->descricao !!}
    </div>
</body>

</html>
