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

/* Login */
Route::get('/', 'LoginController@index')->middleware('usuario.session')->name('loginView');
Route::post('/login', 'LoginController@login');
Route::post('/recoverylogin', 'LoginController@recoverylogin');

/* Configuraciones generales */
Route::get('/encryptConfig', 'ConfigsController@encryptConfig');
Route::get('/menUsuario', 'ConfigsController@menUsuario');

/* Inicio */
Route::get('/inicio', 'InicioController@inicio')->middleware('usuario.session')->name('loginView');;
Route::get('/logout', 'LoginController@logout');
Route::get('/cambiarClave', 'InicioController@cambiarClave')/*->middleware('usuario.session')*/;
Route::post('/guardarNuevaClave', 'InicioController@guardarNuevaClave');

/* Módulo de Usuario */
Route::get('/usuarios', function() {return view('usuario/index');})->middleware('usuario.session');
Route::get('/searchUsers', 'UsuarioController@searchUsers');
Route::get('/buscarUsuarios', 'UsuarioController@buscarUsuarios');



Route::get('/formNuevoUsuario', function() {return view('usuario/nuevoUsuario');})->middleware('usuario.session');
Route::get('/dataInicialNuevoUsuario','UsuarioController@dataInicialNuevoUsuario')->middleware('usuario.session');
Route::get('/estados', 'UsuarioController@estados');
Route::get('/municipios', 'UsuarioController@municipios');
Route::get('/parroquias', 'UsuarioController@parroquias');
Route::get('/divisiones', 'UsuarioController@divisiones');
Route::get('/cargos', 'UsuarioController@cargos');
Route::post('/crearUsuario', 'UsuarioController@crearUsuario');
Route::get('/formBuscarUsuario', function() {return view('usuario/buscarUsuario');});
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
Route::get('/menuUsuario', 'UsuarioController@menuUsuario');

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
Route::get('/montosAdicionesProy', 'ProyectoController@montosAdicionesProy');
Route::get('/horasAdicionesProyDiv', 'ProyectoController@horasAdicionesProyDiv');

Route::post('/crearProyecto', 'ProyectoController@crearProyecto');
Route::post('/modificarProyecto', 'ProyectoController@modificarProyecto');
Route::post('/agregarMontoAdicionalProy', 'ProyectoController@agregarMontoAdicionalProy');
Route::post('/eliminarMontosAdicionesProy', 'ProyectoController@eliminarMontosAdicionesProy');
Route::post('/agregarHoraAdicionalProyDiv', 'ProyectoController@agregarHoraAdicionalProyDiv');
Route::post('/eliminarHoraAdicionalProyDiv', 'ProyectoController@eliminarHoraAdicionalProyDiv');

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
Route::get('/formReportes', 'Reportes\ReportesController@formReportes')->middleware('usuario.session');
Route::get('/dataInicialFormReportes', 'Reportes\ReportesController@dataInicialFormReportes')->middleware('usuario.session');
Route::get('/dataRepHorasCargables', 'Reportes\HorasCargablesController@dataRepHorasCargables')->middleware('usuario.session');
Route::get('/buscarHorasCargables', 'Reportes\HorasCargablesController@buscarHorasCargables')->middleware('usuario.session');
Route::get('/dataRepClientesProyectos', 'Reportes\ClientesProyectoController@dataRepClientesProyectos')->middleware('usuario.session');
Route::get('/buscarClientesProyectos', 'Reportes\ClientesProyectoController@buscarClientesProyectos')->middleware('usuario.session');
Route::get('/dataRepEmpleados', 'Reportes\EmpleadosController@dataRepEmpleados')->middleware('usuario.session');
Route::get('/buscarEmpleados', 'Reportes\EmpleadosController@buscarEmpleados')->middleware('usuario.session');
Route::get('/dataRepClientes', 'Reportes\ClientesController@dataRepClientes')->middleware('usuario.session');
Route::get('/dataRepHorasProyectos', 'Reportes\HorasProyectosController@dataRepHorasProyectos')->middleware('usuario.session');
Route::get('/buscarHorasProyectos', 'Reportes\HorasProyectosController@buscarHorasProyectos')->middleware('usuario.session');
Route::get('/consultarClientes', 'Reportes\ClientesController@consultarClientes')->middleware('usuario.session');
Route::get('/dataRepFacturadoCliProy', 'Reportes\FacturadoCliProyController@dataRepFacturadoCliProy')->middleware('usuario.session');
Route::get('/filtrarCliProy', 'Reportes\FacturadoCliProyController@filtrarCliProy')->middleware('usuario.session');
Route::get('/dataRepTotalHorasEmp', 'Reportes\TotalHorasEmpController@dataRepTotalHorasEmp')->middleware('usuario.session');
Route::get('/repTotalHorasEmpEmpleadosDivision', 'Reportes\TotalHorasEmpController@repTotalHorasEmpEmpleadosDivision')->middleware('usuario.session');
Route::get('/repTotalHorasInfoEmp', 'Reportes\TotalHorasEmpController@repTotalHorasInfoEmp')->middleware('usuario.session');
Route::get('/dataRepTotalHorasCarg', 'Reportes\TotalHorasCargController@dataRepTotalHorasCarg')->middleware('usuario.session');
Route::get('/buscarRepTotalHorasCarg', 'Reportes\TotalHorasCargController@buscarRepTotalHorasCarg')->middleware('usuario.session');
Route::get('/dataRepTotalHorasProyectos', 'Reportes\TotalHorasProyectosController@dataRepTotalHorasProyectos')->middleware('usuario.session');
Route::get('/buscarTotalHorasProyectos', 'Reportes\TotalHorasProyectosController@buscarTotalHorasProyectos')->middleware('usuario.session');
Route::get('/dataRepTotalHorasAsig', 'Reportes\TotalHorasAsigController@dataRepTotalHorasAsig')->middleware('usuario.session');
Route::get('/buscarTotalHorasAsig', 'Reportes\TotalHorasAsigController@buscarTotalHorasAsig')->middleware('usuario.session');
Route::get('/dataRepUltimaCarga', 'Reportes\UltimaCargaController@dataRepUltimaCarga')->middleware('usuario.session');
Route::get('/buscarUltimaCarga', 'Reportes\UltimaCargaController@buscarUltimaCarga')->middleware('usuario.session');
Route::get('/dataRepTotalNoHorasCarg', 'Reportes\TotalHorasNoCargController@dataRepTotalNoHorasCarg')->middleware('usuario.session');
Route::get('/buscarRepTotalHorasNoCarg', 'Reportes\TotalHorasNoCargController@buscarRepTotalHorasNoCarg')->middleware('usuario.session');
Route::get('/dataRepUltimaCargaHorasNo', 'Reportes\UltimaCargaHorasNoCargablesController@dataRepUltimaCargaHorasNo')->middleware('usuario.session');
Route::get('/buscarUltimaCargaHorasNo', 'Reportes\UltimaCargaHorasNoCargablesController@buscarUltimaCargaHorasNo')->middleware('usuario.session');
/*
  Rutas para tareas programadas
*/
Route::get('/repEmpSinCargarHoras', 'Reportes\TotalHorasEmpController@repEmpSinCargarHoras')->name('test1');
