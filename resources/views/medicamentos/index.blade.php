{{--
    Vista: medicamentos/index.blade.php — GET /medicamentos

    Catálogo de medicamentos. Todos los médicos lo ven; solo admin ve los
    botones de gestión y el modal de alta/edición.

    Variables: $medicamentos (paginator), $q, $estado
--}}
@extends('main')

@section('title', 'Medicamentos · Fisio Clínica')

@php($esAdmin = auth()->user()->esAdmin())

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Medicamentos</h1>
        <p class="text-muted small mb-0">{{ $medicamentos->total() }} en el catálogo</p>
    </div>
    @if($esAdmin)
    <button type="button" class="btn-next" onclick="App.medForm.abrir()">
        <i class="fa-solid fa-plus"></i> Nuevo medicamento
    </button>
    @endif
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('medicamentos.index') }}" class="sec-card mb-3">
    <div class="sec-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-7">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control"
                        placeholder="Nombre genérico o comercial…">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="activos" @selected($estado==='activos' )>Activos</option>
                    <option value="inactivos" @selected($estado==='inactivos' )>Archivados</option>
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

{{-- Lista --}}
<div class="sec-card">
    @if($medicamentos->isEmpty())
    <div class="sec-body text-center py-5">
        <i class="fa-solid fa-pills fa-2x mb-2" style="color:var(--slate-mid)"></i>
        <p class="mb-1" style="font-weight:600">Sin medicamentos</p>
        <p class="text-muted small mb-0">
            @if($q) Ajusta la búsqueda o <a href="{{ route('medicamentos.index') }}">límpiala</a>.
            @else El catálogo está vacío. @endif
        </p>
    </div>
    @else
    {{-- Tabla (escritorio) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 pac-table">
            <thead>
                <tr>
                    <th>Genérico</th>
                    <th>Comercial</th>
                    <th>Forma</th>
                    <th>Concentración</th>
                    <th>Vía</th>
                    <th>Estado</th>
                    @if($esAdmin)<th class="text-end">Acciones</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($medicamentos as $m)
                <tr class="{{ $m->activo ? '' : 'row-inactive' }}">
                    <td style="font-weight:600">{{ $m->nombre_generico }}</td>
                    <td class="small">{{ $m->nombre_comercial ?? '—' }}</td>
                    <td class="small">{{ $m->forma_farmaceutica ?? '—' }}</td>
                    <td class="small mono">{{ $m->concentracion ?? '—' }}</td>
                    <td class="small">{{ $m->via_administracion ?? '—' }}</td>
                    <td>
                        @if($m->activo)
                        <span class="estado-firmada nota-estado"><i class="fa-solid fa-circle-check"></i> Activo</span>
                        @else
                        <span class="badge-inactive">Archivado</span>
                        @endif
                    </td>
                    @if($esAdmin)
                    <td class="text-end">
                        <button class="btn-icon btn-editar-med" title="Editar"
                            data-med="{{ json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        @if($m->activo)
                        <button class="btn-icon btn-icon-danger" title="Archivar"
                            onclick="App.medForm.archivar('{{ $m->id_medicamento }}')">
                            <i class="fa-solid fa-box-archive"></i>
                        </button>
                        @else
                        <button class="btn-icon" title="Reactivar"
                            onclick="App.medForm.reactivar('{{ $m->id_medicamento }}')">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tarjetas (móvil) --}}
    <div class="d-md-none">
        @foreach($medicamentos as $m)
        <div class="med-card {{ !$loop->last ? 'border-bottom' : '' }} {{ $m->activo ? '' : 'row-inactive' }}">
            <div class="flex-grow-1">
                <div style="font-weight:600">{{ $m->nombre_generico }}</div>
                <div class="small text-muted">
                    {{ collect([$m->nombre_comercial, $m->concentracion, $m->forma_farmaceutica])->filter()->join(' · ') ?: '—' }}
                </div>
                @unless($m->activo)<span class="badge-inactive mt-1 d-inline-block">Archivado</span>@endunless
            </div>
            @if($esAdmin)
            <div class="d-flex gap-1">
                <button class="btn-icon btn-editar-med" data-med="{{ json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) }}"><i class="fa-solid fa-pen"></i></button>
                @if($m->activo)
                <button class="btn-icon btn-icon-danger" onclick="App.medForm.archivar('{{ $m->id_medicamento }}')"><i class="fa-solid fa-box-archive"></i></button>
                @else
                <button class="btn-icon" onclick="App.medForm.reactivar('{{ $m->id_medicamento }}')"><i class="fa-solid fa-rotate-left"></i></button>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($medicamentos->hasPages())
<div class="pagination-wrap mt-3 d-flex justify-content-center">{{ $medicamentos->links() }}</div>
@endif

{{-- Modal de alta/edición (solo admin) --}}
@if($esAdmin)
<div class="modal-overlay" id="med-modal" style="display:none">
    <div class="modal-box">
        <div class="modal-head">
            <h2 class="modal-title" id="med-modal-title">Nuevo medicamento</h2>
            <button class="modal-close" onclick="App.medForm.cerrar()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="med-form" novalidate>
            @csrf
            <input type="hidden" id="med-id">
            <div class="row g-3">
                <div class="col-12">
                    <label for="nombre_generico" class="form-label">Nombre genérico <span class="req">*</span></label>
                    <input type="text" id="nombre_generico" name="nombre_generico" class="form-control" required>
                    <div class="invalid-feedback" id="err-nombre_generico"></div>
                </div>
                <div class="col-md-6">
                    <label for="nombre_comercial" class="form-label">Nombre comercial</label>
                    <input type="text" id="nombre_comercial" name="nombre_comercial" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="forma_farmaceutica" class="form-label">Forma farmacéutica</label>
                    <input type="text" id="forma_farmaceutica" name="forma_farmaceutica" class="form-control" placeholder="Tableta, jarabe…">
                </div>
                <div class="col-md-6">
                    <label for="concentracion" class="form-label">Concentración</label>
                    <input type="text" id="concentracion" name="concentracion" class="form-control" placeholder="500 mg">
                </div>
                <div class="col-md-6">
                    <label for="via_administracion" class="form-label">Vía de administración</label>
                    <input type="text" id="via_administracion" name="via_administracion" class="form-control" placeholder="Oral, IV…">
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-prev" onclick="App.medForm.cerrar()">Cancelar</button>
                <button type="submit" class="btn-next" id="med-submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@if($esAdmin)

@push('scripts')
@vite('resources/js/medicamentos.js')
@endpush
@endif