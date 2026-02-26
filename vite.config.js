import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    root: __dirname, // 👈 FORCE correct root
    base: '',
    plugins: [
        laravel({
            hotFile: 'public/commenter.hot', // Most important lines
            buildDirectory: 'build', // Most important lines
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
