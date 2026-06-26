/*
|------------------------------------------------------------------------------
| resources/js/notas-form.js  —  App.notaForm
|------------------------------------------------------------------------------
|
| Lógica de notas médicas: submit (crear/editar), mostrar/ocultar la sección
| quirúrgica según el tipo de nota, y la acción de firmar (con confirmación,
| porque es irreversible).
|
| Se carga en notas/create, notas/edit y notas/show vía @vite.
*/

import App from './app.js';

const notaForm = {
    /* ── Mostrar sección quirúrgica según el tipo ────────────────────────── */
    onTipoChange() {
        const form = document.getElementById('nota-form');
        const select = document.getElementById('id_tipo_nota');
        const sec = document.getElementById('sec-quirurgica');
        if (!form || !select || !sec) return;

        const tiposQx = (form.dataset.tiposQx || '')
            .split(',').filter(Boolean).map(Number);
        const val = parseInt(select.value, 10);
        sec.style.display = tiposQx.includes(val) ? '' : 'none';
    },

    /* ── Submit (crear/editar) ───────────────────────────────────────────── */
    async submit(e) {
        e.preventDefault();
        const form = e.target;
        App.clearErrors(form);

        const btn = document.getElementById('btn-guardar');
        App.loading(btn, true, 'Guardando…');

        const payload = notaForm._buildPayload(form);
        const modo = form.dataset.modo;
        const pacienteId = form.dataset.paciente;
        const id = form.dataset.id;

        try {
            const res = modo === 'edit'
                ? await App.put(`pacientes/${pacienteId}/notas/${id}`, payload)
                : await App.post(`pacientes/${pacienteId}/notas`, payload);

            App.toast('success', modo === 'edit' ? 'Nota actualizada' : 'Nota creada', 'Redirigiendo…');
            setTimeout(() => window.location.href = res.data.redirect_url, 800);
        } catch (err) {
            App.loading(btn, false);
            if (err instanceof App.ApiError && err.status === 422) {
                App.showErrors(err.errors);
                // Errores de candado (nota / firma) van como toast, no por campo.
                if (err.errors.nota || err.errors.firma) {
                    App.toast('error', 'No editable', err.errors.nota?.[0] || err.errors.firma?.[0]);
                } else {
                    App.toast('error', 'Revisa el formulario', 'Hay campos que corregir.');
                }
            } else {
                App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
            }
        }
    },

    /* ── Firmar (irreversible → confirmación) ────────────────────────────── */
    async firmar(pacienteId, notaId) {
        const ok = await App.confirm({
            title: '¿Firmar esta nota?',
            body: 'Una vez firmada, la nota quedará bloqueada y no podrá modificarse. Esta acción es permanente.',
            confirmText: 'Sí, firmar',
            cancelText: 'Cancelar',
        });
        if (!ok) return;

        const btn = document.getElementById('btn-firmar');
        App.loading(btn, true, 'Firmando…');

        try {
            const res = await App.post(`pacientes/${pacienteId}/notas/${notaId}/firmar`, {});
            App.toast('success', 'Nota firmada', 'Ahora es de solo lectura.');
            setTimeout(() => window.location.href = res.data.redirect_url, 800);
        } catch (err) {
            App.loading(btn, false);
            App.toast('error', 'No se pudo firmar', err.message ?? 'Intenta de nuevo.');
        }
    },

    /* ── Payload (agrupa quirurgica[campo] en objeto) ────────────────────── */
    _buildPayload(form) {
        const fd = new FormData(form);
        const out = { quirurgica: {} };
        for (const [key, value] of fd.entries()) {
            if (key === '_token') continue;
            const m = key.match(/^quirurgica\[(\w+)\]$/);
            if (m) {
                if (value !== '') out.quirurgica[m[1]] = value;
            } else if (value !== '') {
                out[key] = value;
            }
        }
        // Si no hay datos quirúrgicos, no mandes el objeto vacío.
        if (Object.keys(out.quirurgica).length === 0) delete out.quirurgica;
        return out;
    },
};

App.notaForm = notaForm;

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('nota-form');
    if (form) {
        App.bindClearOnInput(form);
        form.addEventListener('submit', notaForm.submit);
        notaForm.onTipoChange(); // estado inicial de la sección quirúrgica
    }
});

export default notaForm;
