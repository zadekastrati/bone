import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    preview: {
        host: true, // allows 0.0.0.0 access
        port: parseInt(process.env.PORT) || 5173, // use Render's PORT
        allowedHosts: ['bone-a9mw.onrender.com'], // add your Render URL here
    },
});
