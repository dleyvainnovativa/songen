{{--
    Vista: pacientes/index.blade.php
    Ruta:  GET /pacientes

    Listado de pacientes con búsqueda (nombre/expediente/CURP), filtro por tipo
    y por estado (activos/inactivos/todos). Paginado server-side.

    Variables: $pacientes (paginator), $q, $tipo, $estado
--}}
@extends('main')

@section('title', 'Pacientes · Fisio Clínica')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Pacientes</h1>
        <p class="text-muted small mb-0">{{ $pacientes->total() }} en total</p>
    </div>
    <a href="{{ route('pacientes.create') }}" class="btn-next text-decoration-none">
        <i class="fa-solid fa-user-plus"></i> Nuevo paciente
    </a>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('pacientes.index') }}" class="sec-card mb-3">
    <div class="sec-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control"
                        placeholder="Nombre, expediente o CURP…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos los tipos</option>
                    @foreach(['Quirúrgico', 'Neurológico', 'Geriátrico'] as $t)
                    <option value="{{ $t }}" @selected($tipo===$t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="activos" @selected($estado==='activos' )>Activos</option>
                    <option value="inactivos" @selected($estado==='inactivos' )>Inactivos</option>
                    <option value="todos" @selected($estado==='todos' )>Todos</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-next w-100 justify-content-center">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</form>

{{-- Tabla --}}
<div class="sec-card">
    @if($pacientes->isEmpty())
    <div class="sec-body text-center py-5">
        <i class="fa-solid fa-users-slash fa-2x mb-2" style="color:var(--slate-mid)"></i>
        <p class="mb-1" style="font-weight:600">Sin resultados</p>
        <p class="text-muted small mb-3">
            @if($q || $tipo)
            Ajusta los filtros o
            <a href="{{ route('pacientes.index') }}">límpialos</a>.
            @else
            Aún no hay pacientes registrados.
            @endif
        </p>
        <a href="{{ route('pacientes.create') }}" class="btn-next d-inline-flex text-decoration-none">
            <i class="fa-solid fa-user-plus"></i> Registrar el primero
        </a>
    </div>
    @else
    {{-- Vista tabla (escritorio) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 pac-table">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Expediente</th>
                    <th>Tipo</th>
                    <th>Edad</th>
                    <th>Teléfono</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pacientes as $p)
                @php
                $cfg = match($p->tipo_paciente) {
                'Quirúrgico' => ['c'=>'#d97706','bg'=>'#fffbeb','bd'=>'#fde68a','i'=>'fa-user-doctor'],
                'Neurológico' => ['c'=>'#0891b2','bg'=>'#ecfeff','bd'=>'#a5f3fc','i'=>'fa-brain'],
                'Geriátrico' => ['c'=>'#16a34a','bg'=>'#f0fdf4','bd'=>'#bbf7d0','i'=>'fa-person-cane'],
                default => ['c'=>'#64748b','bg'=>'#f1f5f9','bd'=>'#e2e8f0','i'=>'fa-user'],
                };
                $initials = mb_strtoupper(mb_substr($p->nombre,0,1).mb_substr($p->primer_apellido,0,1));
                @endphp
                <tr class="{{ $p->activo ? '' : 'row-inactive' }}">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="pac-avatar" style="width:34px;height:34px;font-size:.8rem;background:{{ $cfg['c'] }}">
                                {{ $initials }}
                            </div>
                            <div>
                                <a href="{{ route('pacientes.show', $p->id_paciente) }}"
                                    class="pac-info-name text-decoration-none">
                                    {{ $p->nombre_completo }}
                                </a>
                                @unless($p->activo)
                                <span class="badge-inactive">Inactivo</span>
                                @endunless
                                @if($p->alergias_conocidas)
                                <i class="fa-solid fa-triangle-exclamation ms-1"
                                    style="color:var(--danger);font-size:.7rem"
                                    title="Alergias: {{ $p->alergias_conocidas }}"></i>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="mono small">{{ $p->numero_expediente }}</td>
                    <td>
                        <span class="tipo-pill" style="color:{{ $cfg['c'] }};background:{{ $cfg['bg'] }};border-color:{{ $cfg['bd'] }}">
                            <i class="fa-solid {{ $cfg['i'] }}" style="font-size:.6rem"></i>
                            {{ $p->tipo_paciente }}
                        </span>
                    </td>
                    <td class="small">{{ $p->edad }} años</td>
                    <td class="small mono">{{ $p->telefono ?? '—' }}</td>
                    <td class="text-end">
                        <a href="{{ route('pacientes.show', $p->id_paciente) }}"
                            class="btn-icon" title="Ver expediente">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <a href="{{ route('pacientes.edit', $p->id_paciente) }}"
                            class="btn-icon" title="Editar">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Vista tarjetas (móvil) --}}
    <div class="pac-cards d-md-none">
        @foreach($pacientes as $p)
        @php
        $cfg = match($p->tipo_paciente) {
        'Quirúrgico' => ['c'=>'#d97706','bg'=>'#fffbeb','bd'=>'#fde68a','i'=>'fa-user-doctor'],
        'Neurológico' => ['c'=>'#0891b2','bg'=>'#ecfeff','bd'=>'#a5f3fc','i'=>'fa-brain'],
        'Geriátrico' => ['c'=>'#16a34a','bg'=>'#f0fdf4','bd'=>'#bbf7d0','i'=>'fa-person-cane'],
        default => ['c'=>'#64748b','bg'=>'#f1f5f9','bd'=>'#e2e8f0','i'=>'fa-user'],
        };
        $initials = mb_strtoupper(mb_substr($p->nombre,0,1).mb_substr($p->primer_apellido,0,1));
        @endphp
        <a href="{{ route('pacientes.show', $p->id_paciente) }}"
            class="pac-card {{ $p->activo ? '' : 'pac-card-inactive' }}">
            <div class="pac-avatar" style="background:{{ $cfg['c'] }}">{{ $initials }}</div>
            <div class="pac-card-body">
                <div class="pac-card-top">
                    <span class="pac-info-name">{{ $p->nombre_completo }}</span>
                    @unless($p->activo)<span class="badge-inactive">Inactivo</span>@endunless
                </div>
                <div class="pac-card-meta">
                    <span class="mono">{{ $p->numero_expediente }}</span>
                    <span>·</span>
                    <span>{{ $p->edad }} años</span>
                    @if($p->telefono)
                    <span>·</span><span class="mono">{{ $p->telefono }}</span>
                    @endif
                </div>
                <div class="pac-card-tags">
                    <span class="tipo-pill" style="color:{{ $cfg['c'] }};background:{{ $cfg['bg'] }};border-color:{{ $cfg['bd'] }}">
                        <i class="fa-solid {{ $cfg['i'] }}" style="font-size:.6rem"></i>
                        {{ $p->tipo_paciente }}
                    </span>
                    @if($p->alergias_conocidas)
                    <span class="tag-alergia">
                        <i class="fa-solid fa-triangle-exclamation"></i> Alergias
                    </span>
                    @endif
                </div>
            </div>
            <i class="fa-solid fa-chevron-right pac-card-chevron"></i>
        </a>
        @endforeach
    </div>
    @endif
</div>

@if($pacientes->hasPages())
<div class="pagination-wrap mt-3 d-flex justify-content-center">
    {{ $pacientes->links() }}
</div>
@endif
@endsection