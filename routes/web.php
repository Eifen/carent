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
    Route::get('/', function () { return view('login'); });
});

/*
Route::group(['prefix' => '/'], function () {

  $value = session('keys', 'defaultsss');

  if(session()->has("usuario_id")){
    Route::get('/', function () { return redirect('/inicio'); });
  }else{
    Route::get('/', function () { return view('login'); });
  }

});*/
Route::get('/encryptConfig', 'LoginController@encryptConfig');
Route::post('/login', 'LoginController@login');
Route::post('/recoverylogin', 'LoginController@recoverylogin');
Route::get('/inicio', 'InicioController@inicio');
