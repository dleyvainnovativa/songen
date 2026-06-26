<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Impresión')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/theme.css'])
</head>
<body class="print-body">

    <div class="print-toolbar no-print">
        <button onclick="window.print()" class="btn-next">
            <i class="fa-solid fa-print"></i> Imprimir
        </button>
        <button onclick="window.close()" class="btn-prev">Cerrar</button>
    </div>

    <div class="print-sheet">
        {{-- Encabezado del documento --}}
        <div class="print-head">
            <img src="{{ asset('img/logo.png') }}" alt="Fisio Clínica" class="print-logo">
            <div class="print-head-meta">
                <div>Impreso: <span class="mono">{{ now()->format('d/m/Y H:i') }}</span></div>
                <div>Por: {{ auth()->user()->nombre_completo ?? '—' }}</div>
            </div>
        </div>

        @yield('print-content')

        <div class="print-foot">
            Documento generado por Fisio Clínica · Confidencial
        </div>
    </div>

</body>
</html>
