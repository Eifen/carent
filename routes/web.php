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

Route::get('/', 'LoginController@index')->middleware('usuario.session')->name('loginView');
Route::get('/encryptConfig', 'ConfigsController@encryptConfig');
Route::post('/login', 'LoginController@login');
Route::post('/recoverylogin', 'LoginController@recoverylogin');
Route::get('/inicio', 'InicioController@inicio');
Route::get('/menUsuario', 'InicioController@menUsuario');
Route::get('/logout', 'LoginController@logout');
Route::get('/cambiarClave', 'InicioController@cambiarClave')->middleware('usuario.session');
Route::post('/guardarNuevaClave', 'InicioController@guardarNuevaClave');
Route::get('/formNuevoUsuario', function() {return view('usuario/nuevoUsuario');})->middleware('usuario.session');
Route::get('/dataInicialNuevoUsuario','UsuarioController@dataInicialNuevoUsuario')->middleware('usuario.session');
Route::get('/estados', 'UsuarioController@estados');
Route::get('/municipios', 'UsuarioController@municipios');
Route::get('/parroquias', 'UsuarioController@parroquias');
Route::get('/divisiones', 'UsuarioController@divisiones');
Route::get('/cargos', 'UsuarioController@cargos');
Route::post('/crearUsuario', 'UsuarioController@crearUsuario');
Route::get('/formBuscarUsuario', function() {return view('usuario/buscarUsuario');});
Route::get('/buscarUsuarios', 'UsuarioController@buscarUsuarios');
Route::get('/detalleUsuario', 'UsuarioController@detalleUsuario');
Route::get('/formModificarUsuario/{idUsuario}', 'UsuarioController@formModificarUsuario')->middleware('usuario.session')->where('idUsuario', '[0-9]+');//Validamos que solo pase números*/
Route::get('/detalleUsuarioModificar', 'UsuarioController@detalleUsuarioModificar');
Route::post('/modificarUsuario', 'UsuarioController@modificarUsuario');
Route::get('/formNuevoCliente', function() {return view('cliente/nuevoCliente');})->middleware('usuario.session');
Route::get('/buscarCliente', 'ClienteController@buscarCliente');
Route::get('/estados', 'ClienteController@estados');
Route::get('/municipios', 'ClienteController@municipios');
Route::get('/parroquias', 'ClienteController@parroquias');
Route::get('/dataInicialCliente', 'ClienteController@dataInicialCliente');
Route::post('/crearCliente', 'ClienteController@crearCliente');
Route::get('/formBuscarCliente', function() {return view('cliente/buscarCliente');})->middleware('usuario.session');
Route::get('/buscarClientes', 'ClienteController@buscarClientes');
Route::get('/detalleCliente', 'ClienteController@detalleCliente');
Route::get('/formDetalleFactCliente', function() {return view('cliente/detalleFactcliente');})->middleware('usuario.session');
Route::get('/buscarClieProyec', 'ClienteController@buscarClieProyec');
Route::get('/detalleClienteProy', 'ClienteController@detalleClienteProy');
Route::post('/actualizarFactCliente', 'ClienteController@actualizarFactCliente');
Route::post('/crearFactCliente', 'ClienteController@crearFactCliente');
Route::get('/buscarUsuariosS', 'ClienteController@buscarUsuariosS');
Route::get('/buscarUsuariosG', 'ClienteController@buscarUsuariosG');
Route::get('/detalleUsuarios', 'ClienteController@detalleUsuario');
Route::get('/formModificarCliente/{idCliente}', 'ClienteController@formModificarCliente')->middleware('usuario.session')->where('idCliente', '[0-9]+');//Validamos que solo pase números*/
Route::get('/detalleClienteModificar', 'ClienteController@detalleClienteModificar');
Route::post('/modificarCliente', 'ClienteController@modificarCliente');
Route::get('/formBuscarRegistro', function() {return view('crea/buscarRegistro');})->middleware('usuario.session');
Route::get('/buscarRegistro', 'CreaController@buscarRegistro');
Route::get('/detalleRegistro', 'CreaController@detalleRegistro');
Route::get('/formNuevoCargo', function() {return view('crea/nuevoCargo');})->middleware('usuario.session');
Route::post('/crearCargo', 'CreaController@crearCargo');
Route::get('/formNuevaDivision', function() {return view('crea/nuevaDivision');})->middleware('usuario.session');
Route::post('/crearDivision', 'CreaController@crearDivision');
Route::get('/datosHorasProyecto', 'HorasCargadasController@datosHorasProyecto');
Route::post('/cargarHoras', 'HorasCargadasController@cargarHoras');
Route::get('/detalleModHorasCargadas', 'HorasCargadasController@detalleModHorasCargadas');
Route::post('/ModificarHorasCargadas', 'HorasCargadasController@ModificarHorasCargadas');
Route::get('/detalleHorasEliminar', 'HorasCargadasController@detalleHorasEliminar');
Route::post('/EliminarHorasCargadas', 'HorasCargadasController@EliminarHorasCargadas');
Route::get('/formHorasNoCargables',  function() {return view('horasNoCargables/formConceptosHorasNoCargables');})->middleware('usuario.session');
Route::get('/dataInicialConceptosHorasNoCargables', 'HorasNoCargablesController@dataInicialConceptosHorasNoCargables');
Route::get('/buscarConceptoHorasNoCargables', 'HorasNoCargablesController@buscarConceptoHorasNoCargables');
Route::post('/crearConceptoNoCargable', 'HorasNoCargablesController@crearConceptoNoCargable');
Route::post('/modificarConceptoNoCargable', 'HorasNoCargablesController@modificarConceptoNoCargable');
Route::post('/eliminarHorasNoCargables', 'HorasNoCargablesController@eliminarHorasNoCargables');
Route::get('/cargarHorasNoCargables',  function() {return view('horasNoCargables/formHorasNoCargables');})->middleware('usuario.session');
Route::get('/dataInicialHorasNoCargables', 'HorasNoCargablesController@dataInicialHorasNoCargables');
Route::post('/registrarHorasNoCargables', 'HorasNoCargablesController@registrarHorasNoCargables');
Route::get('/buscarHorasNoCargableCargadas', 'HorasNoCargablesController@buscarHorasNoCargableCargadas');
Route::post('/modificarHorasNoCargables', 'HorasNoCargablesController@modificarHorasNoCargables');
Route::get('/detalleMenu', 'UsuarioController@detalleMenu');
Route::get('/agregarMenUsu', 'UsuarioController@agregarMenUsu');
Route::get('/quitarMenUsu', 'UsuarioController@quitarMenUsu');
Route::get('/modificarMenUsu', 'UsuarioController@modificarMenUsu');

