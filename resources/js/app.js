/*
|------------------------------------------------------------------------------
| resources/js/app.js  —  window.App
|------------------------------------------------------------------------------
|
| Capa central de helpers reutilizables para todo el proyecto. Las vistas
| consumen App.* en lugar de duplicar fetch/toast/validación inline.
|
|   App.get/post/put/del   → HTTP con CSRF + Bearer (Firebase) automáticos
|   App.auth               → token / usuario / logout de Firebase
|   App.toast              → notificaciones
|   App.loading            → estado de carga en botones
|   App.confirm            → modal de confirmación (promise)
|   App.serialize          → FormData → objeto limpio
|   App.showErrors         → pinta errores 422 en el formulario
|   App.clearErrors        → limpia errores
|
| Las funciones específicas de una vista (wizard goToStep, calcIMC, toggleBool)
| NO van aquí; viven en su blade. Aquí solo va la plomería compartida.
|
*/

import { initAuth, getIdToken, getCurrentUser, signInEmail, signOut } from './firebase.js';

const App = {};

/* ──────────────────────────────────────────────────────────────────────────
 | Config
 ────────────────────────────────────────────────────────────────────────── */
App.config = {
    csrf: document.querySelector('meta[name="csrf-token"]')?.content ?? '',
    apiBase: document.querySelector('meta[name="api-base"]')?.content ?? '',
};

/* ──────────────────────────────────────────────────────────────────────────
 | Error tipado para respuestas no-OK
 ────────────────────────────────────────────────────────────────────────── */
class ApiError extends Error {
    constructor(message, status, payload) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload ?? {};
        this.errors = payload?.errors ?? {};
    }
}
App.ApiError = ApiError;

/* ──────────────────────────────────────────────────────────────────────────
 | HTTP core
 |
 | - Adjunta X-CSRF-TOKEN (rutas web) y Bearer Firebase (rutas api) a la vez,
 |   así el mismo helper sirve sin importar el middleware de la ruta.
 | - Lanza ApiError en respuestas !ok; en 422 expone .errors.
 ────────────────────────────────────────────────────────────────────────── */
async function request(method, url, body, opts = {}) {
    const headers = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': App.config.csrf,
        'X-Requested-With': 'XMLHttpRequest',
        ...(opts.headers ?? {}),
    };

    // Bearer Firebase si hay sesión (para rutas api con auth:sanctum/token)
    try {
        const token = await getIdToken();
        if (token) headers['Authorization'] = `Bearer ${token}`;
    } catch (_) { /* sin sesión Firebase: seguimos solo con CSRF */ }

    const init = { method, headers, credentials: 'same-origin' };

    if (body !== undefined && body !== null) {
        if (body instanceof FormData) {
            init.body = body; // el browser pone el boundary
        } else {
            headers['Content-Type'] = 'application/json';
            init.body = JSON.stringify(body);
        }
    }

    // Resolución de URL:
    //   - URL absoluta (http...)        → tal cual
    //   - empieza con '/api' o es web    → tal cual (ruta absoluta del sitio)
    //   - relativa ('pacientes', ...)    → se le antepone apiBase (/api/v1)
    let full;
    if (url.startsWith('http') || url.startsWith('/')) {
        full = url;
    } else {
        full = App.config.apiBase + '/' + url.replace(/^\/+/, '');
    }
    const res = await fetch(full, init);

    // 204 / sin cuerpo
    if (res.status === 204) return null;

    let data = null;
    const ct = res.headers.get('content-type') ?? '';
    if (ct.includes('application/json')) {
        data = await res.json();
    } else {
        data = await res.text();
    }

    if (!res.ok) {
        const msg = (data && data.message) ? data.message : `Error ${res.status}`;
        throw new ApiError(msg, res.status, data);
    }
    return data;
}

App.get  = (url, opts)        => request('GET',    url, null, opts);
App.post = (url, body, opts)  => request('POST',   url, body, opts);
App.put  = (url, body, opts)  => request('PUT',    url, body, opts);
App.del  = (url, opts)        => request('DELETE', url, null, opts);

