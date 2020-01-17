<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('usuario.session')->group(function () {
    Route::get('/', function () { return view('login'); })->name('loginView');
});

Route::get('/encryptConfig', 'ConfigsController@encryptConfig');
Route::post('/login', 'LoginController@login');
Route::post('/recoverylogin', 'LoginController@recoverylogin');
Route::get('/inicio', 'InicioController@inicio');
Route::get('/menUsuario', 'InicioController@menUsuario');
Route::get('/logout', 'LoginController@logout');
Route::get('/cambiarClave', 'InicioController@cambiarClave');
Route::post('/guardarNuevaClave', 'InicioController@guardarNuevaClave');
Route::get('/formNuevoUsuario', function() {return view('usuario/nuevoUsuario');});
Route::get('/estados', 'UsuarioController@estados');
Route::get('/municipios', 'UsuarioController@municipios');
Route::get('/parroquias', 'UsuarioController@parroquias');
Route::get('/divisiones', 'UsuarioController@divisiones');
Route::get('/cargos', 'UsuarioController@cargos');
Route::post('/crearUsuario', 'UsuarioController@crearUsuario');
