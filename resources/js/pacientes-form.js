/*
|------------------------------------------------------------------------------
| resources/js/pacientes-form.js  —  App.pacForm
|------------------------------------------------------------------------------
|
| Lógica específica del formulario de paciente (create/edit). Consume la
| plomería compartida de window.App (post/put, toast, loading, serialize,
| showErrors) y solo añade lo propio de esta vista: los repeaters de contactos
| y medicamentos, y el submit que decide POST vs PUT según data-modo.
|
| Se carga solo en create.blade y edit.blade vía @vite.
*/

import App from './app.js';

const pacForm = {
    /* ── Selección de tipo de paciente (tarjetas) ────────────────────────── */
    selectTipo(card) {
        const value = card.dataset.value;

        // Marca visualmente la tarjeta elegida.
        document.querySelectorAll('.tipo-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');

        // Guarda el valor en el hidden y limpia el error.
        const hidden = document.getElementById('tipo_paciente');
        hidden.value = value;
        hidden.classList.remove('is-invalid');
        const err = document.getElementById('err-tipo_paciente');
        if (err) err.style.display = 'none';

        // Muestra el panel informativo correspondiente.
        const info = document.getElementById('tipo-info');
        if (info) {
            info.style.display = '';
            info.querySelectorAll('.tipo-info-box').forEach(box => {
                box.style.display = box.dataset.for === value ? '' : 'none';
            });
        }
    },

    /* ── Repeaters ───────────────────────────────────────────────────────── */
    _idx: { contacto: 0, medicamento: 0 },

    addContacto() {
        this._addRow('tpl-contacto', 'contactos-list', 'contacto');
    },

    addMedicamento() {
        this._addRow('tpl-medicamento', 'medicamentos-list', 'medicamento');
    },

    _addRow(tplId, listId, tipo) {
        const tpl = document.getElementById(tplId);
        const list = document.getElementById(listId);
        if (!tpl || !list) return;

        // Índice único creciente para los names array (contactos[N][...])
        this._idx[tipo] = Math.max(this._idx[tipo], this._countRows(listId)) + 1;
        const html = tpl.innerHTML.replaceAll('__IDX__', this._idx[tipo]);

        const wrap = document.createElement('div');
        wrap.innerHTML = html.trim();
        const row = wrap.firstElementChild;
        list.appendChild(row);
    },

    _countRows(listId) {
        return document.getElementById(listId)?.querySelectorAll('.repeater-row').length ?? 0;
    },

    removeRow(btn) {
        const row = btn.closest('.repeater-row');
        const list = row?.parentElement;
        row?.remove();
        // Si era la última fila, deja una vacía para que el usuario no se quede sin nada.
        if (list && list.querySelectorAll('.repeater-row').length === 0) {
            const tipo = list.id === 'contactos-list' ? 'contacto' : 'medicamento';
            const tplId = tipo === 'contacto' ? 'tpl-contacto' : 'tpl-medicamento';
            this._addRow(tplId, list.id, tipo);
        }
    },

    /* ── Submit ──────────────────────────────────────────────────────────── */
    async submit(e) {
        e.preventDefault();
        const form = e.target;
        App.clearErrors(form);

        // Validación local: el tipo de paciente es obligatorio.
        const tipo = document.getElementById('tipo_paciente');
        if (tipo && !tipo.value) {
            const err = document.getElementById('err-tipo_paciente');
            if (err) { err.textContent = 'Debe seleccionar el tipo de paciente.'; err.style.display = 'block'; }
            document.getElementById('sec-tipo')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            App.toast('error', 'Falta el tipo de paciente', 'Selecciona una de las tres opciones.');
            return;
        }

        const btn = document.getElementById('btn-guardar');
        App.loading(btn, true, 'Guardando…');

        // Serializa incluyendo arrays anidados (contactos[], medicamentos[]).
        const payload = pacForm._buildPayload(form);
        const modo = form.dataset.modo;
        const id   = form.dataset.id;

        try {
            const res = modo === 'edit'
                ? await App.put(`pacientes/${id}`, payload)
                : await App.post('pacientes', payload);

            App.toast('success', modo === 'edit' ? 'Cambios guardados' : 'Paciente registrado', 'Redirigiendo…');
            setTimeout(() => window.location.href = res.data.redirect_url, 900);
        } catch (err) {
            App.loading(btn, false);
            if (err instanceof App.ApiError && err.status === 422) {
                App.showErrors(err.errors);
                App.toast('error', 'Revisa el formulario', 'Hay campos que necesitan corrección.');
            } else {
                App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
            }
        }
    },

    /**
     * Construye el payload respetando los arrays anidados. FormData aplana los
     * names tipo contactos[0][telefono]; los re-agrupamos en arrays de objetos.
     */
    _buildPayload(form) {
        const fd = new FormData(form);
        const out = {};
        const nested = {}; // { contactos: { 0: {...} }, medicamentos: {...} }

        for (const [key, value] of fd.entries()) {
            if (key === '_token') continue;

            const m = key.match(/^(\w+)\[(\d+)\]\[(\w+)\]$/);
            if (m) {
                const [, grupo, idx, campo] = m;
                nested[grupo] ??= {};
                nested[grupo][idx] ??= {};
                nested[grupo][idx][campo] = value;
            } else if (value !== '') {
                out[key] = value;
            }
        }

        // Convierte los grupos a arrays, descartando filas totalmente vacías.
        for (const grupo in nested) {
            out[grupo] = Object.values(nested[grupo]).filter(row =>
                Object.values(row).some(v => v !== '' && v != null)
            );
        }
        return out;
    },
};

App.pacForm = pacForm;

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('paciente-form');
    if (!form) return;

    App.bindClearOnInput(form);
    form.addEventListener('submit', pacForm.submit);

    // Auto-uppercase de CURP
    document.getElementById('curp')?.addEventListener('input', (e) => {
        e.target.value = e.target.value.toUpperCase();
    });
});

export default pacForm;