/* ──────────────────────────────────────────────────────────────────────────
 | Auth (Firebase)
 ────────────────────────────────────────────────────────────────────────── */
App.auth = {
    init: initAuth,
    token: getIdToken,
    user: getCurrentUser,

    /**
     * Login completo: Firebase (email/password) → ID token → sesión Laravel.
     * Devuelve { redirect } en éxito; lanza ApiError con .status/.message si falla.
     */
    async login(email, password) {
        // 1. Firebase nos da el ID token.
        const idToken = await signInEmail(email, password);
        // 2. Lo canjeamos por una sesión Laravel.
        return App.post('/auth/session', { id_token: idToken });
    },

    async logout(redirect = '/login') {
        try { await signOut(); } catch (_) {}
        try { await App.post('/logout', {}); } catch (_) {}
        window.location.href = redirect;
    },
};

/* ──────────────────────────────────────────────────────────────────────────
 | Toast
 |
 | Reutiliza el contenedor #toast-container del layout. type: success|error|info
 ────────────────────────────────────────────────────────────────────────── */
App.toast = function (type, title, body = '') {
    let c = document.getElementById('toast-container');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toast-container';
        document.body.appendChild(c);
    }
    const icons = {
        success: 'fa-circle-check',
        error: 'fa-circle-exclamation',
        info: 'fa-circle-info',
        warning: 'fa-triangle-exclamation',
    };
    const div = document.createElement('div');
    div.className = `toast-msg toast-${type}`;
    div.innerHTML =
        `<i class="fa-solid ${icons[type] ?? icons.info}"></i>` +
        `<div><div class="toast-title">${title}</div>` +
        (body ? `<div class="toast-body">${body}</div>` : '') +
        `</div>`;
    c.appendChild(div);
    setTimeout(() => {
        div.style.opacity = '0';
        setTimeout(() => div.remove(), 250);
    }, 5000);
};

/* ──────────────────────────────────────────────────────────────────────────
 | Loading state en botones
 |
 | App.loading(btn, true)  → guarda label, pone spinner, deshabilita
 | App.loading(btn, false) → restaura
 ────────────────────────────────────────────────────────────────────────── */
App.loading = function (btn, on, loadingLabel = 'Procesando…') {
    if (!btn) return;
    if (on) {
        btn.dataset.originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin me-1"></i>${loadingLabel}`;
    } else {
        btn.disabled = false;
        if (btn.dataset.originalHtml) {
            btn.innerHTML = btn.dataset.originalHtml;
            delete btn.dataset.originalHtml;
        }
    }
};

/* ──────────────────────────────────────────────────────────────────────────
 | Confirm modal (promise-based)
 |
 | const ok = await App.confirm({ title, body, confirmText, danger:true });
 ────────────────────────────────────────────────────────────────────────── */
App.confirm = function ({ title = '¿Confirmar?', body = '', confirmText = 'Confirmar', cancelText = 'Cancelar', danger = false } = {}) {
    return new Promise((resolve) => {
        const overlay = document.createElement('div');
        overlay.className = 'app-confirm-overlay';
        overlay.innerHTML = `
            <div class="app-confirm-box" role="dialog" aria-modal="true">
                <div class="app-confirm-title">${title}</div>
                ${body ? `<div class="app-confirm-body">${body}</div>` : ''}
                <div class="app-confirm-actions">
                    <button class="btn-prev" data-act="cancel">${cancelText}</button>
                    <button class="${danger ? 'btn-danger-solid' : 'btn-next'}" data-act="ok">${confirmText}</button>
                </div>
            </div>`;
        document.body.appendChild(overlay);
        const done = (val) => { overlay.remove(); resolve(val); };
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) done(false);
            const act = e.target.closest('[data-act]')?.dataset.act;
            if (act === 'ok') done(true);
            if (act === 'cancel') done(false);
        });
        document.addEventListener('keydown', function esc(e) {
            if (e.key === 'Escape') { done(false); document.removeEventListener('keydown', esc); }
        });
    });
};

