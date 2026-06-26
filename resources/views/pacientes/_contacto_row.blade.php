{{--
    Partial: pacientes/_contacto_row.blade.php
    Fila de contacto de emergencia. $i = índice, $c = datos (array, puede estar vacío).
--}}
<div class="repeater-row" data-repeater="contacto">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Nombre completo</label>
            <input type="text" name="contactos[{{ $i }}][nombre_completo]" class="form-control"
                   value="{{ $c['nombre_completo'] ?? '' }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">Parentesco</label>
            <input type="text" name="contactos[{{ $i }}][parentesco]" class="form-control"
                   value="{{ $c['parentesco'] ?? '' }}" placeholder="Ej. Hijo, Cónyuge">
        </div>
        <div class="col-md-2">
            <label class="form-label">Teléfono</label>
            <input type="tel" name="contactos[{{ $i }}][telefono]" class="form-control mono"
                   value="{{ $c['telefono'] ?? '' }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Tel. alterno</label>
            <input type="tel" name="contactos[{{ $i }}][telefono_alt]" class="form-control mono"
                   value="{{ $c['telefono_alt'] ?? '' }}">
        </div>
        <div class="col-md-1">
            <button type="button" class="btn-icon btn-icon-danger" onclick="App.pacForm.removeRow(this)" title="Quitar">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
</div>
