{{--
    Partial: pacientes/_medicamento_row.blade.php
    Fila de medicamento. $i = índice, $m = datos, $medicamentos = catálogo.
--}}
<div class="repeater-row" data-repeater="medicamento">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Medicamento</label>
            <select name="medicamentos[{{ $i }}][id_medicamento]" class="form-select">
                <option value="">Selecciona…</option>
                @foreach($medicamentos as $med)
                    <option value="{{ $med->id_medicamento }}"
                        @selected((int)($m['id_medicamento'] ?? 0) === $med->id_medicamento)>
                        {{ $med->nombre_generico }}{{ $med->concentracion ? ' · '.$med->concentracion : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Dosis</label>
            <input type="text" name="medicamentos[{{ $i }}][dosis]" class="form-control"
                   value="{{ $m['dosis'] ?? '' }}" placeholder="Ej. 500 mg">
        </div>
        <div class="col-md-3">
            <label class="form-label">Frecuencia</label>
            <input type="text" name="medicamentos[{{ $i }}][frecuencia]" class="form-control"
                   value="{{ $m['frecuencia'] ?? '' }}" placeholder="Ej. Cada 8 h">
        </div>
        <div class="col-md-1">
            <label class="form-label">Inicio</label>
            <input type="date" name="medicamentos[{{ $i }}][fecha_inicio]" class="form-control"
                   value="{{ isset($m['fecha_inicio']) ? \Illuminate\Support\Str::of($m['fecha_inicio'])->substr(0,10) : '' }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn-icon btn-icon-danger" onclick="App.pacForm.removeRow(this)" title="Quitar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
</div>