/*
  Módulo de Proyectos
*/
Route::get('/formNuevoProyecto', function() {return view('proyecto/nuevoProyecto');})->middleware('usuario.session');
Route::get('/dataInicialNuevoProyecto', 'ProyectoController@dataInicialNuevoProyecto');
Route::get('/proyectos', function() {return view('proyecto/formBuscarProyectos');})->middleware('usuario.session');
Route::get('/dataInicialListadoProyectos', 'ProyectoController@dataInicialListadoProyectos');
Route::get('/buscarProyectos', 'ProyectoController@buscarProyectos');
Route::get('/formModificarProyecto/{idProyecto}', 'ProyectoController@formModificarProyecto')->where('idProyecto', '[0-9]+')->middleware('usuario.session');
Route::get('/detalleProyectoModificar', 'ProyectoController@detalleProyectoModificar');
Route::get('/proyectoDivision', function() {return view('proyecto/proyectoDivision');})->middleware('usuario.session');
Route::get('/asignarProyectos', 'ProyectoController@asignarProyectos');
Route::get('/buscardiviProyectos', 'ProyectoController@buscardiviProyectos');
Route::get('/detalleAnalistaProyecto', 'ProyectoController@detalleAnalistaProyecto');
Route::get('/agregarAnalistaProy', 'ProyectoController@agregarAnalistaProy');
Route::get('/DetalleDivProyecto', 'ProyectoController@DetalleDivProyecto');
Route::get('/modAnalistaProy', 'ProyectoController@modAnalistaProy');
Route::get('/asigHorasAnalistaProy', 'ProyectoController@asigHorasAnalistaProy');
Route::get('/formCargarHoras/{idProyAnalista}', 'ProyectoController@formCargarHoras');
Route::get('/buscarClienteProyecto', 'ProyectoController@buscarClienteProyecto');
Route::get('/buscarSocioProyecto', 'ProyectoController@buscarSocioProyecto');
Route::get('/buscarGerenteProyecto', 'ProyectoController@buscarGerenteProyecto');
Route::get('/proyectoGerentesDivision', 'ProyectoController@proyectoGerentesDivision');

Route::post('/crearProyecto', 'ProyectoController@crearProyecto');
Route::post('/modificarProyecto', 'ProyectoController@modificarProyecto');


/*
  Módulo Facturación
*/
Route::get('/formIngresosGastos', 'FacturacionController@formIngresosGastos')->middleware('usuario.session');
Route::get('/dataInicialIngresosGastos', 'FacturacionController@dataInicialIngresosGastos')->middleware('usuario.session');
Route::get('/formAgregarIngresosGastos/{idProyecto}', 'FacturacionController@formAgregarIngresosGastos')->where('idProyecto', '[0-9]+')->middleware('usuario.session');
Route::get('/dataInicialAgregarIngresosGastos', 'FacturacionController@dataInicialAgregarIngresosGastos')->middleware('usuario.session');
Route::get('/buscarFacturaProyectoNotaCredito', 'FacturacionController@buscarFacturaProyectoNotaCredito')->middleware('usuario.session');
Route::get('/buscarProyectoFacturacion', 'FacturacionController@buscarProyectoFacturacion')->middleware('usuario.session');
Route::get('/buscarFacturasCargadas', 'FacturacionController@buscarFacturasCargadas')->middleware('usuario.session');

Route::post('/registrarFactura', 'FacturacionController@registrarFactura');
Route::post('/eliminarFactura', 'FacturacionController@eliminarFactura');
Route::post('/modificarFactura', 'FacturacionController@modificarFactura');

/*
  Módulo de Reportes
*/
Route::get('/formReportes', 'ReportesController@formReportes')->middleware('usuario.session');
Route::get('/dataInicialFormReportes', 'ReportesController@dataInicialFormReportes')->middleware('usuario.session');
