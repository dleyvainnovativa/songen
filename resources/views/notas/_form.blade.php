{{--
    Partial: notas/_form.blade.php

    Formulario compartido por create y edit. El modo lo determina $nota.
    La sección quirúrgica se muestra solo cuando el tipo de nota seleccionado
    es Preoperatoria o Postoperatoria (App.notaForm lo controla en vivo).

    Una nota firmada no llega aquí en modo edición (la vista edit redirige a
    show si está firmada), así que el form siempre es editable.

    Variables: $paciente, $nota (NotaMedica|null), $tiposNota, $medicos
--}}
@php
    $n = $nota ?? null;
    $esEdicion = (bool) $n;
    $nv = fn($campo, $def = '') => old($campo, $n->$campo ?? $def);
    $qx = $esEdicion && $n->notaQuirurgica ? $n->notaQuirurgica : null;
    $qv = fn($campo, $def = '') => old("quirurgica.$campo", $qx->$campo ?? $def);

    // IDs de tipos quirúrgicos (para activar la sección): por descripción.
    $tiposQx = $tiposNota->whereIn('descripcion', ['Preoperatoria','Postoperatoria'])->pluck('id_tipo_nota')->all();
    $tipoActual = (int) $nv('id_tipo_nota');
    $mostrarQx = in_array($tipoActual, $tiposQx, true);
@endphp

