import App from './app.js';

    App.pacShow = {
        async archivar(id) {
            const ok = await App.confirm({
                title: '¿Archivar paciente?',
                body: 'El paciente se marcará como inactivo. Sus datos clínicos se conservan y puedes reactivarlo después.',
                confirmText: 'Sí, archivar',
                cancelText: 'Cancelar',
            });
            if (!ok) return;

            const btn = document.getElementById('btn-archivar');
            App.loading(btn, true, 'Archivando…');
            try {
                await App.del(`pacientes/${id}`);
                App.toast('success', 'Paciente archivado', 'Recargando…');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                App.loading(btn, false);
                App.toast('error', 'No se pudo archivar', err.message ?? 'Intenta de nuevo.');
            }
        },

        async reactivar(id) {
            const btn = document.getElementById('btn-reactivar');
            App.loading(btn, true, 'Reactivando…');
            try {
                await App.post(`pacientes/${id}/reactivar`, {});
                App.toast('success', 'Paciente reactivado', 'Recargando…');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                App.loading(btn, false);
                App.toast('error', 'No se pudo reactivar', err.message ?? 'Intenta de nuevo.');
            }
        },
    };