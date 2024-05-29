import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'public/laravel-comments.hot', // Most important lines
            buildDirectory: 'public/build', // Most important lines
            input: ['resources/js/app.js'],
            refresh: true,
        }),
    ],
});
