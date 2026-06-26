{{--
    Vista: auditoria/index.blade.php — GET /auditoria (solo admin)
    Variables: $registros (paginator), $medicos, $acciones
--}}
@extends('main')

@section('title', 'Auditoría · Fisio Clínica')

@section('content')
<div class="mb-3">
    <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Registro de auditoría</h1>
    <p class="text-muted small mb-0">{{ $registros->total() }} eventos registrados</p>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('auditoria.index') }}" class="sec-card mb-3">
    <div class="sec-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Buscar</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Descripción o tabla…">
            </div>
            <div class="col-md-2">
                <label class="form-label">Acción</label>
                <select name="accion" class="form-select">
                    <option value="">Todas</option>
                    @foreach($acciones as $a)
                    <option value="{{ $a }}" @selected(request('accion')===$a)>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Médico</label>
                <select name="medico" class="form-select">
                    <option value="">Todos</option>
                    @foreach($medicos as $m)
                    <option value="{{ $m->id_medico }}" @selected((int)request('medico')===$m->id_medico)>
                        {{ $m->nombre_completo }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control">
            </div>
            <div class="col-12 d-flex gap-2 mt-1">
                <button type="submit" class="btn-next"><i class="fa-solid fa-filter"></i> Filtrar</button>
                @if(request()->hasAny(['q','accion','medico','desde','hasta']))
                <a href="{{ route('auditoria.index') }}" class="btn-prev text-decoration-none">
                    <i class="fa-solid fa-xmark"></i> Limpiar
                </a>
                @endif
            </div>
        </div>
    </div>
</form>

{{-- Resultados --}}
<div class="sec-card">
    @if($registros->isEmpty())
    <div class="sec-body text-center py-5">
        <i class="fa-solid fa-clipboard-question fa-2x mb-2" style="color:var(--slate-mid)"></i>
        <p class="mb-0" style="font-weight:600">Sin eventos</p>
        <p class="text-muted small">Ajusta los filtros para ver resultados.</p>
    </div>
    @else
    {{-- Tabla (escritorio) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 pac-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>Médico</th>
                    <th>Paciente</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $r)
                <tr>
                    <td class="mono small">{{ $r->fecha_hora?->format('d/m/Y H:i:s') }}</td>
                    <td><span class="accion-badge accion-{{ strtolower($r->accion) }}">{{ $r->accion }}</span></td>
                    <td class="small">
                        {{ $r->descripcion ?? '—' }}
                        @if($r->tabla_afectada)
                        <span class="text-muted">({{ $r->tabla_afectada }})</span>
                        @endif
                    </td>
                    <td class="small">{{ $r->medico->nombre_completo ?? 'Sistema' }}</td>
                    <td class="small">
                        @if($r->paciente)
                        <a href="{{ route('pacientes.show', $r->id_paciente) }}" class="text-decoration-none">
                            {{ $r->paciente->nombre_completo }}
                        </a>
                        @else — @endif
                    </td>
                    <td class="mono small text-muted">{{ $r->ip_origen ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tarjetas (móvil) --}}
    <div class="d-md-none">
        @foreach($registros as $r)
        <div class="audit-card {{ !$loop->last ? 'border-bottom' : '' }}">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="accion-badge accion-{{ strtolower($r->accion) }}">{{ $r->accion }}</span>
                <span class="mono small text-muted">{{ $r->fecha_hora?->format('d/m/Y H:i') }}</span>
            </div>
            <div class="small">{{ $r->descripcion ?? $r->tabla_afectada ?? '—' }}</div>
            <div class="small text-muted mt-1">
                {{ $r->medico->nombre_completo ?? 'Sistema' }}
                @if($r->paciente) · {{ $r->paciente->nombre_completo }} @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($registros->hasPages())
<div class="pagination-wrap mt-3 d-flex justify-content-center">{{ $registros->links() }}</div>
@endif
@endsection