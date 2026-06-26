/*
|------------------------------------------------------------------------------
| resources/js/hc-wizard.js  —  App.hcWizard
|------------------------------------------------------------------------------
|
| Lógica del wizard de Historia Clínica (4 pasos). Consume la plomería de
| window.App (put, toast, loading, showErrors) y añade lo propio del wizard:
| navegación entre pasos, toggles bool, cálculo de IMC, etiquetas de escalas
| y el submit que separa los campos del padre de los del subtipo.
|
| Se carga solo en historias/edit.blade vía @vite.
*/

import App from './app.js';

const TOTAL = 4;

const hcWizard = {
    step: 1,

    /* ── Navegación ──────────────────────────────────────────────────────── */
    go(n) {
        if (n < 1 || n > TOTAL) return;
        this.step = n;

        document.querySelectorAll('.step-panel').forEach(p => {
            p.classList.toggle('active', +p.dataset.panel === n);
        });
        document.querySelectorAll('.wstep').forEach(s => {
            const sn = +s.dataset.step;
            s.classList.toggle('active', sn === n);
            s.classList.toggle('done', sn < n);
        });

        document.getElementById('cur-step').textContent = n;
        document.getElementById('btn-prev').disabled = (n === 1);

        // En el último paso, cambia "Siguiente" por "Guardar".
        const esUltimo = (n === TOTAL);
        document.getElementById('btn-next').style.display = esUltimo ? 'none' : '';
        document.getElementById('btn-save').style.display = esUltimo ? '' : 'none';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    next() { this.go(this.step + 1); },
    prev() { this.go(this.step - 1); },

    /* ── Bool toggle (bool-card) ─────────────────────────────────────────── */
    toggleBool(card, hiddenId, detalleId = null) {
        console.log(card);
        console.log( hiddenId);
        console.log(detalleId);
        const hidden = document.getElementById(hiddenId);
        const on = hidden.value === '1';
        const nuevo = !on;

        hidden.value = nuevo ? '1' : '0';
        card.classList.toggle('selected', nuevo);

        // Campo de detalle asociado (se muestra/oculta).
        if (detalleId) {
            const det = document.getElementById(detalleId);
            console.log(det);
            if (det) {
                det.classList.toggle('campo-oculto', !nuevo);
                if (!nuevo) det.value = '';
            }
        }
    },

    /* ── IMC en vivo ─────────────────────────────────────────────────────── */
    calcImc() {
        const peso = parseFloat(document.getElementById('peso_kg')?.value);
        const talla = parseFloat(document.getElementById('talla_cm')?.value);
        const badge = document.getElementById('imc-badge');
        const val = document.getElementById('imc-val');
        if (!val) return;

        if (peso > 0 && talla > 0) {
            const m = talla / 100;
            const imc = peso / (m * m);
            val.textContent = imc.toFixed(1);

            // Color según rango.
            let color = 'var(--success)', txt = 'Normal';
            if (imc < 18.5) { color = 'var(--info)'; txt = 'Bajo peso'; }
            else if (imc < 25) { color = 'var(--success)'; txt = 'Normal'; }
            else if (imc < 30) { color = 'var(--warning)'; txt = 'Sobrepeso'; }
            else { color = 'var(--danger)'; txt = 'Obesidad'; }

            val.textContent = `${imc.toFixed(1)} · ${txt}`;
            if (badge) { badge.style.borderColor = color; badge.style.color = color; }
        } else {
            val.textContent = '—';
            if (badge) { badge.style.borderColor = ''; badge.style.color = ''; }
        }
    },

    /* ── Escalas (Glasgow, Barthel, MMSE…) ───────────────────────────────── */
    updateScale(input) {
        const ranges = JSON.parse(input.dataset.scale || '[]');
        const lbl = document.getElementById(input.id + '_lbl');
        if (!lbl) return;

        const v = parseInt(input.value, 10);
        if (isNaN(v)) { lbl.textContent = ''; return; }

        const hit = ranges.find(([lo, hi]) => v >= lo && v <= hi);
        lbl.textContent = hit ? hit[2] : '';
    },

    /* ── Polifarmacia (badge desde config: onchange) ─────────────────────── */
    geri: {
        checkPolifarmacia(value) {
            const badge = document.getElementById('poly-badge');
            const poly = document.getElementById('st_polifarmacia');
            const n = parseInt(value, 10);
            if (!badge) return;
            if (n >= 5) {
                badge.textContent = '⚠ Polifarmacia';
                badge.style.display = 'inline-block';
                // Marca el bool de polifarmacia automáticamente, si existe.
                if (poly) {
                    poly.value = '1';
                    poly.closest('.campo-subtipo')?.querySelector('.bool-card')?.classList.add('selected');
                }
            } else {
                badge.style.display = 'none';
            }
        },
    },

    /* ── Submit ──────────────────────────────────────────────────────────── */
    async submit(e) {
        e.preventDefault();
        const form = e.target;
        App.clearErrors(form);

        const btn = document.getElementById('btn-save');
        App.loading(btn, true, 'Guardando…');

        const payload = hcWizard._buildPayload(form);
        const pacienteId = form.dataset.paciente;

        try {
            const res = await App.put(`pacientes/${pacienteId}/historia`, payload);
            App.toast('success', 'Historia clínica guardada', 'Redirigiendo al expediente…');
            setTimeout(() => window.location.href = res.data.redirect_url, 900);
        } catch (err) {
            App.loading(btn, false);
            if (err instanceof App.ApiError && err.status === 422) {
                hcWizard._showErrors(err.errors);
            } else {
                App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
            }
        }
    },

    /**
     * Construye el payload. Los campos subtipo[campo] se agrupan en un objeto
     * `subtipo`; el resto son campos del padre. Los bool van como 0/1 (hidden).
     */
    _buildPayload(form) {
        const fd = new FormData(form);
        const out = { subtipo: {} };
        for (const [key, value] of fd.entries()) {
            if (key === '_token') continue;
            const m = key.match(/^subtipo\[(\w+)\]$/);
            if (m) {
                out.subtipo[m[1]] = value;
            } else {
                out[key] = value;
            }
        }
        return out;
    },

    /** Muestra errores 422 y salta al paso donde está el primer campo fallido. */
    _showErrors(errors) {
        // Mapa campo → paso (los del padre; subtipo siempre es paso 4).
        const stepOf = (field) => {
            if (field.startsWith('subtipo')) return 4;
            if (['motivo_consulta','padecimiento_actual','peso_kg','talla_cm',
                 'presion_arterial','frecuencia_cardiaca','frecuencia_respiratoria',
                 'temperatura_c','saturacion_o2','exploracion_fisica'].includes(field)) return 2;
            if (['diagnostico_inicial','plan_manejo','pronostico'].includes(field)) return 3;
            return 1;
        };

        let firstStep = 99;
        Object.entries(errors).forEach(([field, msgs]) => {
            const cleanField = field.replace('subtipo.', 'subtipo_');
            const el = document.getElementById(field.replace('subtipo.', 'st_'))
                     || document.querySelector(`[name="${field}"]`)
                     || document.querySelector(`[name="${field.replace('subtipo.', 'subtipo[')}]"]`);
            if (el) el.classList.add('is-invalid');
            const errEl = document.getElementById('err-' + field);
            if (errEl) { errEl.textContent = Array.isArray(msgs) ? msgs[0] : msgs; errEl.style.display = 'block'; }
            firstStep = Math.min(firstStep, stepOf(field));
        });

        if (firstStep <= TOTAL) this.go(firstStep);
        App.toast('error', 'Revisa el formulario', 'Hay campos que necesitan corrección.');
    },
};

App.hcWizard = hcWizard;

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('hc-form');
    if (!form) return;

    form.addEventListener('submit', hcWizard.submit);

    // Inicializa IMC y escalas con los valores precargados (modo edición).
    hcWizard.calcImc();
    document.querySelectorAll('[data-scale]').forEach(input => {
        if (input.value) hcWizard.updateScale(input);
    });
});

export default hcWizard;
