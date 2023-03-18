import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/less/index.less',
                'resources/js/app.js',
                'resources/sass/app.scss'
            ],
            refresh: true,
        }),
        vue({
            template:{
                transformAssetUrls:{
                    base: null,
                    includeAbsolute: false
                }
            }
        })
    ],
    resolve:{
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap')
        }
    }
});
