<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
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
                <h5>Seleccionar menus</h5>
                <form class="row">
                  <div class="form-group col-12 col-sm-6">
                    <multiselect  :multiple="true"
                                  :group-select="true" 
                                  :options="comboMenus"
                                  :clear-on-select="false"
                                  :close-on-select="false"
                                  group-values="value"
                                  group-label="menus"                                 
                                  placeholder="Seleccione..." 
                                  v-model="selectMenus.value"
                                  track-by="descripcion"
                                  label="descripcion">
                      <span slot="noResult">No se encontraron menus.</span>
                    </multiselect>
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
