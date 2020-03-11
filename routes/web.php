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
Route::get('/formBuscarUsuario', function() {return view('usuario/buscarUsuario');});
Route::get('/buscarUsuarios', 'UsuarioController@buscarUsuarios');
Route::get('/detalleUsuario', 'UsuarioController@detalleUsuario');
Route::get('/formModificarUsuario/{idUsuario}', 'UsuarioController@formModificarUsuario')->where('idUsuario', '[0-9]+');//Validamos que solo pase números*/
Route::get('/detalleUsuarioModificar', 'UsuarioController@detalleUsuarioModificar');
Route::post('/modificarUsuario', 'UsuarioController@modificarUsuario');
Route::get('/formNuevoCliente', function() {return view('cliente/nuevoCliente');});
Route::get('/buscarCliente', 'ClienteController@buscarCliente');
Route::get('/estados', 'ClienteController@estados');
Route::get('/municipios', 'ClienteController@municipios');
Route::get('/parroquias', 'ClienteController@parroquias');
Route::post('/crearCliente', 'ClienteController@crearCliente');
Route::get('/formBuscarCliente', function() {return view('cliente/buscarCliente');});
Route::get('/buscarClientes', 'ClienteController@buscarClientes');
Route::get('/detalleCliente', 'ClienteController@detalleCliente');
Route::get('/formDetalleFactCliente', function() {return view('cliente/detalleFactcliente');});
Route::get('/buscarClieProyec', 'ClienteController@buscarClieProyec');
Route::get('/detalleClienteProy', 'ClienteController@detalleClienteProy');
Route::post('/actualizarFactCliente', 'ClienteController@actualizarFactCliente');
Route::post('/crearFactCliente', 'ClienteController@crearFactCliente');
Route::get('/buscarUsuariosS', 'ClienteController@buscarUsuarios');
Route::get('/buscarUsuariosG', 'ClienteController@buscarUsuariosG');
Route::get('/detalleUsuarios', 'ClienteController@detalleUsuario');
Route::get('/formModificarCliente/{idCliente}', 'ClienteController@formModificarCliente')->where('idCliente', '[0-9]+');//Validamos que solo pase números*/
Route::get('/detalleClienteModificar', 'ClienteController@detalleClienteModificar');
Route::post('/modificarCliente', 'ClienteController@modificarCliente');
Route::get('/formBuscarRegistro', function() {return view('crea/buscarRegistro');});
Route::get('/buscarRegistro', 'CreaController@buscarRegistro');
Route::get('/detalleRegistro', 'CreaController@detalleRegistro');
Route::get('/formNuevoCargo', function() {return view('crea/nuevoCargo');});
Route::post('/crearCargo', 'CreaController@crearCargo');
Route::get('/formNuevaDivision', function() {return view('crea/nuevaDivision');});
Route::post('/crearDivision', 'CreaController@crearDivision');
Route::get('/formNuevoProyecto', function() {return view('proyecto/nuevoProyecto');});
Route::get('/dataInicialNuevoProyecto', 'ProyectoController@dataInicialNuevoProyecto');
Route::post('/crearProyecto', 'ProyectoController@crearProyecto');
Route::get('/proyectos', function() {return view('proyecto/formBuscarProyectos');});
Route::get('/dataInicialListadoProyectos', 'ProyectoController@dataInicialListadoProyectos');
Route::get('/buscarProyectos', 'ProyectoController@buscarProyectos');
Route::get('/formModificarProyecto/{idProyecto}', 'ProyectoController@formModificarProyecto')->where('idProyecto', '[0-9]+');//Validamos que solo pase números*/
Route::get('/detalleProyectoModificar', 'ProyectoController@detalleProyectoModificar');
Route::post('/modificarProyecto', 'ProyectoController@modificarProyecto');