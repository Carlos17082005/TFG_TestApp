import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0', // 1. Permite que el puerto salga del contenedor de Docker a tu Windows
        hmr: {
            host: 'localhost', // 2. Le dice a tu navegador dónde buscar las actualizaciones
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
            usePolling: true, // 3. OBLIGATORIO en Docker/Windows para que detecte cuando guardas un archivo
        },
    },
});
