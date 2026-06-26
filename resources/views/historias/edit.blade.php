{{--
    Vista: historias/edit.blade.php
    Ruta:  GET /pacientes/{paciente}/historia

    Wizard de 4 pasos para la Historia Clínica (1:1 con el paciente):
      1. Antecedentes
      2. Padecimiento actual + exploración + signos vitales
      3. Diagnóstico, plan y pronóstico
      4. Extensión dinámica según tipo_paciente (desde config/hc_subtipos)

    Submit: PUT /api/v1/pacientes/{id}/historia vía App.hcWizard.
    Variables: $paciente, $historia (HistoriaClinica|null), $subtipo (model|null), $cfg (array)
--}}
@extends('main')

@section('title', 'Historia clínica · ' . $paciente->nombre_completo)

@php
// Helper para leer un valor del padre existente.
$hc = fn($campo, $def = '') => old($campo, $historia->$campo ?? $def);
// Valor de un campo de subtipo.
$st = fn($campo, $def = null) => old("subtipo.$campo", $subtipo->$campo ?? $def);
$bool = fn($campo) => (bool) old($campo, $historia->$campo ?? false);

$tipoCfg = match($paciente->tipo_paciente) {
'Quirúrgico' => ['c'=>'#d97706','i'=>'fa-user-doctor'],
'Neurológico' => ['c'=>'#0891b2','i'=>'fa-brain'],
'Geriátrico' => ['c'=>'#16a34a','i'=>'fa-person-cane'],
default => ['c'=>'#64748b','i'=>'fa-user'],
};

// Tipos como 'Otro' no tienen campos de subtipo: el paso 4 no aplica.
$tieneExtension = ! empty($cfg['campos']);
$totalPasos = $tieneExtension ? 4 : 3;
@endphp

{{-- Barra de progreso pegajosa bajo el top-bar --}}
@section('header')
<div class="wizard-progress">
    <ul class="wizard-steps" id="wizard-steps">
        <li class="wstep active" data-step="1" onclick="App.hcWizard.go(1)">
            <span class="wstep-num">1</span>
            <span class="wstep-label">Antecedentes</span>
            <span class="wstep-sub">Heredofamiliares y personales</span>
        </li>
        <li class="wstep" data-step="2" onclick="App.hcWizard.go(2)">
            <span class="wstep-num">2</span>
            <span class="wstep-label">Padecimiento</span>
            <span class="wstep-sub">Actual y exploración</span>
        </li>
        <li class="wstep" data-step="3" onclick="App.hcWizard.go(3)">
            <span class="wstep-num">3</span>
            <span class="wstep-label">Diagnóstico</span>
            <span class="wstep-sub">Plan y pronóstico</span>
        </li>
        @if($tieneExtension)
        <li class="wstep" data-step="4" onclick="App.hcWizard.go(4)">
            <span class="wstep-num">4</span>
            <span class="wstep-label">{{ $cfg['ui']['titulo'] ?? 'Extensión' }}</span>
            <span class="wstep-sub">Específico del tipo</span>
        </li>
        @endif
    </ul>
</div>
@endsection

@section('content')
{{-- Cabecera del paciente --}}
<div class="mb-3">
    <a href="{{ route('pacientes.show', $paciente->id_paciente) }}" class="text-decoration-none small text-muted">
        <i class="fa-solid fa-arrow-left"></i> {{ $paciente->nombre_completo }}
    </a>
    <div class="d-flex align-items-center gap-2 mt-1">
        <h1 class="h5 mb-0" style="font-weight:700;color:var(--slate)">Historia clínica</h1>
        <span class="tipo-pill" style="color:{{ $tipoCfg['c'] }};background:{{ $tipoCfg['c'] }}14;border-color:{{ $tipoCfg['c'] }}33">
            <i class="fa-solid {{ $tipoCfg['i'] }}" style="font-size:.6rem"></i> {{ $paciente->tipo_paciente }}
        </span>
        @if($historia)
        <span class="badge-inactive" style="background:var(--teal-light);color:var(--teal-dark)">
            <i class="fa-solid fa-circle-check"></i> Existente
        </span>
        @endif
    </div>
</div>

