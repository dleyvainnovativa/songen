{{--
    Vista: personal/index.blade.php — GET /personal (solo admin)

    Gestión del personal médico. Cada médico es además un usuario del sistema.
    El modal de alta permite (opcionalmente) crear el acceso Firebase. Borrar
    aplica las guardias del servicio (no a sí mismo, no al último admin) y, si
    el médico tiene registros clínicos, archiva en lugar de borrar.

    Variables: $personal (paginator), $q, $estado, $establecimientos, $especialidades
--}}
@extends('main')

@section('title', 'Personal médico · Fisio Clínica')

@section('content')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Personal médico</h1>
        <p class="text-muted small mb-0">{{ $personal->total() }} registro(s)</p>
    </div>
    <button type="button" class="btn-next" onclick="App.persForm.abrir()">
        <i class="fa-solid fa-user-plus"></i> Nuevo personal
    </button>
</div>

<form method="GET" action="{{ route('personal.index') }}" class="sec-card mb-3">
    <div class="sec-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-7">
                <label class="form-label">Buscar</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="q" value="{{ $q }}" class="form-control"
                        placeholder="Nombre, apellido o cédula…">
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

<div class="sec-card">
    @if($personal->isEmpty())
    <div class="sec-body text-center py-5">
        <i class="fa-solid fa-user-doctor fa-2x mb-2" style="color:var(--slate-mid)"></i>
        <p class="mb-1" style="font-weight:600">Sin personal</p>
        <p class="text-muted small mb-0">
            @if($q) Ajusta la búsqueda o <a href="{{ route('personal.index') }}">límpiala</a>.
            @else Agrega el primer miembro del personal. @endif
        </p>
    </div>
    @else
    {{-- Tabla (escritorio) --}}
    <div class="table-responsive d-none d-md-block">
        <table class="table align-middle mb-0 pac-table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Especialidad</th>
                    <th>Rol</th>
                    <th>Acceso</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($personal as $m)
                <tr class="{{ $m->activo ? '' : 'row-inactive' }}">
                    <td>
                        <div style="font-weight:600">{{ $m->nombre_completo }}</div>
                        @if($m->email)<div class="small text-muted">{{ $m->email }}</div>@endif
                    </td>
                    <td class="small mono">{{ $m->cedula_profesional }}</td>
                    <td class="small">{{ $m->especialidad->nombre ?? '—' }}</td>
                    <td>
                        @if($m->rol_sistema === 'admin')
                        <span class="role-chip role-chip-admin"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                        @else
                        <span class="role-chip"><i class="fa-solid fa-user-doctor"></i> Médico</span>
                        @endif
                    </td>
                    <td>
                        @if($m->firebase_uid)
                        <span class="small" style="color:var(--success)" title="Tiene acceso al sistema">
                            <i class="fa-solid fa-circle-check"></i> Sí
                        </span>
                        @else
                        <span class="small text-muted"><i class="fa-solid fa-circle-xmark"></i> No</span>
                        @endif
                    </td>
                    <td>
                        @if($m->activo)
                        <span class="estado-firmada nota-estado"><i class="fa-solid fa-circle-check"></i> Activo</span>
                        @else
                        <span class="badge-inactive">Archivado</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <button class="btn-icon btn-editar-pers" title="Editar"
                            data-pers="{{ json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) }}">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        @if($m->activo)
                        <button class="btn-icon btn-icon-danger" title="Eliminar o archivar"
                            onclick="App.persForm.eliminar('{{ $m->id_medico }}')">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                        @else
                        <button class="btn-icon" title="Reactivar"
                            onclick="App.persForm.reactivar('{{ $m->id_medico }}')">
                            <i class="fa-solid fa-rotate-left"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Tarjetas (móvil) --}}
    <div class="d-md-none">
        @foreach($personal as $m)
        <div class="med-card {{ !$loop->last ? 'border-bottom' : '' }} {{ $m->activo ? '' : 'row-inactive' }}">
            <div class="flex-grow-1 min-w-0">
                <div style="font-weight:600">{{ $m->nombre_completo }}</div>
                <div class="small text-muted">
                    {{ $m->especialidad->nombre ?? 'Sin especialidad' }} · Céd. {{ $m->cedula_profesional }}
                </div>
                <div class="d-flex gap-1 mt-1">
                    @if($m->rol_sistema === 'admin')
                    <span class="role-chip role-chip-admin"><i class="fa-solid fa-shield-halved"></i> Admin</span>
                    @else
                    <span class="role-chip"><i class="fa-solid fa-user-doctor"></i> Médico</span>
                    @endif
                    @unless($m->activo)<span class="badge-inactive">Archivado</span>@endunless
                </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
                <button class="btn-icon btn-editar-pers" data-pers="{{ json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) }}"><i class="fa-solid fa-pen"></i></button>
                @if($m->activo)
                <button class="btn-icon btn-icon-danger" onclick="App.persForm.eliminar('{{ $m->id_medico }}')"><i class="fa-solid fa-trash"></i></button>
                @else
                <button class="btn-icon" onclick="App.persForm.reactivar('{{ $m->id_medico }}')"><i class="fa-solid fa-rotate-left"></i></button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@if($personal->hasPages())
