import path from 'node:path';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

/** Bare imports in ../src must resolve here; src/ and laravel-backend/ are siblings, so Node would not find node_modules otherwise. */
function nm(pkg) {
    return path.resolve(__dirname, 'node_modules', pkg);
}

export default defineConfig({
    resolve: {
        alias: {
            '@': path.resolve(__dirname, '../src'),
            vue: nm('vue'),
            'vue-router': nm('vue-router'),
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue(),
    ],
    server: {
        fs: {
            allow: ['..'],
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
