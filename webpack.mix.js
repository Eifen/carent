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
    .js('resources/js/fontawesome-free-5.12.0/all.min.js', 'public/js/fontawesome-free-5.12.0.js')
    .less('resources/less/login.less', 'public/css/login.css')
    .styles('resources/css/bootstrap-4.4.1/bootstrap.min.css','public/css/bootstrap.min.css');
