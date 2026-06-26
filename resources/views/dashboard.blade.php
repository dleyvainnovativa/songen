{{--
    Vista: dashboard.blade.php — GET /dashboard
    Resumen de la clínica. Variables: $stats, $recientes, $actividad
--}}
@extends('main')

@section('title', 'Inicio · Fisio Clínica')

@section('content')
@php($u = auth()->user())

<div class="mb-4">
    <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">
        Hola, {{ $u->nombre }}
    </h1>
    <p class="text-muted small mb-0">Resumen de la clínica</p>
</div>

{{-- Tarjetas de métricas --}}
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--teal-pale);color:var(--teal-dark)">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <div class="stat-num">{{ $stats['pacientes_activos'] }}</div>
            <div class="stat-label">Pacientes activos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#ecfeff;color:#0891b2">
            <i class="fa-solid fa-file-medical"></i>
        </div>
        <div>
            <div class="stat-num">{{ $stats['notas_mes'] }}</div>
            <div class="stat-label">Notas este mes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fffbeb;color:#d97706">
            <i class="fa-solid fa-pen-ruler"></i>
        </div>
        <div>
            <div class="stat-num">{{ $stats['notas_sin_firmar'] }}</div>
            <div class="stat-label">Notas sin firmar</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Distribución por tipo --}}
    <div class="col-lg-5">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <div>
                    <p class="sec-title">Pacientes por tipo</p>
                </div>
            </div>
            <div class="sec-body">
                @foreach([
                'Quirúrgico' => ['#d97706','fa-user-doctor'],
                'Neurológico' => ['#0891b2','fa-brain'],
                'Geriátrico' => ['#16a34a','fa-person-cane'],
                ] as $tipo => $d)
                @php($n = $stats['por_tipo'][$tipo])
                @php($total = max($stats['pacientes_activos'], 1))
                <div class="tipo-stat">
                    <div class="tipo-stat-head">
                        <span><i class="fa-solid {{ $d[1] }}" style="color:{{ $d[0] }}"></i> {{ $tipo }}</span>
                        <strong>{{ $n }}</strong>
                    </div>
                    <div class="tipo-stat-bar">
                        <div class="tipo-stat-fill" style="width:{{ round($n/$total*100) }}%;background:{{ $d[0] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Pacientes recientes --}}
    <div class="col-lg-7">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                <div>
                    <p class="sec-title">Pacientes recientes</p>
                </div>
            </div>
            <div class="sec-body">
                @forelse($recientes as $p)
                <a href="{{ route('pacientes.show', $p->id_paciente) }}"
                    class="reciente-row {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="pac-avatar" style="width:34px;height:34px;font-size:.8rem;background:{{ $p['cfg'][0] }}">{{ $p["ini"] }}</div>
                    <div class="flex-grow-1">
                        <div class="pac-info-name">{{ $p->nombre_completo }}</div>
                        <div class="pac-info-meta">{{ $p->edad }} años · Exp. {{ $p->numero_expediente }}</div>
                    </div>
                    <i class="fa-solid fa-chevron-right text-muted small"></i>
                </a>
                @empty
                <p class="text-muted small mb-0">Aún no hay pacientes.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Actividad reciente (solo admin) --}}
    @if($u->esAdmin() && $actividad->isNotEmpty())
    <div class="col-12">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-clipboard-list"></i></div>
                <div class="flex-grow-1">
                    <p class="sec-title">Actividad reciente</p>
                </div>
                <a href="{{ route('auditoria.index') }}" class="text-decoration-none small">Ver todo</a>
            </div>
            <div class="sec-body">
                @foreach($actividad as $a)
                <div class="actividad-row {{ !$loop->last ? 'border-bottom' : '' }}">
                    <span class="accion-badge accion-{{ strtolower($a->accion) }}">{{ $a->accion }}</span>
                    <span class="small flex-grow-1">{{ $a->descripcion ?? $a->tabla_afectada }}</span>
                    <span class="small text-muted">{{ $a->medico->nombre ?? 'Sistema' }}</span>
                    <span class="small text-muted mono">{{ $a->fecha_hora?->format('d/m H:i') }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection