{{--
    Partial: pacientes/_form.blade.php

    Formulario compartido por create y edit. El modo lo determina $paciente:
      - null  → alta (POST)
      - model → edición (PUT), con campos prellenados

    El submit lo maneja resources/js/pacientes-form.js vía window.App.
    tipo_paciente se bloquea en edición si el paciente ya tiene historia clínica.

    Variables esperadas:
      $paciente (Paciente|null), $establecimientos, $estadosCiviles,
      $escolaridades, $tiposSangre, $medicamentos
--}}
@php
$p = $paciente ?? null;
$esEdicion = (bool) $p;
$tipoBloqueado = $esEdicion && $p->historiaClinica()->exists();
$old = fn($campo, $def = '') => old($campo, $p->$campo ?? $def);
@endphp

<form id="paciente-form"
    data-modo="{{ $esEdicion ? 'edit' : 'create' }}"
    @if($esEdicion) data-id="{{ $p->id_paciente }}" @endif
    novalidate>
    @csrf

    {{-- ── Tipo de paciente (tarjetas) ──────────────────────────────────── --}}
    @php
    $tiposInfo = [
    'Quirúrgico' => [
    'cls' => 'qx',
    'icono'=> 'fa-user-doctor',
    'info' => 'Se generará una extensión con clasificación ASA, riesgo quirúrgico y datos de anestesia.',
    ],
    'Neurológico' => [
    'cls' => 'neuro',
    'icono'=> 'fa-brain',
    'info' => 'Se incluirá Escala de Glasgow, evaluación de pares craneales y valoración cognitiva (MMSE/MoCA).',
    ],
    'Geriátrico' => [
    'cls' => 'geri',
    'icono'=> 'fa-person-cane',
    'info' => 'Se activará la Valoración Geriátrica Integral: Barthel, Lawton, Tinetti y detección de polifarmacia.',
    ],
    'Otro' => [
    'cls' => 'otro',
    'icono'=> 'fa-user',
    'info' => 'Paciente genérico sin extensión clínica especializada. La historia clínica tendrá los apartados generales únicamente.',
    ],
    ];
    $tipoSel = $old('tipo_paciente');
    @endphp
    <div class="sec-card" id="sec-tipo">
        <div class="sec-header">
            <div class="sec-icon"><i class="fa-solid fa-stethoscope"></i></div>
            <div>
                <p class="sec-title">Tipo de paciente <span class="req">*</span></p>
                <p class="sec-subtitle">Define la especialización del expediente clínico</p>
            </div>
        </div>
        <div class="sec-body">
            <input type="hidden" id="tipo_paciente" name="tipo_paciente" value="{{ $tipoSel }}">

            <div class="tipo-grid {{ $tipoBloqueado ? 'tipo-grid-locked' : '' }}">
                @foreach($tiposInfo as $valor => $cfg)
                <div class="tipo-card tipo-card-{{ $cfg['cls'] }} {{ $tipoSel === $valor ? 'selected' : '' }}"
                    data-value="{{ $valor }}"
                    @unless($tipoBloqueado) onclick="App.pacForm.selectTipo(this)" @endunless>
                    <i class="fa-solid {{ $cfg['icono'] }}"></i>
                    <span class="tipo-label">{{ $valor }}</span>
                    <i class="fa-solid fa-circle-check tipo-check"></i>
                </div>
                @endforeach
            </div>

            <div class="invalid-feedback" id="err-tipo_paciente"></div>

            @if($tipoBloqueado)
            <p class="tipo-locked-note">
                <i class="fa-solid fa-lock"></i>
                No se puede cambiar: el paciente ya tiene historia clínica.
            </p>
            @endif

            {{-- Panel informativo según el tipo elegido --}}
            <div id="tipo-info" class="tipo-info" style="{{ $tipoSel ? '' : 'display:none' }}">
                @foreach($tiposInfo as $valor => $cfg)
                <div class="tipo-info-box tipo-info-{{ $cfg['cls'] }}"
                    data-for="{{ $valor }}"
                    style="{{ $tipoSel === $valor ? '' : 'display:none' }}">
                    <i class="fa-solid {{ $cfg['icono'] }}"></i>
                    <div><strong>Paciente {{ $valor }}:</strong> {{ $cfg['info'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Datos de identificación ──────────────────────────────────────── --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon"><i class="fa-solid fa-id-card"></i></div>
            <div>
                <p class="sec-title">Identificación</p>
                <p class="sec-subtitle">Datos personales del paciente</p>
            </div>
        </div>
        <div class="sec-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="numero_expediente" class="form-label">N.º de expediente <span class="req">*</span></label>
                    <input type="text" id="numero_expediente" name="numero_expediente"
                        class="form-control mono" value="{{ $old('numero_expediente') }}" required>
                    <div class="invalid-feedback" id="err-numero_expediente"></div>
                </div>

                <div class="col-md-4">
                    <label for="nombre" class="form-label">Nombre(s) <span class="req">*</span></label>
                    <input type="text" id="nombre" name="nombre" class="form-control"
                        value="{{ $old('nombre') }}" required>
                    <div class="invalid-feedback" id="err-nombre"></div>
                </div>
                <div class="col-md-4">
                    <label for="primer_apellido" class="form-label">Primer apellido <span class="req">*</span></label>
                    <input type="text" id="primer_apellido" name="primer_apellido" class="form-control"
                        value="{{ $old('primer_apellido') }}" required>
                    <div class="invalid-feedback" id="err-primer_apellido"></div>
                </div>
                <div class="col-md-4">
                    <label for="segundo_apellido" class="form-label">Segundo apellido</label>
                    <input type="text" id="segundo_apellido" name="segundo_apellido" class="form-control"
                        value="{{ $old('segundo_apellido') }}">
                </div>

                <div class="col-md-3">
                    <label for="fecha_nacimiento" class="form-label">Fecha de nacimiento <span class="req">*</span></label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                        value="{{ $old('fecha_nacimiento') ? \Illuminate\Support\Str::of($old('fecha_nacimiento'))->substr(0,10) : '' }}"
                        max="{{ date('Y-m-d') }}" required>
                    <div class="invalid-feedback" id="err-fecha_nacimiento"></div>
                </div>
                <div class="col-md-3">
                    <label for="sexo" class="form-label">Sexo <span class="req">*</span></label>
                    <select id="sexo" name="sexo" class="form-select" required>
                        <option value="">…</option>
                        <option value="M" @selected($old('sexo')==='M' )>Masculino</option>
                        <option value="F" @selected($old('sexo')==='F' )>Femenino</option>
                        <option value="Indeterminado" @selected($old('sexo')==='Indeterminado' )>Indeterminado</option>
                    </select>
                    <div class="invalid-feedback" id="err-sexo"></div>
                </div>
                <div class="col-md-6">
                    <label for="curp" class="form-label">CURP</label>
                    <input type="text" id="curp" name="curp" class="form-control mono text-uppercase"
                        value="{{ $old('curp') }}" maxlength="18" placeholder="18 caracteres">
                    <div class="invalid-feedback" id="err-curp"></div>
                </div>

                <div class="col-md-4">
                    <label for="id_tipo_sangre" class="form-label">Tipo de sangre</label>
                    <select id="id_tipo_sangre" name="id_tipo_sangre" class="form-select">
                        <option value="">—</option>
                        @foreach($tiposSangre as $ts)
                        <option value="{{ $ts->id_tipo_sangre }}" @selected((int)$old('id_tipo_sangre')===$ts->id_tipo_sangre)>
                            {{ $ts->descripcion }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="id_estado_civil" class="form-label">Estado civil</label>
                    <select id="id_estado_civil" name="id_estado_civil" class="form-select">
                        <option value="">—</option>
                        @foreach($estadosCiviles as $ec)
                        <option value="{{ $ec->id_estado_civil }}" @selected((int)$old('id_estado_civil')===$ec->id_estado_civil)>
                            {{ $ec->descripcion }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="id_escolaridad" class="form-label">Escolaridad</label>
                    <select id="id_escolaridad" name="id_escolaridad" class="form-select">
                        <option value="">—</option>
                        @foreach($escolaridades as $e)
                        <option value="{{ $e->id_escolaridad }}" @selected((int)$old('id_escolaridad')===$e->id_escolaridad)>
                            {{ $e->descripcion }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label for="ocupacion" class="form-label">Ocupación</label>
                    <input type="text" id="ocupacion" name="ocupacion" class="form-control" value="{{ $old('ocupacion') }}">
                </div>
                <div class="col-md-3">
                    <label for="religion" class="form-label">Religión</label>
                    <input type="text" id="religion" name="religion" class="form-control" value="{{ $old('religion') }}">
                </div>
                <div class="col-md-3">
                    <label for="etnia" class="form-label">Etnia</label>
                    <input type="text" id="etnia" name="etnia" class="form-control" value="{{ $old('etnia') }}">
                </div>

                <input type="hidden" name="id_establecimiento"
                    value="{{ $old('id_establecimiento', $establecimientos->first()->id_establecimiento ?? 1) }}">
            </div>
        </div>
    </div>

    {{-- ── Contacto y domicilio ─────────────────────────────────────────── --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon"><i class="fa-solid fa-location-dot"></i></div>
            <div>
                <p class="sec-title">Contacto y domicilio</p>
                <p class="sec-subtitle">Datos de localización</p>
            </div>
        </div>
        <div class="sec-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control mono" value="{{ $old('telefono') }}">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Correo electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ $old('email') }}">
                    <div class="invalid-feedback" id="err-email"></div>
                </div>
                <div class="col-12">
                    <label for="domicilio" class="form-label">Domicilio</label>
                    <input type="text" id="domicilio" name="domicilio" class="form-control"
                        value="{{ $old('domicilio') }}" placeholder="Calle y número">
                </div>
                <div class="col-md-4">
                    <label for="colonia" class="form-label">Colonia</label>
                    <input type="text" id="colonia" name="colonia" class="form-control" value="{{ $old('colonia') }}">
                </div>
                <div class="col-md-4">
                    <label for="municipio" class="form-label">Municipio</label>
                    <input type="text" id="municipio" name="municipio" class="form-control" value="{{ $old('municipio') }}">
                </div>
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <input type="text" id="estado" name="estado" class="form-control" value="{{ $old('estado') }}">
                </div>
                <div class="col-md-2">
                    <label for="cp" class="form-label">C.P.</label>
                    <input type="text" id="cp" name="cp" class="form-control mono" value="{{ $old('cp') }}" maxlength="5">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Datos clínicos básicos ───────────────────────────────────────── --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon" style="background:var(--danger)"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
                <p class="sec-title">Alertas clínicas</p>
                <p class="sec-subtitle">Información crítica visible en todo el expediente</p>
            </div>
        </div>
        <div class="sec-body">
            <label for="alergias_conocidas" class="form-label">Alergias conocidas</label>
            <textarea id="alergias_conocidas" name="alergias_conocidas" class="form-control" rows="2"
                placeholder="Ej. Penicilina, AINEs, látex… (dejar vacío si no se conocen)">{{ $old('alergias_conocidas') }}</textarea>
        </div>
    </div>

    {{-- ── Contactos de emergencia (repeater) ───────────────────────────── --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon" style="background:#7c3aed"><i class="fa-solid fa-phone-volume"></i></div>
            <div>
                <p class="sec-title">Contactos de emergencia</p>
                <p class="sec-subtitle">Personas a contactar en caso necesario</p>
            </div>
        </div>
        <div class="sec-body">
            <div id="contactos-list">
                @php($contactos = old('contactos', $esEdicion ? $p->contactosEmergencia->toArray() : []))
                @forelse($contactos as $i => $c)
                @include('pacientes._contacto_row', ['i' => $i, 'c' => $c])
                @empty
                @include('pacientes._contacto_row', ['i' => 0, 'c' => []])
                @endforelse
            </div>
            <button type="button" class="btn-prev mt-2" onclick="App.pacForm.addContacto()">
                <i class="fa-solid fa-plus"></i> Agregar contacto
            </button>
        </div>
    </div>

    {{-- ── Medicamentos actuales (repeater) ─────────────────────────────── --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon" style="background:var(--info)"><i class="fa-solid fa-pills"></i></div>
            <div>
                <p class="sec-title">Medicamentos actuales</p>
                <p class="sec-subtitle">Tratamiento farmacológico en curso</p>
            </div>
        </div>
        <div class="sec-body">
            <div id="medicamentos-list">
                @php($meds = old('medicamentos', $esEdicion ? $p->medicamentos->toArray() : []))
                @forelse($meds as $i => $m)
                @include('pacientes._medicamento_row', ['i' => $i, 'm' => $m, 'medicamentos' => $medicamentos])
                @empty
                @include('pacientes._medicamento_row', ['i' => 0, 'm' => [], 'medicamentos' => $medicamentos])
                @endforelse
            </div>
            <button type="button" class="btn-prev mt-2" onclick="App.pacForm.addMedicamento()">
                <i class="fa-solid fa-plus"></i> Agregar medicamento
            </button>
        </div>
    </div>

    {{-- ── Barra de acciones ────────────────────────────────────────────── --}}
    <div class="nav-bar">
        <a href="{{ $esEdicion ? route('pacientes.show', $p->id_paciente) : route('pacientes.index') }}"
            class="btn-prev text-decoration-none">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
        <button type="submit" class="btn-next" id="btn-guardar">
            <i class="fa-solid fa-floppy-disk"></i>
            {{ $esEdicion ? 'Guardar' : 'Registrar paciente' }}
        </button>
    </div>
</form>

{{-- Plantillas para los repeaters (clonadas por JS) --}}
<template id="tpl-contacto">
    @include('pacientes._contacto_row', ['i' => '__IDX__', 'c' => []])
</template>
<template id="tpl-medicamento">
    @include('pacientes._medicamento_row', ['i' => '__IDX__', 'm' => [], 'medicamentos' => $medicamentos])
</template>