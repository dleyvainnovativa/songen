{{--
    Vista: establecimientos/index.blade.php — GET /establecimientos

    Gestión de establecimientos (clínicas). Todos ven; solo admin gestiona.
    Borrar está protegido: si tiene pacientes o personal ligados, el API lo
    rechaza (422) y se muestra el motivo.

    Variables: $establecimientos (paginator con counts), $q
--}}
@extends('main')

@section('title', 'Establecimientos · Fisio Clínica')

@php($esAdmin = auth()->user()->esAdmin())

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Establecimientos</h1>
        <p class="text-muted small mb-0">{{ $establecimientos->total() }} registrado(s)</p>
    </div>
    @if($esAdmin)
    <button type="button" class="btn-next" onclick="App.estForm.abrir()">
        <i class="fa-solid fa-plus"></i> Nuevo establecimiento
    </button>
    @endif
</div>

<form method="GET" action="{{ route('establecimientos.index') }}" class="sec-card mb-3">
    <div class="sec-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-10">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control"
                        placeholder="Nombre, municipio o estado…">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn-next w-100 justify-content-center">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </button>
            </div>
        </div>
    </div>
</form>

@if($establecimientos->isEmpty())
<div class="sec-card">
    <div class="sec-body text-center py-5">
        <i class="fa-solid fa-hospital fa-2x mb-2" style="color:var(--slate-mid)"></i>
        <p class="mb-1" style="font-weight:600">Sin establecimientos</p>
        <p class="text-muted small mb-0">
            @if($q) Ajusta la búsqueda o <a href="{{ route('establecimientos.index') }}">límpiala</a>.
            @else No hay establecimientos registrados. @endif
        </p>
    </div>
</div>
@else
<div class="row g-3">
    @foreach($establecimientos as $e)
    <div class="col-md-6">
        <div class="sec-card mb-0 h-100">
            <div class="sec-body">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div class="min-w-0">
                        <div style="font-weight:700;font-size:.95rem;color:var(--slate)">{{ $e->nombre }}</div>
                        @if($e->razon_social)
                        <div class="small text-muted">{{ $e->razon_social }}</div>
                        @endif
                    </div>
                    @if($esAdmin)
                    <div class="d-flex gap-1 flex-shrink-0">
                        <button class="btn-icon btn-editar-est" title="Editar"
                            data-est="{{ json_encode($e, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <button class="btn-icon btn-icon-danger" title="Eliminar"
                            onclick="App.estForm.eliminar('{{ $e->id_establecimiento }}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                    @endif
                </div>

                <dl class="row mb-0 small mt-3">
                    <dt class="col-4 text-muted fw-normal">Domicilio</dt>
                    <dd class="col-8">{{ collect([$e->domicilio, $e->colonia, $e->municipio, $e->estado])->filter()->join(', ') }}</dd>
                    @if($e->telefono)
                    <dt class="col-4 text-muted fw-normal">Teléfono</dt>
                    <dd class="col-8 mono">{{ $e->telefono }}</dd>
                    @endif
                    @if($e->rfc)
                    <dt class="col-4 text-muted fw-normal">RFC</dt>
                    <dd class="col-8 mono">{{ $e->rfc }}</dd>
                    @endif
                </dl>

                <div class="d-flex gap-3 mt-3 pt-2 border-top">
                    <span class="small text-muted"><i class="fa-solid fa-users"></i> {{ $e->pacientes_count }} pacientes</span>
                    <span class="small text-muted"><i class="fa-solid fa-user-doctor"></i> {{ $e->personal_medico_count }} personal</span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@if($establecimientos->hasPages())
<div class="pagination-wrap mt-3 d-flex justify-content-center">{{ $establecimientos->links() }}</div>
@endif

{{-- Modal (solo admin) --}}
@if($esAdmin)
<div class="modal-overlay" id="est-modal" style="display:none">
    <div class="modal-box">
        <div class="modal-head">
            <h2 class="modal-title" id="est-modal-title">Nuevo establecimiento</h2>
            <button class="modal-close" onclick="App.estForm.cerrar()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="est-form" novalidate>
            @csrf
            <input type="hidden" id="est-id">
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="nombre" class="form-label">Nombre <span class="req">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                    <div class="invalid-feedback" id="err-nombre"></div>
                </div>
                <div class="col-md-4">
                    <label for="nivel_atencion" class="form-label">Nivel de atención</label>
                    <select id="nivel_atencion" name="nivel_atencion" class="form-select">
                        <option value="">—</option>
                        <option value="1">1er nivel</option>
                        <option value="2">2do nivel</option>
                        <option value="3">3er nivel</option>
                    </select>
                </div>
                <div class="col-12">
                    <label for="razon_social" class="form-label">Razón social</label>
                    <input type="text" id="razon_social" name="razon_social" class="form-control">
                </div>
                <div class="col-md-6">
                    <label for="rfc" class="form-label">RFC</label>
                    <input type="text" id="rfc" name="rfc" class="form-control mono text-uppercase" maxlength="13">
                </div>
                <div class="col-md-6">
                    <label for="licencia_sanitaria" class="form-label">Licencia sanitaria</label>
                    <input type="text" id="licencia_sanitaria" name="licencia_sanitaria" class="form-control">
                </div>
                <div class="col-12">
                    <label for="domicilio" class="form-label">Domicilio <span class="req">*</span></label>
                    <input type="text" id="domicilio" name="domicilio" class="form-control" required>
                    <div class="invalid-feedback" id="err-domicilio"></div>
                </div>
                <div class="col-md-4">
                    <label for="colonia" class="form-label">Colonia</label>
                    <input type="text" id="colonia" name="colonia" class="form-control">
                </div>
                <div class="col-md-4">
                    <label for="municipio" class="form-label">Municipio <span class="req">*</span></label>
                    <input type="text" id="municipio" name="municipio" class="form-control" required>
                    <div class="invalid-feedback" id="err-municipio"></div>
                </div>
                <div class="col-md-4">
                    <label for="estado" class="form-label">Estado <span class="req">*</span></label>
                    <input type="text" id="estado" name="estado" class="form-control" required>
                    <div class="invalid-feedback" id="err-estado"></div>
                </div>
                <div class="col-md-4">
                    <label for="cp" class="form-label">C.P.</label>
                    <input type="text" id="cp" name="cp" class="form-control mono" maxlength="5">
                </div>
                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control mono">
                </div>
                <div class="col-md-4">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" id="email" name="email" class="form-control">
                    <div class="invalid-feedback" id="err-email"></div>
                </div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-prev" onclick="App.estForm.cerrar()">Cancelar</button>
                <button type="submit" class="btn-next" id="est-submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@if($esAdmin)
@push('scripts')
@vite(["resources/js/establecimientos.js"])
@endpush
@endif