import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    server: {
        // Listen on all addresses, so Docker can forward port 5173 to your computer
        host: '0.0.0.0',
        // The browser loads the React and CSS files from localhost:5173
        hmr: { host: 'localhost' },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
});
