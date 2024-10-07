<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{{ str_replace(' ', '_', $exame->paciente->nome) . ' _ ' . time() . '.pdf' }}</title>
</head>

<body>
    <div class="document-content">
        {!! $settings[App\Models\Setting::BIORRESSONANCIA_TEXTO_INICIAL] !!}
        <div style="font-size: 12pt">
            @foreach ($categorias as $key => $categoria)
                <p style="text-align: justify; margin-bottom: 0.5cm; text-indent: 40px;">
                    <strong><u>{{ $categoria['nome'] }}</u>:</strong>
                    {{ ucfirst(strtolower(implode(', ', array_column($categoria['testadores'], 'nome')))) }}.
                </p>
                @if ($categoria['nota'])
                    <div style="text-align: justify; margin-bottom: 0.5cm; text-indent: 40px; font-size: 10pt;">
                        {!! $categoria['nota'] !!}
                    </div>
                @endif
            @endforeach
        </div>
        {!! $settings[App\Models\Setting::BIORRESSONANCIA_TEXTO_FINAL] !!}
    </div>
</body>

</html>
