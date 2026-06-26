{{--
    Partial: historias/_campo_subtipo.blade.php

    Renderiza UN campo del subtipo a partir de su metadata en config/hc_subtipos.
    Es el corazón del paso 4 dinámico: el mismo partial sirve para quirúrgico,
    neurológico y geriátrico; lo único que cambia son los datos de config.

    Variables:
      $campo  (string)  nombre del campo → name="subtipo[campo]"
      $meta   (array)   metadata del campo desde config
      $valor  (mixed)   valor actual (de la extensión hc_* existente) o null
--}}
@php
$name = "subtipo[{$campo}]";
$id = "st_{$campo}";
$col = $meta['col'] ?? 'col-md-6';
$oculto = !empty($meta['oculto']);
@endphp

@switch($meta['tipo'])

{{-- ── TEXT ─────────────────────────────────────────────────────────── --}}
@case('text')
<div class="{{ $col }} campo-subtipo {{ $oculto ? 'campo-oculto' : '' }}" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <label for="{{ $id }}" class="form-label">{{ $meta['label'] }}</label>
    <input type="text" id="{{ $id }}" name="{{ $name }}" class="form-control"
        value="{{ $valor }}" placeholder="{{ $meta['placeholder'] ?? '' }}">
</div>
@break

{{-- ── TEXTAREA ─────────────────────────────────────────────────────── --}}
@case('textarea')
<div class="{{ $col }} campo-subtipo {{ $oculto ? 'campo-oculto' : '' }}" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <label for="{{ $id }}" class="form-label">{{ $meta['label'] }}</label>
    <textarea id="{{ $id }}" name="{{ $name }}" class="form-control" rows="{{ $meta['rows'] ?? 2 }}"
        placeholder="{{ $meta['placeholder'] ?? '' }}">{{ $valor }}</textarea>
</div>
@break

{{-- ── NUMBER ───────────────────────────────────────────────────────── --}}
@case('number')
<div class="{{ $col }} campo-subtipo {{ $oculto ? 'campo-oculto' : '' }}" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <label for="{{ $id }}" class="form-label">{{ $meta['label'] }}</label>
    <input type="number" id="{{ $id }}" name="{{ $name }}" class="form-control"
        value="{{ $valor }}"
        @isset($meta['min']) min="{{ $meta['min'] }}" @endisset
        @isset($meta['max']) max="{{ $meta['max'] }}" @endisset
        @isset($meta['onchange']) oninput="{{ $meta['onchange'] }}" @endisset>
    @isset($meta['badge'])
    <span id="{{ $meta['badge'] }}" class="subtipo-badge" style="display:none"></span>
    @endisset
</div>
@break

{{-- ── SELECT ───────────────────────────────────────────────────────── --}}
@case('select')
<div class="{{ $col }} campo-subtipo {{ $oculto ? 'campo-oculto' : '' }}" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <label for="{{ $id }}" class="form-label">{{ $meta['label'] }}</label>
    <select id="{{ $id }}" name="{{ $name }}" class="form-select">
        <option value="">—</option>
        @foreach($meta['opciones'] ?? [] as $val => $txt)
        <option value="{{ $val }}" @selected((string)$valor===(string)$val)>{{ $txt }}</option>
        @endforeach
    </select>
</div>
@break

{{-- ── SCALE (number + etiqueta interpretativa en vivo) ─────────────── --}}
@case('scale')
<div class="{{ $col }} campo-subtipo {{ $oculto ? 'campo-oculto' : '' }}" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <label for="{{ $id }}" class="form-label">{{ $meta['label'] }}</label>
    <div class="scale-wrap">
        <input type="number" id="{{ $id }}" name="{{ $name }}" class="form-control"
            value="{{ $valor }}"
            @isset($meta['min']) min="{{ $meta['min'] }}" @endisset
            @isset($meta['max']) max="{{ $meta['max'] }}" @endisset
            data-scale='@json($meta[' ranges'] ?? [])'
            oninput="App.hcWizard.updateScale(this)">
        <span class="scale-display" id="{{ $id }}_lbl"></span>
    </div>
</div>
@break

{{-- ── BOOL (bool-card toggle) ──────────────────────────────────────── --}}
@case('bool')
@php
$onclick = isset($meta['detalle'])
? "App.hcWizard.toggleBool(this, '{$id}', 'st_{$meta['detalle']}')"
: "App.hcWizard.toggleBool(this, '{$id}')";
@endphp
<div class="col-auto campo-subtipo" id="st_{{ $campo }}" data-campo="{{ $campo }}">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" value="{{ $valor ? 1 : 0 }}">
    <div class="bool-card {{ $valor ? 'selected' : '' }}" onclick="{{ $onclick }}">
        <i class="bool-icon fa-solid {{ $meta['icono'] ?? 'fa-check' }}"></i>
        <span class="bool-label">{{ $meta['label'] }}</span>
        <i class="bool-check fa-solid fa-circle-check"></i>
    </div>
</div>
@break

@endswitch