<div class="pagination-wrap mt-3 d-flex justify-content-center">{{ $personal->links() }}</div>
@endif

{{-- Modal de alta/edición --}}
<div class="modal-overlay" id="pers-modal" style="display:none">
    <div class="modal-box">
        <div class="modal-head">
            <h2 class="modal-title" id="pers-modal-title">Nuevo personal</h2>
            <button class="modal-close" onclick="App.persForm.cerrar()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="pers-form" novalidate>
            @csrf
            <input type="hidden" id="pers-id">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre <span class="req">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                    <div class="invalid-feedback" id="err-nombre"></div>
                </div>
                <div class="col-md-4">
                    <label for="primer_apellido" class="form-label">Primer apellido <span class="req">*</span></label>
                    <input type="text" id="primer_apellido" name="primer_apellido" class="form-control" required>
                    <div class="invalid-feedback" id="err-primer_apellido"></div>
                </div>
                <div class="col-md-4">
                    <label for="segundo_apellido" class="form-label">Segundo apellido</label>
                    <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control">
                </div>

                <div class="col-md-6">
                    <label for="cedula_profesional" class="form-label">Cédula profesional <span class="req">*</span></label>
                    <input type="text" id="cedula_profesional" name="cedula_profesional" class="form-control mono" required>
                    <div class="invalid-feedback" id="err-cedula_profesional"></div>
                </div>
                <div class="col-md-6">
                    <label for="cedula_especialidad" class="form-label">Cédula de especialidad</label>
                    <input type="text" id="cedula_especialidad" name="cedula_especialidad" class="form-control mono">
                </div>

                <div class="col-md-6">
                    <label for="id_especialidad" class="form-label">Especialidad</label>
                    <select id="id_especialidad" name="id_especialidad" class="form-select">
                        <option value="">—</option>
                        @foreach($especialidades as $esp)
                        <option value="{{ $esp->id_especialidad }}">{{ $esp->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="id_establecimiento" class="form-label">Establecimiento <span class="req">*</span></label>
                    <select id="id_establecimiento" name="id_establecimiento" class="form-select" required>
                        <option value="">Selecciona…</option>
                        @foreach($establecimientos as $est)
                        <option value="{{ $est->id_establecimiento }}">{{ $est->nombre }}</option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="err-id_establecimiento"></div>
                </div>

                <div class="col-md-6">
                    <label for="rol" class="form-label">Puesto / rol clínico</label>
                    <select id="rol" name="rol" class="form-select">
                        <option value="">—</option>
                        @foreach(config('roles_clinicos') as $rolOpcion)
                        <option value="{{ $rolOpcion }}">{{ $rolOpcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="rol_sistema" class="form-label">Rol del sistema <span class="req">*</span></label>
                    <select id="rol_sistema" name="rol_sistema" class="form-select" required>
                        <option value="medico">Médico (acceso normal)</option>
                        <option value="admin">Administrador</option>
                    </select>
                    <div class="invalid-feedback" id="err-rol_sistema"></div>
                </div>

                <div class="col-md-6">
                    <label for="email" class="form-label">Correo</label>
                    <input type="email" id="email" name="email" class="form-control">
                    <div class="invalid-feedback" id="err-email"></div>
                </div>
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control mono">
                </div>

                {{-- Acceso Firebase: solo al crear --}}
                <div class="col-12" id="acceso-bloque">
                    <div class="acceso-box">
                        <label class="bool-card-inline">
                            <input type="checkbox" id="crear_acceso" name="crear_acceso" value="1"
                                onchange="App.persForm.toggleAcceso()">
                            <span><i class="fa-solid fa-key"></i> Crear acceso al sistema (cuenta Firebase)</span>
                        </label>
                        <div id="acceso-campos" style="display:none" class="mt-2">
                            <label for="password" class="form-label">Contraseña temporal <span class="req">*</span></label>
                            <input type="text" id="password" name="password" class="form-control mono" minlength="6"
                                placeholder="mín. 6 caracteres">
                            <div class="invalid-feedback" id="err-password"></div>
                            <p class="small text-muted mb-0 mt-1">
                                <i class="fa-solid fa-circle-info"></i>
                                Se usará el correo de arriba. El médico podrá cambiarla después.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-prev" onclick="App.persForm.cerrar()">Cancelar</button>
                <button type="submit" class="btn-next" id="pers-submit"><i class="fa-solid fa-floppy-disk"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection


@push('scripts')
@vite(["resources/js/personal.js"])
@endpush