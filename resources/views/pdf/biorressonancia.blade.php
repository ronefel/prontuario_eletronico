<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ str_replace(' ', '_', $exame->paciente->nome) . ' _ ' . time() . '.pdf' }}</title>
</head>

<body>
    <div class="document-content">
        {!! $settings[App\Models\Setting::BIORRESSONANCIA_TEXTO_INICIAL] !!}
        teste
        {!! $settings[App\Models\Setting::BIORRESSONANCIA_TEXTO_FINAL] !!}
    </div>
</body>

</html>
