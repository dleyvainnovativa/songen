import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/theme.css', 'resources/js/app.js',
'resources/js/pacientes-form.js',
'resources/js/hc-wizard.js',
'resources/js/notas-form.js',
'resources/js/medicamentos.js',
'resources/js/establecimientos.js',
'resources/js/pacientes-archive.js',

            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
