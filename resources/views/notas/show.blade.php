{{--
    Vista: notas/show.blade.php
    Ruta:  GET /pacientes/{paciente}/notas/{nota}

    Vista de solo lectura de una nota. Si está sin firmar, ofrece firmar/editar;
    si está firmada, muestra el sello y bloquea la edición.

    Variables: $paciente, $nota (con relaciones cargadas)
--}}
@extends('main')

@section('title', 'Nota médica · ' . $paciente->nombre_completo)

@section('content')


<div class="mb-3">
    <a href="{{ route('notas.index', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
        <i class="fa-solid fa-arrow-left"></i> Notas de {{ $paciente->nombre_completo }}
    </a>
</div>

<div class="sec-card">
    <div class="sec-header">
        <div class="sec-icon"><i class="fa-solid fa-file-medical"></i></div>
        <div class="flex-grow-1">
            <p class="sec-title">{{ $nota->tipoNota->descripcion ?? 'Nota médica' }}</p>
            <p class="sec-subtitle">
                <span class="mono">{{ $nota->fecha_hora?->format('d/m/Y H:i') }}</span>
                · {{ $nota->medico->nombre_completo ?? '—' }}
            </p>
        </div>
        <span class="nota-estado {{ $firmada ? 'estado-firmada' : 'estado-borrador' }}">
            <i class="fa-solid {{ $firmada ? 'fa-lock' : 'fa-pen-ruler' }}"></i>
            {{ $firmada ? 'Firmada' : 'Borrador' }}
        </span>
    </div>
    <div class="sec-body">
        {{-- SOAP --}}
        @foreach([
        'subjetivo' => ['S','soap-s','Subjetivo'],
        'objetivo' => ['O','soap-o','Objetivo'],
        'analisis' => ['A','soap-a','Análisis'],
        'plan' => ['P','soap-p','Plan'],
        ] as $campo => $d)
        @if($nota->$campo)
        <div class="soap-read">
            <span class="soap-letter {{ $d[1] }}">{{ $d[0] }}</span>
            <div>
                <div class="soap-read-label">{{ $d[2] }}</div>
                <div class="soap-read-text">{!! nl2br(e($nota->$campo)) !!}</div>
            </div>
        </div>
        @endif
        @endforeach

        {{-- Signos vitales (si hay alguno) --}}
        @php
        $sv = collect([
        'T/A' => $nota->presion_arterial,
        'FC' => $nota->frecuencia_cardiaca,
        'FR' => $nota->frecuencia_respiratoria,
        'Temp'=> $nota->temperatura_c,
        'SatO₂'=> $nota->saturacion_o2,
        'Peso'=> $nota->peso_kg,
        ])->filter(fn($v) => $v !== null && $v !== '');
        @endphp
        @if($sv->isNotEmpty())
        <hr class="my-3">
        <div class="sv-read">
            @foreach($sv as $label => $val)
            <div class="sv-read-item">
                <span class="sv-read-label">{{ $label }}</span>
                <span class="sv-read-val mono">{{ $val }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

{{-- Extensión quirúrgica --}}
@if($nota->notaQuirurgica)
@php($q = $nota->notaQuirurgica)
<div class="sec-card">
    <div class="sec-header" style="background:var(--qx-p)">
        <div class="sec-icon" style="background:var(--qx-c)"><i class="fa-solid fa-scalpel"></i></div>
        <div>
            <p class="sec-title" style="color:#92400e">Datos quirúrgicos</p>
        </div>
    </div>
    <div class="sec-body">
        <dl class="row mb-0 small">
            @foreach([
            'Cirujano' => $q->cirujano?->nombre_completo,
            'Anestesiólogo' => $q->anestesiologo?->nombre_completo,
            'Tipo de anestesia' => $q->tipo_anestesia,
            'Inicio' => $q->fecha_hora_inicio?->format('d/m/Y H:i'),
            'Fin' => $q->fecha_hora_fin?->format('d/m/Y H:i'),
            'Dx preoperatorio' => $q->diagnostico_preoperatorio,
            'Dx postoperatorio' => $q->diagnostico_postoperatorio,
            'Procedimiento' => $q->procedimiento_realizado,
            'Hallazgos' => $q->hallazgos,
            'Técnica' => $q->tecnica,
            'Material implantado' => $q->material_implantado,
            'Complicaciones' => $q->complicaciones,
            'Sangrado (ml)' => $q->sangrado_ml,
            'Diuresis (ml)' => $q->diuresis_ml,
            'Estado al egreso' => $q->estado_egreso,
            ] as $label => $val)
            @if($val !== null && $val !== '')
            <dt class="col-sm-4 text-muted fw-normal">{{ $label }}</dt>
            <dd class="col-sm-8">{{ $val }}</dd>
            @endif
            @endforeach
        </dl>
    </div>
</div>
@endif

{{-- Acciones / sello de firma --}}
@if($firmada)
<div class="firma-sello">
    <i class="fa-solid fa-circle-check"></i>
    <div class="flex-grow-1">
        <div class="firma-sello-title">Nota firmada</div>
        <div class="firma-sello-meta">
            Firmada el <span class="mono">{{ $nota->fecha_firma?->format('d/m/Y H:i') }}</span>
            por {{ $nota->medico->nombre_completo ?? '—' }}. Este documento es de solo lectura.
        </div>
    </div>
    <a href="{{ route('notas.imprimir', [$paciente->id_paciente, $nota->id_nota]) }}"
        target="_blank" class="btn-prev text-decoration-none">
        <i class="fa-solid fa-print"></i> Imprimir
    </a>
</div>
@else
<div class="nav-bar">
    <a href="{{ route('notas.edit', [$paciente->id_paciente, $nota->id_nota]) }}" class="btn-prev text-decoration-none">
        <i class="fa-solid fa-pen"></i> Editar
    </a>
    <a href="{{ route('notas.imprimir', [$paciente->id_paciente, $nota->id_nota]) }}"
        target="_blank" class="btn-prev text-decoration-none">
        <i class="fa-solid fa-print"></i> Imprimir
    </a>
    <button type="button" class="btn-next" id="btn-firmar"
        onclick="App.notaForm.firmar('{{ $paciente->id_paciente }}', '{{ $nota->id_nota }}')">
        <i class="fa-solid fa-signature"></i> Firmar nota
    </button>
</div>
<p class="text-muted small mt-2">
    <i class="fa-solid fa-circle-info"></i>
    Al firmar, la nota quedará bloqueada y no podrá modificarse.
</p>
@endif
@endsection

@push('scripts')
@vite('resources/js/notas-form.js')
@endpush