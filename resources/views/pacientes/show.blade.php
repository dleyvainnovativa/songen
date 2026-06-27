{{--
    Vista: pacientes/show.blade.php
    Ruta:  GET /pacientes/{paciente}

    Expediente del paciente: resumen, alertas, contactos, medicamentos y estado
    de la historia clínica. Punto de partida hacia HC y notas (fases 3 y 4).

    Variable: $paciente (con relaciones cargadas)
--}}
@extends('main')

@section('title', $paciente->nombre_completo . ' · Fisio Clínica')

@section('content')
@php
$cfg = match($paciente->tipo_paciente) {
'Quirúrgico' => ['c'=>'#d97706','bg'=>'#fffbeb','bd'=>'#fde68a','i'=>'fa-user-doctor'],
'Neurológico' => ['c'=>'#0891b2','bg'=>'#ecfeff','bd'=>'#a5f3fc','i'=>'fa-brain'],
'Geriátrico' => ['c'=>'#16a34a','bg'=>'#f0fdf4','bd'=>'#bbf7d0','i'=>'fa-person-cane'],
default => ['c'=>'#64748b','bg'=>'#f1f5f9','bd'=>'#e2e8f0','i'=>'fa-user'],
};
$initials = mb_strtoupper(mb_substr($paciente->nombre,0,1).mb_substr($paciente->primer_apellido,0,1));
@endphp

<div class="mb-3">
    <a href="{{ route('pacientes.index') }}" class="text-decoration-none small text-muted">
        <i class="fa-solid fa-arrow-left"></i> Pacientes
    </a>
</div>

{{-- Encabezado --}}
<div class="pac-summary">
    <div class="pac-avatar" style="background:{{ $cfg['c'] }}">{{ $initials }}</div>
    <div>
        <div class="pac-info-name">
            {{ $paciente->nombre_completo }}
            @unless($paciente->activo)<span class="badge-inactive">Inactivo</span>@endunless
        </div>
        <div class="pac-info-meta">
            {{ $paciente->edad }} años ·
            {{ match($paciente->sexo){'M'=>'Masculino','F'=>'Femenino',default=>'Indeterminado'} }} ·
            Exp. <span class="mono">{{ $paciente->numero_expediente }}</span>
        </div>
    </div>
    <div class="ms-auto d-flex align-items-center gap-2">
        <span class="tipo-pill" style="color:{{ $cfg['c'] }};background:{{ $cfg['bg'] }};border-color:{{ $cfg['bd'] }}">
            <i class="fa-solid {{ $cfg['i'] }}" style="font-size:.65rem"></i> {{ $paciente->tipo_paciente }}
        </span>
        <a href="{{ route('pacientes.edit', $paciente->id_paciente) }}" class="btn-prev text-decoration-none">
            <i class="fa-solid fa-pen"></i> Editar
        </a>
        @if($paciente->activo)
        <button type="button" class="btn-prev" id="btn-archivar"
            onclick="App.pacShow.archivar('{{ $paciente->id_paciente }}')">
            <i class="fa-solid fa-box-archive"></i> Archivar
        </button>
        @else
        <button type="button" class="btn-next" id="btn-reactivar"
            onclick="App.pacShow.reactivar('{{ $paciente->id_paciente }}')">
            <i class="fa-solid fa-rotate-left"></i> Reactivar
        </button>
        @endif
    </div>
</div>

{{-- Alergias (banda de alerta) --}}
@if($paciente->alergias_conocidas)
<div class="alert-clinico mb-3">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div><strong>Alergias conocidas:</strong> {{ $paciente->alergias_conocidas }}</div>
</div>
@endif

