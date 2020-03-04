const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

 mix.js('resources/js/login.js', 'public/js/login.js')
    .js('resources/js/inicio.js', 'public/js/inicio.js')
    .js('resources/js/cambiarClave.js', 'public/js/cambiarClave.js')
    .js('resources/js/usuario/nuevoUsuario.js', 'public/js/nuevoUsuario.js')
    .js('resources/js/usuario/buscarUsuario.js', 'public/js/buscarUsuario.js')
    .js('resources/js/usuario/modificarUsuario.js', 'public/js/modificarUsuario.js')
    .js('resources/js/cliente/nuevoCliente.js', 'public/js/nuevoCliente.js')
    .js('resources/js/cliente/buscarCliente.js', 'public/js/buscarCliente.js')
    .js('resources/js/cliente/modificarCliente.js', 'public/js/modificarCliente.js')
    .js('resources/js/crea/buscarRegistro.js', 'public/js/buscarRegistro.js')
    .js('resources/js/crea/nuevoCargo.js', 'public/js/nuevoCargo.js')
    .js('resources/js/crea/nuevaDivision.js', 'public/js/nuevaDivision.js')
    .js('resources/js/proyecto/nuevoProyecto.js', 'public/js/nuevoProyecto.js')
    .js('resources/js/proyecto/formBuscarProyectos.js', 'public/js/formBuscarProyectos.js')
    .less('resources/less/login.less', 'public/css/login.css')
    .less('resources/less/inicio.less', 'public/css/inicio.css')
    .less('resources/less/cambiarClave.less', 'public/css/cambiarClave.css')
    .less('resources/less/usuario/nuevoUsuario.less', 'public/css/nuevoUsuario.css')
    .less('resources/less/usuario/buscarUsuario.less', 'public/css/buscarUsuario.css')
    .less('resources/less/usuario/modificarUsuario.less', 'public/css/modificarUsuario.css')
    .less('resources/less/cliente/nuevoCliente.less', 'public/css/nuevoCliente.css')
    .less('resources/less/cliente/buscarCliente.less', 'public/css/buscarCliente.css')
    .less('resources/less/cliente/modificarCliente.less', 'public/css/modificarCliente.css')
    .less('resources/less/crea/buscarRegistro.less', 'public/css/buscarRegistro.css')
    .less('resources/less/crea/nuevoCargo.less', 'public/css/nuevoCargo.css')
    .less('resources/less/crea/nuevaDivision.less', 'public/css/nuevaDivision.css')
    .less('resources/less/proyecto/nuevoProyecto.less', 'public/css/nuevoProyecto.css')
    .less('resources/less/proyecto/formBuscarProyectos.less', 'public/css/formBuscarProyectos.css')
    .styles('resources/css/bootstrap-4.4.1/bootstrap.min.css','public/css/bootstrap.min.css')
    .styles('resources/css/fontawesome-free-5.12.0/all.min.css','public/css/fontawesome-free-5.12.0.css');
