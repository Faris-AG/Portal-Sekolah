import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    optimizeDeps: {
        include: ['vue', '@inertiajs/vue3', 'axios', 'lucide-vue-next'],
    },
    build: {
        rollupOptions: {
            output: {
                /**
                 * manualChunks sebagai Function agar kompatibel dengan Rollup/Vite.
                 * Memisahkan vendor utama ke chunk terpisah supaya cache browser
                 * tetap valid saat hanya kode aplikasi yang berubah.
                 */
                manualChunks(id) {
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/@vue/')) {
                        return 'vendor-vue';
                    }
                    if (id.includes('node_modules/@inertiajs/')) {
                        return 'vendor-inertia';
                    }
                    if (id.includes('node_modules/axios/')) {
                        return 'vendor-axios';
                    }
                    if (id.includes('node_modules/lucide-vue-next/')) {
                        return 'vendor-lucide';
                    }
                },
            },
        },
    },
});