<form id="hc-form" data-paciente="{{ $paciente->id_paciente }}" data-total="{{ $totalPasos }}" novalidate>
    @csrf

    {{-- ═══════════════ PASO 1: ANTECEDENTES ═══════════════ --}}
    <div class="step-panel active" data-panel="1">
        {{-- Heredofamiliares --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-people-roof"></i></div>
                <div>
                    <p class="sec-title">Antecedentes heredofamiliares</p>
                    <p class="sec-subtitle">Enfermedades en familiares directos</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="bool-grid mb-3">
                    @foreach([
                    'ant_hf_diabetes'=>['Diabetes','fa-droplet'],
                    'ant_hf_hipertension'=>['Hipertensión','fa-heart-pulse'],
                    'ant_hf_cardiopatia'=>['Cardiopatía','fa-heart'],
                    'ant_hf_cancer'=>['Cáncer','fa-disease'],
                    ] as $campo => $d)
                    <div class="campo-bool">
                        <input type="hidden" name="{{ $campo }}" id="{{ $campo }}" value="{{ $bool($campo) ? 1 : 0 }}">
                        <div class="bool-card {{ $bool($campo) ? 'selected' : '' }}"
                            onclick="App.hcWizard.toggleBool(this, '{{ $campo }}')">
                            <i class="bool-icon fa-solid {{ $d[1] }}"></i>
                            <span class="bool-label">{{ $d[0] }}</span>
                            <i class="bool-check fa-solid fa-circle-check"></i>
                        </div>
                    </div>
                    @endforeach
                </div>
                <label for="ant_hf_otros" class="form-label">Otros antecedentes heredofamiliares</label>
                <input type="text" id="ant_hf_otros" name="ant_hf_otros" class="form-control" value="{{ $hc('ant_hf_otros') }}">
            </div>
        </div>

        {{-- No patológicos --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-leaf"></i></div>
                <div>
                    <p class="sec-title">Antecedentes personales no patológicos</p>
                    <p class="sec-subtitle">Estilo de vida y hábitos</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="row g-3">
                    @foreach([
                    'tabaquismo'=>['Tabaquismo','fa-smoking','tabaquismo_detalle'],
                    'alcoholismo'=>['Alcoholismo','fa-wine-bottle','alcoholismo_detalle'],
                    'toxicomanias'=>['Toxicomanías','fa-cannabis','toxicomanias_detalle'],
                    ] as $campo => $d)
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="campo-bool">
                                <input type="hidden" name="{{ $campo }}" id="{{ $campo }}" value="{{ $bool($campo) ? 1 : 0 }}">
                                <div class="bool-card {{ $bool($campo) ? 'selected' : '' }}"
                                    onclick="App.hcWizard.toggleBool(this, '{{ $campo }}', '{{ $d[2] }}')">
                                    <i class="bool-icon fa-solid {{ $d[1] }}"></i>
                                    <span class="bool-label">{{ $d[0] }}</span>
                                    <i class="bool-check fa-solid fa-circle-check"></i>
                                </div>
                            </div>
                            <input type="text" name="{{ $d[2] }}" id="{{ $d[2] }}"
                                class="form-control flex-grow-1 campo-detalle {{ $bool($campo) ? '' : 'campo-oculto' }}"
                                style="max-width:60%" placeholder="Detalle…" value="{{ $hc($d[2]) }}">
                        </div>
                    </div>
                    @endforeach
                    <div class="col-md-6">
                        <label for="actividad_fisica" class="form-label">Actividad física</label>
                        <input type="text" id="actividad_fisica" name="actividad_fisica" class="form-control" value="{{ $hc('actividad_fisica') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="dieta" class="form-label">Dieta</label>
                        <input type="text" id="dieta" name="dieta" class="form-control" value="{{ $hc('dieta') }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Patológicos --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-notes-medical"></i></div>
                <div>
                    <p class="sec-title">Antecedentes personales patológicos</p>
                    <p class="sec-subtitle">Historial médico previo</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="enfermedades_previas" class="form-label">Enfermedades previas</label>
                        <textarea id="enfermedades_previas" name="enfermedades_previas" class="form-control" rows="2">{{ $hc('enfermedades_previas') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="cirugias_previas" class="form-label">Cirugías previas</label>
                        <textarea id="cirugias_previas" name="cirugias_previas" class="form-control" rows="2">{{ $hc('cirugias_previas') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="hospitalizaciones_previas" class="form-label">Hospitalizaciones previas</label>
                        <textarea id="hospitalizaciones_previas" name="hospitalizaciones_previas" class="form-control" rows="2">{{ $hc('hospitalizaciones_previas') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="traumatismos_previos" class="form-label">Traumatismos previos</label>
                        <textarea id="traumatismos_previos" name="traumatismos_previos" class="form-control" rows="2">{{ $hc('traumatismos_previos') }}</textarea>
                    </div>
                    <div class="col-12">
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="campo-bool">
                                <input type="hidden" name="transfusiones" id="transfusiones" value="{{ $bool('transfusiones') ? 1 : 0 }}">
                                <div class="bool-card {{ $bool('transfusiones') ? 'selected' : '' }}"
                                    onclick="App.hcWizard.toggleBool(this, 'transfusiones', 'transfusiones_detalle')">
                                    <i class="bool-icon fa-solid fa-droplet"></i>
                                    <span class="bool-label">Transfusiones</span>
                                    <i class="bool-check fa-solid fa-circle-check"></i>
                                </div>
                            </div>
                            <input type="text" name="transfusiones_detalle" id="transfusiones_detalle"
                                class="form-control flex-grow-1 campo-detalle {{ $bool('transfusiones') ? '' : 'campo-oculto' }}"
                                style="max-width:60%" placeholder="Detalle…" value="{{ $hc('transfusiones_detalle') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gineco-obstétricos (solo si sexo F) --}}
        @if($paciente->sexo === 'F')
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon" style="background:#db2777"><i class="fa-solid fa-venus"></i></div>
                <div>
                    <p class="sec-title">Antecedentes gineco-obstétricos</p>
                    <p class="sec-subtitle">Historia reproductiva</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="row g-3">
                    <div class="col-md-3"><label class="form-label">Menarca (años)</label>
                        <input type="number" name="menarca" class="form-control" value="{{ $hc('menarca') }}" min="5" max="20">
                    </div>
                    <div class="col-md-3"><label class="form-label">Ciclos menstruales</label>
                        <input type="text" name="ciclos_menstruales" class="form-control" value="{{ $hc('ciclos_menstruales') }}">
                    </div>
                    <div class="col-md-3"><label class="form-label">Última regla</label>
                        <input type="date" name="fecha_ultima_regla" class="form-control" value="{{ $hc('fecha_ultima_regla') ? \Illuminate\Support\Str::of($hc('fecha_ultima_regla'))->substr(0,10) : '' }}">
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <div><label class="form-label">Gestas</label><input type="number" name="gestas" class="form-control" value="{{ $hc('gestas') }}" min="0"></div>
                        <div><label class="form-label">Partos</label><input type="number" name="partos" class="form-control" value="{{ $hc('partos') }}" min="0"></div>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <div><label class="form-label">Cesáreas</label><input type="number" name="cesareas" class="form-control" value="{{ $hc('cesareas') }}" min="0"></div>
                        <div><label class="form-label">Abortos</label><input type="number" name="abortos" class="form-control" value="{{ $hc('abortos') }}" min="0"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ═══════════════ PASO 2: PADECIMIENTO ═══════════════ --}}
    <div class="step-panel" data-panel="2">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-comment-medical"></i></div>
                <div>
                    <p class="sec-title">Padecimiento actual</p>
                    <p class="sec-subtitle">Motivo de consulta y evolución</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="mb-3">
                    <label for="motivo_consulta" class="form-label">Motivo de consulta <span class="req">*</span></label>
                    <textarea id="motivo_consulta" name="motivo_consulta" class="form-control" rows="2" required>{{ $hc('motivo_consulta') }}</textarea>
                    <div class="invalid-feedback" id="err-motivo_consulta"></div>
                </div>
                <div>
                    <label for="padecimiento_actual" class="form-label">Padecimiento actual <span class="req">*</span></label>
                    <textarea id="padecimiento_actual" name="padecimiento_actual" class="form-control" rows="4" required>{{ $hc('padecimiento_actual') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Signos vitales + somatometría --}}
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon" style="background:var(--info)"><i class="fa-solid fa-heart-pulse"></i></div>
                <div>
                    <p class="sec-title">Signos vitales y somatometría</p>
                    <p class="sec-subtitle">El IMC se calcula automáticamente</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="sv-grid">
                    <div>
                        <div class="sv-label">Peso (kg)</div>
                        <input type="number" step="0.1" id="peso_kg" name="peso_kg" class="form-control" value="{{ $hc('peso_kg') }}" oninput="App.hcWizard.calcImc()">
                    </div>
                    <div>
                        <div class="sv-label">Talla (cm)</div>
                        <input type="number" step="0.1" id="talla_cm" name="talla_cm" class="form-control" value="{{ $hc('talla_cm') }}" oninput="App.hcWizard.calcImc()">
                    </div>
                    <div>
                        <div class="sv-label">IMC</div>
                        <div class="imc-badge" id="imc-badge"><span id="imc-val">—</span></div>
                    </div>
                    <div>
                        <div class="sv-label">T/A</div>
                        <input type="text" id="presion_arterial" name="presion_arterial" class="form-control" value="{{ $hc('presion_arterial') }}" placeholder="120/80">
                    </div>
                    <div>
                        <div class="sv-label">FC (lpm)</div>
                        <input type="number" name="frecuencia_cardiaca" class="form-control" value="{{ $hc('frecuencia_cardiaca') }}">
                    </div>
                    <div>
                        <div class="sv-label">FR (rpm)</div>
                        <input type="number" name="frecuencia_respiratoria" class="form-control" value="{{ $hc('frecuencia_respiratoria') }}">
                    </div>
                    <div>
                        <div class="sv-label">Temp (°C)</div>
                        <input type="number" step="0.1" name="temperatura_c" class="form-control" value="{{ $hc('temperatura_c') }}">
                    </div>
                    <div>
                        <div class="sv-label">SatO₂ (%)</div>
                        <input type="number" name="saturacion_o2" class="form-control" value="{{ $hc('saturacion_o2') }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-stethoscope"></i></div>
                <div>
                    <p class="sec-title">Exploración física</p>
                </div>
            </div>
            <div class="sec-body">
                <textarea id="exploracion_fisica" name="exploracion_fisica" class="form-control" rows="4">{{ $hc('exploracion_fisica') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════════════ PASO 3: DIAGNÓSTICO ═══════════════ --}}
    <div class="step-panel" data-panel="3">
        <div class="sec-card">
            <div class="sec-header">
                <div class="sec-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                <div>
                    <p class="sec-title">Diagnóstico y plan</p>
                    <p class="sec-subtitle">Impresión diagnóstica y manejo</p>
                </div>
            </div>
            <div class="sec-body">
                <div class="mb-3">
                    <label for="diagnostico_inicial" class="form-label">Diagnóstico inicial <span class="req">*</span></label>
                    <textarea id="diagnostico_inicial" name="diagnostico_inicial" class="form-control" rows="3" required>{{ $hc('diagnostico_inicial') }}</textarea>
                    <div class="invalid-feedback" id="err-diagnostico_inicial"></div>
                </div>
                <div class="mb-3">
                    <label for="plan_manejo" class="form-label">Plan de manejo <span class="req">*</span></label>
                    <textarea id="plan_manejo" name="plan_manejo" class="form-control" rows="3" required>{{ $hc('plan_manejo') }}</textarea>
                </div>
                <div>
                    <label for="pronostico" class="form-label">Pronóstico</label>
                    <textarea id="pronostico" name="pronostico" class="form-control" rows="2">{{ $hc('pronostico') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════ PASO 4: EXTENSIÓN DINÁMICA ═══════════════ --}}
    @if($tieneExtension)
    <div class="step-panel" data-panel="4">
        <div class="ext-panel {{ $cfg['ui']['clase'] }}">
            <div class="ext-title">
                <i class="fa-solid {{ $cfg['ui']['icono'] }}"></i>
                {{ $cfg['ui']['titulo'] }}
            </div>
            <div class="row g-3">
                @foreach($cfg['campos'] as $campo => $meta)
                @include('historias._campo_subtipo', [
                'campo' => $campo,
                'meta' => $meta,
                'valor' => $st($campo),
                ])
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Barra de navegación --}}
    <div class="nav-bar">
        <button type="button" class="btn-prev" id="btn-prev" onclick="App.hcWizard.prev()" disabled>
            <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-md-block">Anterior</span>
        </button>
        <div class="step-info">
            Paso <strong id="cur-step">1</strong> de 4
        </div>
        <button type="button" class="btn-next" id="btn-next" onclick="App.hcWizard.next()">
            <span class="d-none d-md-block">Siguiente</span> <i class="fa-solid fa-arrow-right"></i>
        </button>
        <button type="submit" class="btn-next" id="btn-save" style="display:none">
            <i class="fa-solid fa-floppy-disk"></i> <span class="d-none d-md-block">Guardar historia clínica</span>
        </button>
    </div>
</form>
@endsection

@push('scripts')
@vite('resources/js/hc-wizard.js')
@endpush