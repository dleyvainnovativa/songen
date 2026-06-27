
import App from './app.js';

    App.persForm = {
        abrir() {
            document.getElementById('pers-modal-title').textContent = 'Nuevo personal';
            document.getElementById('pers-form').reset();
            document.getElementById('pers-id').value = '';
            document.getElementById('acceso-bloque').style.display = '';   // acceso solo al crear
            document.getElementById('acceso-campos').style.display = 'none';
            App.clearErrors(document.getElementById('pers-form'));
            document.getElementById('pers-modal').style.display = 'flex';
        },
        editar(m) {
            document.getElementById('pers-modal-title').textContent = 'Editar personal';
            App.clearErrors(document.getElementById('pers-form'));
            document.getElementById('pers-id').value = m.id_medico;
            ['nombre','primer_apellido','segundo_apellido','cedula_profesional',
             'cedula_especialidad','id_especialidad','id_establecimiento','rol',
             'rol_sistema','email','telefono'].forEach(f => {
                const el = document.getElementById(f);
                if (el) el.value = m[f] ?? '';
            });
            // Al editar no se crea acceso desde aquí.
            document.getElementById('acceso-bloque').style.display = 'none';
            document.getElementById('pers-modal').style.display = 'flex';
        },
        cerrar() { document.getElementById('pers-modal').style.display = 'none'; },
        toggleAcceso() {
            const on = document.getElementById('crear_acceso').checked;
            document.getElementById('acceso-campos').style.display = on ? '' : 'none';
        },
        async guardar(e) {
            e.preventDefault();
            const form = e.target;
            App.clearErrors(form);
            const id = document.getElementById('pers-id').value;
            const payload = App.serialize(form);
            // checkbox: serialize lo deja como 'on'/ausente; normalizamos a 1/0
            payload.crear_acceso = document.getElementById('crear_acceso')?.checked ? 1 : 0;
            const btn = document.getElementById('pers-submit');
            App.loading(btn, true, 'Guardando…');
            try {
                const res = id
                    ? await App.put(`personal/${id}`, payload)
                    : await App.post('personal', payload);
                // Aviso de Firebase (si la creación de acceso tuvo problemas)
                if (res.firebase_aviso) {
                    App.toast('warning', 'Guardado con aviso', res.firebase_aviso);
                } else {
                    App.toast('success', 'Guardado', 'Recargando…');
                }
                setTimeout(() => window.location.reload(), 1100);
            } catch (err) {
                App.loading(btn, false);
                if (err instanceof App.ApiError && err.status === 422) {
                    App.showErrors(err.errors);
                    if (err.errors.rol_sistema || err.errors.personal) {
                        App.toast('error', 'No permitido', (err.errors.rol_sistema?.[0] || err.errors.personal?.[0]));
                    }
                } else {
                    App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
                }
            }
        },
        async eliminar(id) {
            const ok = await App.confirm({
                title: '¿Eliminar este personal?',
                body: 'Si tiene notas o historias ligadas, se archivará en lugar de borrarse (para conservar el expediente). No puedes eliminarte a ti mismo ni al último administrador.',
                confirmText: 'Continuar', danger: true,
            });
            if (!ok) return;
            try {
                const res = await App.del(`personal/${id}`);
                App.toast('success', res.resultado === 'archivado' ? 'Archivado' : 'Eliminado', res.message);
                setTimeout(() => window.location.reload(), 1100);
            } catch (err) {
                const msg = (err instanceof App.ApiError && err.errors?.personal)
                    ? err.errors.personal[0] : (err.message ?? 'No se pudo completar.');
                App.toast('error', 'No permitido', msg);
            }
        },
        async reactivar(id) {
            try { await App.post(`personal/${id}/reactivar`, {}); window.location.reload(); }
            catch (err) { App.toast('error', 'Error', err.message); }
        },
    };
    document.getElementById('pers-form')?.addEventListener('submit', App.persForm.guardar);
    document.querySelectorAll('.btn-editar-pers').forEach(btn => {
        btn.addEventListener('click', () => App.persForm.editar(JSON.parse(btn.dataset.pers)));
    });
    document.getElementById('pers-modal')?.addEventListener('click', e => {
        if (e.target.id === 'pers-modal') App.persForm.cerrar();
    });