<form id="nota-form"
      data-modo="{{ $esEdicion ? 'edit' : 'create' }}"
      data-paciente="{{ $paciente->id_paciente }}"
      @if($esEdicion) data-id="{{ $n->id_nota }}" @endif
      data-tipos-qx="{{ implode(',', $tiposQx) }}"
      novalidate>
    @csrf

    {{-- Tipo y fecha --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon"><i class="fa-solid fa-file-medical"></i></div>
            <div>
                <p class="sec-title">Datos de la nota</p>
                <p class="sec-subtitle">Tipo y momento de la atención</p>
            </div>
        </div>
        <div class="sec-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="id_tipo_nota" class="form-label">Tipo de nota <span class="req">*</span></label>
                    <select id="id_tipo_nota" name="id_tipo_nota" class="form-select" required
                            onchange="App.notaForm.onTipoChange()">
                        <option value="">Selecciona…</option>
                        @foreach($tiposNota as $t)
                            <option value="{{ $t->id_tipo_nota }}" @selected($tipoActual === $t->id_tipo_nota)>
                                {{ $t->descripcion }}
                            </option>
                        @endforeach
                    </select>
                    <div class="invalid-feedback" id="err-id_tipo_nota"></div>
                </div>
                <div class="col-md-6">
                    <label for="fecha_hora" class="form-label">Fecha y hora</label>
                    <input type="datetime-local" id="fecha_hora" name="fecha_hora" class="form-control"
                           value="{{ $nv('fecha_hora') ? \Illuminate\Support\Carbon::parse($nv('fecha_hora'))->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- SOAP --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon"><i class="fa-solid fa-list-check"></i></div>
            <div>
                <p class="sec-title">Nota SOAP</p>
                <p class="sec-subtitle">Subjetivo · Objetivo · Análisis · Plan</p>
            </div>
        </div>
        <div class="sec-body">
            <div class="soap-field">
                <span class="soap-letter soap-s">S</span>
                <div class="flex-grow-1">
                    <label for="subjetivo" class="form-label">Subjetivo</label>
                    <textarea id="subjetivo" name="subjetivo" class="form-control" rows="3"
                              placeholder="Lo que refiere el paciente…">{{ $nv('subjetivo') }}</textarea>
                </div>
            </div>
            <div class="soap-field">
                <span class="soap-letter soap-o">O</span>
                <div class="flex-grow-1">
                    <label for="objetivo" class="form-label">Objetivo</label>
                    <textarea id="objetivo" name="objetivo" class="form-control" rows="3"
                              placeholder="Hallazgos de la exploración…">{{ $nv('objetivo') }}</textarea>
                </div>
            </div>
            <div class="soap-field">
                <span class="soap-letter soap-a">A</span>
                <div class="flex-grow-1">
                    <label for="analisis" class="form-label">Análisis <span class="req">*</span></label>
                    <textarea id="analisis" name="analisis" class="form-control" rows="3" required
                              placeholder="Impresión diagnóstica / evolución…">{{ $nv('analisis') }}</textarea>
                    <div class="invalid-feedback" id="err-analisis"></div>
                </div>
            </div>
            <div class="soap-field">
                <span class="soap-letter soap-p">P</span>
                <div class="flex-grow-1">
                    <label for="plan" class="form-label">Plan</label>
                    <textarea id="plan" name="plan" class="form-control" rows="3"
                              placeholder="Indicaciones, tratamiento, seguimiento…">{{ $nv('plan') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Signos vitales --}}
    <div class="sec-card">
        <div class="sec-header">
            <div class="sec-icon" style="background:var(--info)"><i class="fa-solid fa-heart-pulse"></i></div>
            <div><p class="sec-title">Signos vitales</p></div>
        </div>
        <div class="sec-body">
            <div class="sv-grid">
                <div><div class="sv-label">T/A</div>
                    <input type="text" name="presion_arterial" class="form-control" value="{{ $nv('presion_arterial') }}" placeholder="120/80"></div>
                <div><div class="sv-label">FC (lpm)</div>
                    <input type="number" name="frecuencia_cardiaca" class="form-control" value="{{ $nv('frecuencia_cardiaca') }}"></div>
                <div><div class="sv-label">FR (rpm)</div>
                    <input type="number" name="frecuencia_respiratoria" class="form-control" value="{{ $nv('frecuencia_respiratoria') }}"></div>
                <div><div class="sv-label">Temp (°C)</div>
                    <input type="number" step="0.1" name="temperatura_c" class="form-control" value="{{ $nv('temperatura_c') }}"></div>
                <div><div class="sv-label">SatO₂ (%)</div>
                    <input type="number" name="saturacion_o2" class="form-control" value="{{ $nv('saturacion_o2') }}"></div>
                <div><div class="sv-label">Peso (kg)</div>
                    <input type="number" step="0.1" name="peso_kg" class="form-control" value="{{ $nv('peso_kg') }}"></div>
            </div>
        </div>
    </div>

    {{-- Extensión quirúrgica (se muestra solo si el tipo lo amerita) --}}
    <div class="sec-card" id="sec-quirurgica" style="{{ $mostrarQx ? '' : 'display:none' }}">
        <div class="sec-header" style="background:var(--qx-p)">
            <div class="sec-icon" style="background:var(--qx-c)"><i class="fa-solid fa-scalpel"></i></div>
            <div>
                <p class="sec-title" style="color:#92400e">Datos quirúrgicos</p>
                <p class="sec-subtitle">Detalle del procedimiento</p>
            </div>
        </div>
        <div class="sec-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Cirujano</label>
                    <select name="quirurgica[id_cirujano]" class="form-select">
                        <option value="">—</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id_medico }}" @selected((int)$qv('id_cirujano') === $m->id_medico)>{{ $m->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Anestesiólogo</label>
                    <select name="quirurgica[id_anestesiologo]" class="form-select">
                        <option value="">—</option>
                        @foreach($medicos as $m)
                            <option value="{{ $m->id_medico }}" @selected((int)$qv('id_anestesiologo') === $m->id_medico)>{{ $m->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Inicio</label>
                    <input type="datetime-local" name="quirurgica[fecha_hora_inicio]" class="form-control"
                           value="{{ $qv('fecha_hora_inicio') ? \Illuminate\Support\Carbon::parse($qv('fecha_hora_inicio'))->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fin</label>
                    <input type="datetime-local" name="quirurgica[fecha_hora_fin]" class="form-control"
                           value="{{ $qv('fecha_hora_fin') ? \Illuminate\Support\Carbon::parse($qv('fecha_hora_fin'))->format('Y-m-d\TH:i') : '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tipo de anestesia</label>
                    <input type="text" name="quirurgica[tipo_anestesia]" class="form-control" value="{{ $qv('tipo_anestesia') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dx preoperatorio</label>
                    <textarea name="quirurgica[diagnostico_preoperatorio]" class="form-control" rows="2">{{ $qv('diagnostico_preoperatorio') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Dx postoperatorio</label>
                    <textarea name="quirurgica[diagnostico_postoperatorio]" class="form-control" rows="2">{{ $qv('diagnostico_postoperatorio') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Procedimiento realizado</label>
                    <textarea name="quirurgica[procedimiento_realizado]" class="form-control" rows="2">{{ $qv('procedimiento_realizado') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Hallazgos</label>
                    <textarea name="quirurgica[hallazgos]" class="form-control" rows="2">{{ $qv('hallazgos') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Técnica</label>
                    <textarea name="quirurgica[tecnica]" class="form-control" rows="2">{{ $qv('tecnica') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Material implantado</label>
                    <textarea name="quirurgica[material_implantado]" class="form-control" rows="2">{{ $qv('material_implantado') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Complicaciones</label>
                    <textarea name="quirurgica[complicaciones]" class="form-control" rows="2">{{ $qv('complicaciones') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sangrado (ml)</label>
                    <input type="number" name="quirurgica[sangrado_ml]" class="form-control" value="{{ $qv('sangrado_ml') }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Diuresis (ml)</label>
                    <input type="number" name="quirurgica[diuresis_ml]" class="form-control" value="{{ $qv('diuresis_ml') }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estado al egreso</label>
                    <input type="text" name="quirurgica[estado_egreso]" class="form-control" value="{{ $qv('estado_egreso') }}">
                </div>
            </div>
        </div>
    </div>

    {{-- Acciones --}}
    <div class="nav-bar">
        <a href="{{ route('notas.index', $paciente->id_paciente) }}" class="btn-prev text-decoration-none">
            <i class="fa-solid fa-xmark"></i> Cancelar
        </a>
        <button type="submit" class="btn-next" id="btn-guardar">
            <i class="fa-solid fa-floppy-disk"></i>
            {{ $esEdicion ? 'Guardar cambios' : 'Crear nota' }}
        </button>
    </div>
</form>
