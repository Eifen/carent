<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        @vite('resources/less/cliente/buscarCliente.less')

    </head>
    <body>

      <div id="buscarCliente" class="container-fluid" v-on:submit.prevent="buscar">
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
                  <option value="1">Código  del Cliente</option>
                  <option value="2">Nombre o Razón Social</option>
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
                <!--Al hacer clic se invoca el metodo buscar de buscarCliente.js -->
                <button class="btn btn-primary"
                        type="button"
                        v-bind:disabled="formSearch.submit.disabled"
                        v-html="formSearch.submit.html"
                        v-on:click="buscar">
                </button>
              </div>
            </form>
          </div>
          <div class="col-12" v-show="clientes.mostrar">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Código</th>
                  <th scope="col">Razón Social </th>
                  <th scope="col">Correo Electrónico</th>
                  <th scope="col"></th>
                  <th scope="col" v-if="permisoActualizar"></th>
                </tr>
              </thead>
              <tbody>
                <!-- Se llena la tabla con los valores que tiene clientes.registro obtenidos de buscarCliente.js -->
                <tr v-for="cliente in clientes.registros">
                  <th scope="row">@{{ cliente.codigo }}</th>
                  <td>@{{ cliente.razon_social }}</td>
                  <td>@{{ cliente.email_fiscal }}</td>
                  <td>
                    <span @click="mostrarDetalleCliente(cliente.id, $event)">
                      <i class="fas fa-search-plus"></i><!-- Se invoca el metodo mostrarDetalleCliente de buscarCliente.js y abre una modal -->
                    </span>
                  </td>
                  <td v-if="permisoActualizar">
                    <a v-bind:href="'/formModificarCliente/'+cliente.id" target="_self">
                       <i class="far fa-edit"></i><!-- Se invoca el metodo formModificarCliente de buscarCliente.js y te lleva a la ventana de modificacion de cliente -->
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <!-- modal que inicia mostrarDetalleCliente-->
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7" v-if="alert.mostrar">
            <div class="alert alert-warning text-center" v-html="alert.message"></div>
          </div>
        </div>
        <div id="modal-detalle-cliente" class="modal fade" tabindex="-1" role="dialog" v-cloak ref="modal-detalle-cliente">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle del Cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleCliente.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del cliente, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <h5 v-if="!detalleCliente.error">Socio Encargado</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Código</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.codigoU">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.nombre">
                  </div>
                </form>
                <h5 v-if="!detalleCliente.error">Datos del Cliente</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Código del Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.codigo">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Rif</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.rif">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nit</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.nit">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nombre o Razón Social</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.razon_social">
                  </div>
                </form>
                <h5 v-if="!detalleCliente.error">Dirección Fiscal</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Pais</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.pais">
                  </div>
                  <div class="form-group col-12 col-sm-6"></div>
                  <div class="form-group col-24 col-sm-12">
                    <label>Dirección</label>
                    <textarea class="form-control" type="text" rows="3" disabled v-bind:value="detalleCliente.data.direccion"></textarea>
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Teléfono Principal</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.telefono_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Email Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.email_fiscal">
                  </div>
                  <div class="form-group col-24 col-sm-12">
                    <label>Pagina Web</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.pagina_web">
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button class="btn btn-primary"
                        data-bs-dismiss="modal"
                        type="button">Ok</button>
              </div>
            </div>
          </div>
        </div>

      </div>

      @vite('resources/js/cliente/buscarCliente.js')

    </body>
</html>
