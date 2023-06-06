import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';

export default defineConfig({
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/cambiarClave.js',
                'resources/js/cliente/buscarCliente.js',
                'resources/js/cliente/modificarCliente.js',
                'resources/js/cliente/nuevoCliente.js',
                'resources/js/crea/buscarRegistro.js',
                'resources/js/crea/nuevaDivision.js',
                'resources/js/crea/nuevoCargo.js',
                'resources/js/error/permiso.js',
                'resources/js/facturacion/agregarIngresosGastos.js',
                'resources/js/facturacion/ingresosGastos.js',
                'resources/js/horasCargables/cargarHoras.js',
                'resources/js/horasNoCargables/formConceptosHorasNoCargables.js',
                'resources/js/horasNoCargables/formHorasNoCargables.js',
                'resources/js/inicio.js',
                'resources/js/login.js',
                'resources/js/proyecto/formBuscarProyectos.js',
                'resources/js/proyecto/modificarProyecto.js',
                'resources/js/proyecto/nuevoProyecto.js',
                'resources/js/proyecto/proyectoDivision.js',
                'resources/js/reportes/formReportes.js',
                'resources/js/usuario/buscarUsuario.js',
                'resources/js/usuario/index.js',
                'resources/js/usuario/modificarUsuario.js',
                'resources/js/usuario/nuevoUsuario.js',
            ],
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
});
