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
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
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
                  <th scope="col"></th>
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
                  <td>
                    <a v-bind:href="'/formModificarUsuario/'+usuario.id" target="_self">
                       <i class="far fa-edit"></i>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>

        </div>

        <div id="modal-detalle-usuario" class="modal fade" tabindex="-1" role="dialog">
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
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.estado">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Municipio</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.municipio">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Parroquia</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.parroquia">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>División</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.division">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Cargo</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.cargo">
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
