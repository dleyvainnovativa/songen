{{--
    Vista: notas/index.blade.php
    Ruta:  GET /pacientes/{paciente}/notas

    Timeline de notas médicas del paciente (más recientes arriba). Cada nota
    muestra tipo, fecha, médico, un extracto del análisis y su estado (firmada
    o borrador). Las firmadas muestran candado.

    Variables: $paciente, $notas (colección)
--}}
@extends('main')

@section('title', 'Notas médicas · ' . $paciente->nombre_completo)

@section('content')
    <div class="mb-3">
        <a href="{{ route('pacientes.show', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left"></i> {{ $paciente->nombre_completo }}
        </a>
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-1">
            <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Notas médicas</h1>
            <a href="{{ route('notas.create', $paciente->id_paciente) }}" class="btn-next text-decoration-none">
                <i class="fa-solid fa-notes-medical"></i> Nueva nota
            </a>
        </div>
    </div>

    @if($notas->isEmpty())
        <div class="sec-card">
            <div class="sec-body text-center py-5">
                <i class="fa-solid fa-file-medical fa-2x mb-2" style="color:var(--slate-mid)"></i>
                <p class="mb-1" style="font-weight:600">Sin notas todavía</p>
                <p class="text-muted small mb-3">Registra la primera nota de evolución de este paciente.</p>
                <a href="{{ route('notas.create', $paciente->id_paciente) }}" class="btn-next d-inline-flex text-decoration-none">
                    <i class="fa-solid fa-notes-medical"></i> Crear primera nota
                </a>
            </div>
        </div>
    @else
        <div class="timeline">
            @foreach($notas as $nota)
                @php
                    $firmada = $nota->firmada;
                @endphp
                <a href="{{ route('notas.show', [$paciente->id_paciente, $nota->id_nota]) }}"
                   class="timeline-item {{ $firmada ? 'is-firmada' : 'is-borrador' }}">
                    <div class="timeline-dot">
                        <i class="fa-solid {{ $firmada ? 'fa-lock' : 'fa-pen' }}"></i>
                    </div>
                    <div class="timeline-card">
                        <div class="timeline-top">
                            <span class="nota-tipo">{{ $nota->tipoNota->descripcion ?? 'Nota' }}</span>
                            <span class="nota-estado {{ $firmada ? 'estado-firmada' : 'estado-borrador' }}">
                                <i class="fa-solid {{ $firmada ? 'fa-circle-check' : 'fa-pen-ruler' }}"></i>
                                {{ $firmada ? 'Firmada' : 'Borrador' }}
                            </span>
                        </div>
                        <div class="timeline-meta">
                            <span class="mono">{{ $nota->fecha_hora?->format('d/m/Y H:i') }}</span>
                            <span>·</span>
                            <span>{{ $nota->medico->nombre_completo ?? '—' }}</span>
                        </div>
                        @if($nota->analisis)
                            <p class="timeline-extract">{{ \Illuminate\Support\Str::limit($nota->analisis, 140) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
@endsection
