import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/filament/admin/theme.css'],
            buildDirectory: 'admin-build',
            hotFile: 'public/admin.hot',
            refresh: true,
        }),
        tailwindcss(),
    ],
});
