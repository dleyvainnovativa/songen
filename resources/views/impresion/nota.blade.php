{{--
    Vista: impresion/nota.blade.php — GET /pacientes/{paciente}/notas/{nota}/imprimir
    Variables: $paciente, $nota
--}}
@extends('layouts.print')

@section('title', 'Nota médica · ' . $paciente->nombre_completo)

@section('print-content')
    <h1 class="print-title">{{ $nota->tipoNota->descripcion ?? 'Nota Médica' }}</h1>

    <table class="print-kv">
        <tr>
            <td><strong>Paciente:</strong> {{ $paciente->nombre_completo }}</td>
            <td><strong>Expediente:</strong> {{ $paciente->numero_expediente }}</td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong> {{ $nota->fecha_hora?->format('d/m/Y H:i') }}</td>
            <td><strong>Médico:</strong> {{ $nota->medico->nombre_completo ?? '—' }}</td>
        </tr>
    </table>

    {{-- SOAP --}}
    @foreach([
        'subjetivo' => 'Subjetivo (S)',
        'objetivo'  => 'Objetivo (O)',
        'analisis'  => 'Análisis (A)',
        'plan'      => 'Plan (P)',
    ] as $campo => $titulo)
        @if($nota->$campo)
            <h2 class="print-section">{{ $titulo }}</h2>
            <p>{!! nl2br(e($nota->$campo)) !!}</p>
        @endif
    @endforeach

    {{-- Signos vitales --}}
    @php
        $sv = collect([
            'T/A' => $nota->presion_arterial, 'FC' => $nota->frecuencia_cardiaca,
            'FR' => $nota->frecuencia_respiratoria, 'Temp' => $nota->temperatura_c,
            'SatO₂' => $nota->saturacion_o2, 'Peso' => $nota->peso_kg,
        ])->filter(fn($v) => $v !== null && $v !== '');
    @endphp
    @if($sv->isNotEmpty())
        <h2 class="print-section">Signos vitales</h2>
        <p class="print-vitals">
            @foreach($sv as $l => $v){{ $l }}: {{ $v }}{{ !$loop->last ? ' · ' : '' }}@endforeach
        </p>
    @endif

    {{-- Datos quirúrgicos --}}
    @if($nota->notaQuirurgica)
        @php($q = $nota->notaQuirurgica)
        <h2 class="print-section">Datos quirúrgicos</h2>
        <table class="print-kv">
            @foreach([
                'Cirujano' => $q->cirujano?->nombre_completo,
                'Anestesiólogo' => $q->anestesiologo?->nombre_completo,
                'Procedimiento' => $q->procedimiento_realizado,
                'Hallazgos' => $q->hallazgos,
                'Complicaciones' => $q->complicaciones,
                'Estado al egreso' => $q->estado_egreso,
            ] as $l => $v)
                @if($v)<tr><td colspan="2"><strong>{{ $l }}:</strong> {{ $v }}</td></tr>@endif
            @endforeach
        </table>
    @endif

    {{-- Firma --}}
    <div class="print-firma">
        <div class="print-firma-line">
            {{ $nota->medico->nombre_completo ?? '' }}<br>
            <span class="small">
                @if($nota->firmada)
                    Firmada el {{ $nota->fecha_firma?->format('d/m/Y H:i') }}
                @else
                    Nota en borrador (sin firmar)
                @endif
            </span>
        </div>
    </div>
@endsection
