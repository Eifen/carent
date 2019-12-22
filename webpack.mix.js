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

 mix.js('resources/js/login.js', 'public/js')
    .less('resources/less/login.less', 'public/css/frontend')
    .styles('resources/css/bootstrap.4.3.1.min.css','public/css/bootstrap.4.3.1.min.css');
