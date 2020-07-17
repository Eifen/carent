<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/buscarUsuario.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="buscarUsuario" class="container-fluid" v-on:submit.prevent="buscar">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
            <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Consultar por</option>
                  <option value="1">Código de usuario</option>
                  <option value="2">Cédula</option>
                  <option value="3">Correo electrónico</option>
                  <option value="4">Primer o segundo nombre</option>
                  <option value="5">Primer o segundo apellido</option>
                </select>
              </div>
              <div class="form-group col-12 col-md-6">
                <input class="form-control inputSearch"
                       ref="inputSearch"
                       type="text"
                       v-bind:disabled="formSearch.inputSearch.disabled"
                       v-on:keyup="evaluarCampo('inputSearch', $event)"
                       v-model="formSearch.inputSearch.value">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-md-2">
                <button class="btn btn-primary"
                        type="button"
                        v-bind:disabled="formSearch.submit.disabled"
                        v-html="formSearch.submit.html"
                        v-on:click="buscar"></button>
              </div>
            </form>
          </div>

          <div class="col-12" v-show="usuarios.mostrar">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Código</th>
                  <th scope="col">Cédula</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Correo</th>
                  <th scope="col">Estatus</th>
                  <th scope="col"></th>
                  <th scope="col" v-if="permisoActualizar"></th>
                  <th scope="col" v-if="permisoActualizar">Asignar Menus</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="usuario in usuarios.registros">
                  <th scope="row">@{{ usuario.codigo }}</th>
                  <td>@{{ usuario.cedula }}</td>
                  <td>@{{ usuario.nombre }}</td>
                  <td>@{{ usuario.correo_principal }}</td>
                  <td>@{{ usuario.estatus }}</td>
                  <td>
                    <i class="fas fa-search-plus" v-on:click="mostrarDetalleUsuario(usuario.id, $event)"></i>
                  </td>
                  <td v-if="permisoActualizar">
                    <a v-bind:href="'/formModificarUsuario/'+usuario.id" target="_self">
                       <i class="far fa-edit"></i>
                    </a>
                  </td>
                  <td>
                    <i class="fas fa-user-edit" v-on:click="mostrarDetalleMenu(usuario.id, $event)"></i>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>

        </div>

        <div id="modal-detalle-usuario" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle del Usuario</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleUsuario.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del usuario, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <form class="row" v-if="!detalleUsuario.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Primer Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.nombre_1">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Segundo Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.nombre_2">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Primer Apellido</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.apellido_1">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Segundo Apellido</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.apellido_2">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Documento de Identidad</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.tipo_documento_identidad }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Cédula de Identidad</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.cedula">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Fecha de Nacimiento</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.fecha_nacimiento">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Código de usuario</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.codigo">
                  </div>
                </form>
                <h5 v-if="!detalleUsuario.error">Datos de contacto</h5>
                <form class="row" v-if="!detalleUsuario.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Correo Principal</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.correo_principal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Correo Secundario</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.correo_secundario">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Teléfono Principal</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.telefono_principal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Teléfono Secundario</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.telefono_secundario">
                  </div>
                </form>
                <h5 v-if="!detalleUsuario.error">Datos como empleado</h5>
                <form class="row" v-if="!detalleUsuario.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Estado</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.estado }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Municipio</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.municipio }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Parroquia</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.parroquia }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>División</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.division }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Cargo</label>
                    <select class="form-control">
                      <option>@{{ detalleUsuario.data.cargo }}</option>
                    </select>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Fecha de Ingreso</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.fecha_ingreso">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Fecha de Egreso</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.fecha_egreso">
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn"
                        data-dismiss="modal"
                        type="button">Ok</button>
              </div>
            </div>
          </div>
        </div>
        <div id="modal-asignar-menu" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Asignar los Menus</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleMenu.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del usuario, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <h5>Datos del Empleado</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoUsuario.nombre">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Division</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoUsuario.Ddivision">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Cargo</label>
                    <input class="form-control" type="text" disabled v-bind:value="infoUsuario.Dcargo">
                  </div>
                </form>
                <h5 v-if="permisoRRHH">Usuarios</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoRRHH">
                    <label>Crear Usuarios</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="crUsuario"
                             type="checkbox"
                             v-model="crUsuario.checked"
                             v-on:change="Crear(crUsuario.c, crUsuario.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="crUsuario"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoRRHH">
                    <label>Consultar Usuarios</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="coUsuario"
                             type="checkbox"
                             v-model="coUsuario.checked"
                             v-on:change="Consultar(coUsuario.r, coUsuario.u, modUsuario.u, coUsuario.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="coUsuario"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoRRHH">
                    <label>Modificar Usuarios</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modUsuario"
                             type="checkbox"
                             v-model="modUsuario.checked"
                             v-on:change="Modificar(modUsuario.r, modUsuario.u, coUsuario.r, modUsuario.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modUsuario"></label>
                    </div>
                  </div>
                </form>
                <h5 v-if="permisoContraloria">Clientes</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Crear Clientes</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="crCliente"
                             type="checkbox"
                             v-model="crCliente.checked"
                             v-on:change="Crear(crCliente.c, crCliente.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="crCliente"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Consultar Clientes</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="coCliente"
                             type="checkbox"
                             v-model="coCliente.checked"
                             v-on:change="Consultar(coCliente.r, coCliente.u, modCliente.u, coCliente.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="coCliente"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Modificar Clientes</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modCliente"
                             type="checkbox"
                             v-model="modCliente.checked"
                             v-on:change="Modificar(modCliente.r, modCliente.u, coCliente.r, modCliente.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modCliente"></label>
                    </div>
                  </div>
                </form>

                <h5 v-if="permisoContraloria">Proyectos</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Crear Proyectos</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="crProyecto"
                             type="checkbox"
                             v-model="crProyecto.checked"
                             v-on:change="Crear(crProyecto.c, crProyecto.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="crProyecto"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Lista de Proyecto</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="coProyecto"
                             type="checkbox"
                             v-model="coProyecto.checked"
                             v-on:change="Consultar(coProyecto.r, coProyecto.u, modProyecto.u, coProyecto.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="coProyecto"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Modificar Proyectos</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modProyecto"
                             type="checkbox"
                             v-model="modProyecto.checked"
                             v-on:change="Modificar(modProyecto.r, modProyecto.u, coProyecto.r, modProyecto.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modProyecto"></label>
                    </div>
                  </div>
                </form>
                 <h5>Detalle de Facturacion</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Crear Detalles de Facturacion</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="crFact"
                             type="checkbox"
                             v-model="crFact.checked"
                             v-on:change="crFactura(crFact.c, crFact.r, crFact.u, crFact.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="crFact"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch">
                    <label>Ver Datos de Facturacion</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="verFact"
                             type="checkbox"
                             v-model="verFact.checked"
                             v-on:change="verFactura(verFact.c, verFact.r, verFact.u, verFact.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="verFact"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoContraloria">
                    <label>Modificar Detalles de Facturacion</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modFact"
                             type="checkbox"
                             v-model="modFact.checked"
                             v-on:change="modFactura(modFact.c, modFact.r, modFact.u, modFact.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modFact"></label>
                    </div>
                  </div>
                </form>
                <h5>Asignar Proyectos</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoSocio">
                    <label>Ver Personal en los Proyectos</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="verAsigna"
                             type="checkbox"
                             v-model="verAsigna.checked"
                             v-on:change="verAsignar(verAsigna.c, verAsigna.r, verAsigna.u, verAsigna.d, verAsigna.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="verAsigna"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoEncargado">
                    <label>Asignar Personal (Unicamente para directores o encargados de la division)</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modAsigna"
                             type="checkbox"
                             v-model="modAsigna.checked"
                             v-on:change="modAsignar(modAsigna.c, modAsigna.r, modAsigna.u, modAsigna.d, modAsigna.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modAsigna"></label>
                    </div>
                  </div>
                </form>

                <h5>Horas Cargables</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch">
                    <label>Cargar Horas</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="caHora"
                             type="checkbox"
                             v-model="caHora.checked"
                             v-on:change="caHoras(caHora.c, caHora.r, caHora.u, caHora.d, caHora.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="caHora"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch" v-if="permisoSocio">
                    <label>Modificar Horas Cargadas</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="modAsigna"
                             type="checkbox"
                             v-model="modAsigna.checked"
                             v-on:change="modAsignar(modAsigna.c, modAsigna.r, modAsigna.u, modAsigna.d, modAsigna.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="modAsigna"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch">
                    <label>Eliminar Horas Cargadas (Para socios,directores o encargados de division)</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="eliHora"
                             type="checkbox"
                             v-model="eliHora.checked"
                             v-on:change="eliHoras(eliHora.c, eliHora.r, eliHora.u, eliHora.d, eliHora.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="eliHora"></label>
                    </div>
                  </div>
                </form>
                <h5>Horas No Cargables</h5>
                <form class="row" v-if="!detalleMenu.error">
                  <div class="custom-control custom-switch" v-if="permisoRRHH">
                    <label>Conceptos de Horas no Cargables</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="conHoraNoC"
                             type="checkbox"
                             v-model="conHoraNoC.checked"
                             v-on:change="conHorasNoC(conHoraNoC.u, conHoraNoC.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="conHoraNoC"></label>
                    </div>
                  </div>
                  <div class="custom-control custom-switch">
                    <label>Cargar Horas no Cargables</label>
                    <div class="form-group form-check col-12">
                      <input class="custom-control-input"
                             id="carHoraNoC"
                             type="checkbox"
                             v-model="carHoraNoC.checked"
                             v-on:change="carHorasNoC(carHoraNoC.u, carHoraNoC.menu, $event)">
                      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="custom-control-label" for="carHoraNoC"></label>
                    </div>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn"
                        data-dismiss="modal"
                        type="button">Ok</button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/buscarUsuario.js') }}"></script>

    </body>
</html>
