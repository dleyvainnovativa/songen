{{--
    Vista: impresion/historia.blade.php — GET /pacientes/{paciente}/historia/imprimir
    Variables: $paciente, $historia, $subtipo, $cfg
--}}
@extends('layouts.print')

@section('title', 'Historia Clínica · ' . $paciente->nombre_completo)

@section('print-content')
    <h1 class="print-title">Historia Clínica</h1>

    {{-- Datos del paciente --}}
    <table class="print-kv">
        <tr>
            <td><strong>Paciente:</strong> {{ $paciente->nombre_completo }}</td>
            <td><strong>Expediente:</strong> {{ $paciente->numero_expediente }}</td>
        </tr>
        <tr>
            <td><strong>Edad:</strong> {{ $paciente->edad }} años</td>
            <td><strong>Sexo:</strong> {{ match($paciente->sexo){'M'=>'Masculino','F'=>'Femenino',default=>'Indeterminado'} }}</td>
        </tr>
        <tr>
            <td><strong>Tipo:</strong> {{ $paciente->tipo_paciente }}</td>
            <td><strong>Elaborada:</strong> {{ $historia->fecha_elaboracion?->format('d/m/Y') }}</td>
        </tr>
        @if($paciente->alergias_conocidas)
            <tr><td colspan="2" class="print-alert"><strong>Alergias:</strong> {{ $paciente->alergias_conocidas }}</td></tr>
        @endif
    </table>

    {{-- Antecedentes --}}
    <h2 class="print-section">Antecedentes</h2>
    @php
        $hf = collect([
            'Diabetes' => $historia->ant_hf_diabetes,
            'Hipertensión' => $historia->ant_hf_hipertension,
            'Cardiopatía' => $historia->ant_hf_cardiopatia,
            'Cáncer' => $historia->ant_hf_cancer,
        ])->filter()->keys()->join(', ');
    @endphp
    <p><strong>Heredofamiliares:</strong> {{ $hf ?: 'Negados' }}{{ $historia->ant_hf_otros ? '. '.$historia->ant_hf_otros : '' }}</p>
    <p><strong>No patológicos:</strong>
        Tabaquismo: {{ $historia->tabaquismo ? 'Sí'.($historia->tabaquismo_detalle?' ('.$historia->tabaquismo_detalle.')':'') : 'No' }}.
        Alcoholismo: {{ $historia->alcoholismo ? 'Sí' : 'No' }}.
        Toxicomanías: {{ $historia->toxicomanias ? 'Sí' : 'No' }}.
    </p>
    @if($historia->enfermedades_previas || $historia->cirugias_previas)
        <p><strong>Patológicos:</strong>
            {{ $historia->enfermedades_previas ? 'Enfermedades: '.$historia->enfermedades_previas.'. ' : '' }}
            {{ $historia->cirugias_previas ? 'Cirugías: '.$historia->cirugias_previas.'.' : '' }}
        </p>
    @endif

    {{-- Padecimiento --}}
    <h2 class="print-section">Padecimiento actual</h2>
    <p><strong>Motivo de consulta:</strong> {{ $historia->motivo_consulta }}</p>
    @if($historia->padecimiento_actual)
        <p>{{ $historia->padecimiento_actual }}</p>
    @endif

    {{-- Signos vitales --}}
    <h2 class="print-section">Exploración física</h2>
    <p class="print-vitals">
        @if($historia->peso_kg)Peso: {{ $historia->peso_kg }} kg · @endif
        @if($historia->talla_cm)Talla: {{ $historia->talla_cm }} cm · @endif
        @if($historia->imc)IMC: {{ $historia->imc }} · @endif
        @if($historia->presion_arterial)T/A: {{ $historia->presion_arterial }} · @endif
        @if($historia->frecuencia_cardiaca)FC: {{ $historia->frecuencia_cardiaca }} · @endif
        @if($historia->temperatura_c)Temp: {{ $historia->temperatura_c }}°C · @endif
        @if($historia->saturacion_o2)SatO₂: {{ $historia->saturacion_o2 }}%@endif
    </p>
    @if($historia->exploracion_fisica)<p>{{ $historia->exploracion_fisica }}</p>@endif

    {{-- Diagnóstico --}}
    <h2 class="print-section">Diagnóstico y plan</h2>
    <p><strong>Diagnóstico:</strong> {{ $historia->diagnostico_inicial }}</p>
    @if($historia->plan_manejo)<p><strong>Plan:</strong> {{ $historia->plan_manejo }}</p>@endif
    @if($historia->pronostico)<p><strong>Pronóstico:</strong> {{ $historia->pronostico }}</p>@endif

    {{-- Extensión de subtipo --}}
    @if($subtipo && $cfg)
        <h2 class="print-section">{{ $cfg['ui']['titulo'] }}</h2>
        <table class="print-kv">
            @foreach($cfg['campos'] as $campo => $meta)
                @php($val = $subtipo->$campo)
                @if($val !== null && $val !== '' && $meta['tipo'] !== 'bool')
                    <tr><td colspan="2"><strong>{{ $meta['label'] }}:</strong> {{ $val }}</td></tr>
                @elseif($meta['tipo'] === 'bool' && $val)
                    <tr><td colspan="2">✓ {{ $meta['label'] }}</td></tr>
                @endif
            @endforeach
        </table>
    @endif

    {{-- Firma --}}
    <div class="print-firma">
        <div class="print-firma-line">
            {{ $historia->medicoResponsable->nombre_completo ?? '' }}<br>
            <span class="small">Médico responsable
                @if($historia->medicoResponsable?->cedula_profesional)
                    · Céd. {{ $historia->medicoResponsable->cedula_profesional }}
                @endif
            </span>
        </div>
    </div>
@endsection
