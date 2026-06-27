console.log("medicamentos");

import App from './app.js';
    App.medForm = {
        abrir() {
            document.getElementById('med-modal-title').textContent = 'Nuevo medicamento';
            document.getElementById('med-form').reset();
            document.getElementById('med-id').value = '';
            App.clearErrors(document.getElementById('med-form'));
            document.getElementById('med-modal').style.display = 'flex';
        },
        editar(med) {
            document.getElementById('med-modal-title').textContent = 'Editar medicamento';
            App.clearErrors(document.getElementById('med-form'));
            document.getElementById('med-id').value = med.id_medicamento;
            document.getElementById('nombre_generico').value = med.nombre_generico ?? '';
            document.getElementById('nombre_comercial').value = med.nombre_comercial ?? '';
            document.getElementById('forma_farmaceutica').value = med.forma_farmaceutica ?? '';
            document.getElementById('concentracion').value = med.concentracion ?? '';
            document.getElementById('via_administracion').value = med.via_administracion ?? '';
            document.getElementById('med-modal').style.display = 'flex';
        },
        cerrar() {
            document.getElementById('med-modal').style.display = 'none';
        },
        async guardar(e) {
            e.preventDefault();
            const form = e.target;
            App.clearErrors(form);
            const id = document.getElementById('med-id').value;
            const payload = App.serialize(form);
            const btn = document.getElementById('med-submit');
            App.loading(btn, true, 'Guardando…');
            try {
                if (id) await App.put(`medicamentos/${id}`, payload);
                else await App.post('medicamentos', payload);
                App.toast('success', 'Guardado', 'Recargando…');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                App.loading(btn, false);
                if (err instanceof App.ApiError && err.status === 422) App.showErrors(err.errors);
                else App.toast('error', 'No se pudo guardar', err.message ?? 'Intenta de nuevo.');
            }
        },
        async archivar(id) {
            const ok = await App.confirm({
                title: '¿Archivar medicamento?',
                body: 'Dejará de aparecer en las listas de selección, pero se conservan los registros que lo usan.',
                confirmText: 'Archivar',
                danger: true
            });
            if (!ok) return;
            try {
                await App.del(`medicamentos/${id}`);
                window.location.reload();
            } catch (err) {
                App.toast('error', 'Error', err.message);
            }
        },
        async reactivar(id) {
            try {
                await App.post(`medicamentos/${id}/reactivar`, {});
                window.location.reload();
            } catch (err) {
                App.toast('error', 'Error', err.message);
            }
        },
    };
    document.getElementById('med-form')?.addEventListener('submit', App.medForm.guardar);
    // Botones de editar: leen el medicamento desde data-med (evita inyectar JSON en onclick)
    document.querySelectorAll('.btn-editar-med').forEach(btn => {
        btn.addEventListener('click', () => App.medForm.editar(JSON.parse(btn.dataset.med)));
    });
    // Cerrar con click en backdrop
    document.getElementById('med-modal')?.addEventListener('click', e => {
        if (e.target.id === 'med-modal') App.medForm.cerrar();
    });