/* ──────────────────────────────────────────────────────────────────────────
 | Form helpers
 ────────────────────────────────────────────────────────────────────────── */

// FormData → objeto limpio (omite _token y strings vacíos)
App.serialize = function (form, { keepEmpty = false } = {}) {
    const fd = new FormData(form);
    const out = {};
    fd.forEach((v, k) => {
        if (k === '_token') return;
        if (!keepEmpty && v === '') return;
        out[k] = v;
    });
    return out;
};

// Pinta errores de validación 422 sobre el formulario.
// fieldStep (opcional) = { campo: numeroPaso } para saltar al paso correcto.
App.showErrors = function (errors, { fieldStep = {}, onJumpStep = null } = {}) {
    let firstStep = null;
    Object.entries(errors).forEach(([field, msgs]) => {
        const el = document.getElementById(field) || document.querySelector(`[name="${field}"]`);
        const errEl = document.getElementById('err-' + field);
        if (el) el.classList.add('is-invalid');
        if (errEl) {
            errEl.textContent = Array.isArray(msgs) ? msgs[0] : msgs;
            errEl.style.display = 'block';
        }
        if (fieldStep[field] != null && (firstStep === null || fieldStep[field] < firstStep)) {
            firstStep = fieldStep[field];
        }
    });
    if (firstStep !== null && typeof onJumpStep === 'function') onJumpStep(firstStep);
    setTimeout(() => {
        document.querySelector('.is-invalid')
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }, 150);
};

App.clearErrors = function (scope = document) {
    scope.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    scope.querySelectorAll('.invalid-feedback').forEach(el => el.style.display = 'none');
};

// Limpia el estado inválido al escribir (auto-bind para cualquier form)
App.bindClearOnInput = function (form) {
    if (!form) return;
    form.querySelectorAll('.form-control, .form-select').forEach(el => {
        const clear = () => el.classList.remove('is-invalid');
        el.addEventListener('input', clear);
        el.addEventListener('change', clear);
    });
};

/* ──────────────────────────────────────────────────────────────────────────
 | Bootstrap del módulo
 ────────────────────────────────────────────────────────────────────────── */
window.App = App;

document.addEventListener('DOMContentLoaded', () => {
    // Inicia el observador de sesión de Firebase (no bloquea el render)
    App.auth.init?.();

    // Botones con [data-logout]
    document.querySelectorAll('[data-logout]').forEach(btn => {
        btn.addEventListener('click', (e) => { e.preventDefault(); App.auth.logout(); });
    });

    // Menú de usuario del top-bar
    const trigger = document.getElementById('user-trigger');
    const menu = document.getElementById('user-menu');
    if (trigger && menu) {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const open = menu.classList.toggle('open');
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', (e) => {
            if (!menu.contains(e.target)) {
                menu.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                menu.classList.remove('open');
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Offcanvas de navegación (móvil)
    const navToggle = document.getElementById('nav-toggle');
    const offcanvas = document.getElementById('offcanvas-nav');
    const backdrop = document.getElementById('offcanvas-backdrop');
    const ocClose = document.getElementById('offcanvas-close');
    if (navToggle && offcanvas && backdrop) {
        const abrir = () => {
            offcanvas.classList.add('open');
            backdrop.classList.add('show');
            offcanvas.setAttribute('aria-hidden', 'false');
            navToggle.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };
        const cerrar = () => {
            offcanvas.classList.remove('open');
            backdrop.classList.remove('show');
            offcanvas.setAttribute('aria-hidden', 'true');
            navToggle.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };
        navToggle.addEventListener('click', abrir);
        backdrop.addEventListener('click', cerrar);
        ocClose?.addEventListener('click', cerrar);
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && offcanvas.classList.contains('open')) cerrar();
        });
    }
});

export default App;