<div class="row g-3">
    {{-- Columna izquierda: datos --}}
    <div class="col-lg-7">
        {{-- Historia clínica (estado / acceso) --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-file-medical"></i></div>
                <div>
                    <p class="sec-title">Historia clínica</p>
                    <p class="sec-subtitle">Extensión {{ $paciente->tipo_paciente }}</p>
                </div>
            </div>
            <div class="sec-body">
                @if($paciente->historiaClinica)
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small text-muted">
                        <i class="fa-solid fa-circle-check" style="color:var(--success)"></i>
                        Elaborada el
                        <span class="mono">{{ $paciente->historiaClinica->fecha_elaboracion?->format('d/m/Y') }}</span>
                    </div>
                    <a href="{{ route('historias.edit', $paciente->id_paciente) }}" class="btn-prev text-decoration-none">
                        <i class="fa-solid fa-pen"></i> Ver / editar
                    </a>
                    <a href="{{ route('historias.imprimir', $paciente->id_paciente) }}"
                        target="_blank" class="btn-prev text-decoration-none">
                        <i class="fa-solid fa-print"></i> Imprimir
                    </a>
                </div>
                @else
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="small text-muted">
                        <i class="fa-solid fa-circle-info"></i> Aún sin historia clínica.
                    </div>
                    <a href="{{ route('historias.edit', $paciente->id_paciente) }}" class="btn-next text-decoration-none">
                        <i class="fa-solid fa-file-circle-plus"></i> Capturar historia
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Notas médicas (acceso al timeline) --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-notes-medical"></i></div>
                <div>
                    <p class="sec-title">Notas médicas</p>
                    <p class="sec-subtitle">
                        {{ $paciente->notasMedicas()->count() }} nota(s) registrada(s)
                    </p>
                </div>
            </div>
            <div class="sec-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="{{ route('notas.index', $paciente->id_paciente) }}" class="btn-prev text-decoration-none">
                        <i class="fa-solid fa-list"></i> Ver notas
                    </a>
                    <a href="{{ route('notas.create', $paciente->id_paciente) }}" class="btn-next text-decoration-none">
                        <i class="fa-solid fa-plus"></i> Nueva nota
                    </a>
                </div>
            </div>
        </div>



        {{-- Domicilio --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-location-dot"></i></div>
                <div>
                    <p class="sec-title">Domicilio y contacto</p>
                </div>
            </div>
            <div class="sec-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-3 text-muted fw-normal">Teléfono</dt>
                    <dd class="col-sm-9 mono">{{ $paciente->telefono ?? '—' }}</dd>
                    <dt class="col-sm-3 text-muted fw-normal">Correo</dt>
                    <dd class="col-sm-9">{{ $paciente->email ?? '—' }}</dd>
                    <dt class="col-sm-3 text-muted fw-normal">Domicilio</dt>
                    <dd class="col-sm-9">
                        {{ collect([$paciente->domicilio, $paciente->colonia, $paciente->municipio, $paciente->estado, $paciente->cp])->filter()->join(', ') ?: '—' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Columna derecha: contactos + medicamentos --}}
    <div class="col-lg-5">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon" style="background:#7c3aed"><i class="fa-solid fa-phone-volume"></i></div>
                <div>
                    <p class="sec-title">Contactos de emergencia</p>
                </div>
            </div>
            <div class="sec-body">
                @forelse($paciente->contactosEmergencia as $c)
                <div class="d-flex justify-content-between py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div style="font-weight:600;font-size:.85rem">{{ $c->nombre_completo }}</div>
                        <div class="text-muted" style="font-size:.75rem">{{ $c->parentesco ?? 'Contacto' }}</div>
                    </div>
                    <div class="mono small text-end">
                        {{ $c->telefono }}
                        @if($c->telefono_alt)<br><span class="text-muted">{{ $c->telefono_alt }}</span>@endif
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0">Sin contactos registrados.</p>
                @endforelse
            </div>
        </div>

        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon" style="background:var(--info)"><i class="fa-solid fa-pills"></i></div>
                <div>
                    <p class="sec-title">Medicamentos actuales</p>
                </div>
            </div>
            <div class="sec-body">
                @forelse($paciente->medicamentos->where('activo', true) as $m)
                <div class="d-flex justify-content-between py-1 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div style="font-weight:600;font-size:.85rem">
                        {{ $m->medicamento->nombre_generico ?? 'Medicamento' }}
                    </div>
                    <div class="small text-muted text-end">{{ $m->dosis }} · {{ $m->frecuencia }}</div>
                </div>
                @empty
                <p class="text-muted small mb-0">Sin medicamentos registrados.</p>
                @endforelse
            </div>
        </div>
        {{-- Documentos --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon" style="background:#7c3aed"><i class="fa-solid fa-folder-open"></i></div>
                <div>
                    <p class="sec-title">Documentos</p>
                    <p class="sec-subtitle">{{ $paciente->documentos()->count() }} archivo(s)</p>
                </div>
            </div>
            <div class="sec-body">
                <a href="{{ route('documentos.index', $paciente->id_paciente) }}" class="btn-prev text-decoration-none">
                    <i class="fa-solid fa-folder-open"></i> Ver documentos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite('resources/js/pacientes-archive.js')
@endpush