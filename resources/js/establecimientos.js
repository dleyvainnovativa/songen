import App from './app.js';


    window.App = window.App || {};
    App.estForm = {
        abrir() {
            document.getElementById('est-modal-title').textContent = 'Nuevo establecimiento';
            document.getElementById('est-form').reset();
            document.getElementById('est-id').value = '';
            App.clearErrors(document.getElementById('est-form'));
            document.getElementById('est-modal').style.display = 'flex';
        },
        editar(est) {
            document.getElementById('est-modal-title').textContent = 'Editar establecimiento';
            App.clearErrors(document.getElementById('est-form'));
            document.getElementById('est-id').value = est.id_establecimiento;
            ['nombre','razon_social','rfc','licencia_sanitaria','domicilio','colonia',
             'municipio','estado','cp','telefono','email','nivel_atencion'].forEach(f => {
                const el = document.getElementById(f);
                if (el) el.value = est[f] ?? '';
            });
            document.getElementById('est-modal').style.display = 'flex';
        },
        cerrar() { document.getElementById('est-modal').style.display = 'none'; },
        async guardar(e) {
            e.preventDefault();
            const form = e.target;
            App.clearErrors(form);
            const id = document.getElementById('est-id').value;
            const payload = App.serialize(form);
            const btn = document.getElementById('est-submit');
            App.loading(btn, true, 'Guardando…');
            try {
                if (id) await App.put(`establecimientos/${id}`, payload);
                else    await App.post('establecimientos', payload);
                App.toast('success', 'Guardado', 'Recargando…');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                App.loading(btn, false);
                if (err instanceof App.ApiError && err.status === 422) App.showErrors(err.errors);
                else App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
            }
        },
        async eliminar(id) {
            const ok = await App.confirm({ title:'¿Eliminar establecimiento?', body:'Solo se puede eliminar si no tiene pacientes ni personal ligados.', confirmText:'Eliminar', danger:true });
            if (!ok) return;
            try {
                await App.del(`establecimientos/${id}`);
                App.toast('success', 'Eliminado', 'Recargando…');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                // 422 = tiene registros ligados; mostramos el motivo del servidor.
                const msg = (err instanceof App.ApiError && err.errors?.establecimiento)
                    ? err.errors.establecimiento[0] : (err.message ?? 'No se pudo eliminar.');
                App.toast('error', 'No se puede eliminar', msg);
            }
        },
    };
    document.getElementById('est-form')?.addEventListener('submit', App.estForm.guardar);
    document.querySelectorAll('.btn-editar-est').forEach(btn => {
        btn.addEventListener('click', () => App.estForm.editar(JSON.parse(btn.dataset.est)));
    });
    document.getElementById('est-modal')?.addEventListener('click', e => {
        if (e.target.id === 'est-modal') App.estForm.cerrar();
    });