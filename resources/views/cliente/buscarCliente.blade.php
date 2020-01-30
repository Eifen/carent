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
        <link href="{{ mix('/css/buscarCliente.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="buscarCliente" class="container-fluid" v-on:submit.prevent="buscar">
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
                  <option value="1">Codigo del cliente</option>
                  <option value="2">Nombre o Razon Social </option>
                  <option value="3">Descripcion</option>
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
                  <th scope="col">Razon social</th>
                  <th scope="col">Descripcion</th>
                  <th scope="col"></th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="cliente in clientes.registros">
                  <th scope="row">@{{ cliente.codigo }}</th>
                  <td>@{{ cliente.razon_social }}</td>
                  <td>@{{ cliente.descripcion_factura }}</td>
                  <td>
                    <i class="fas fa-search-plus" v-on:click="mostrarDetalleCliente(cliente.id, $event)"></i>
                  </td>
                  <td>
                    <a v-bind:href="'/formModificarCliente/'+cliente.id" target="_self">
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
        <div id="modal-detalle-cliente" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Detalle del cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div v-if="detalleCliente.error" class="alert alert-warning">
                  Ocurrio un error al intentar mostrar el detalle del cliente, por favor intente nuevamente o comuníquese con el administrador del sistema!
                </div>
                <h5 v-if="!detalleCliente.error">Encargado</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Código del socio encargado</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.codigoU">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Primer Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.nombre_1">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Segundo Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.nombre_2">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Primer Apellido</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.apellido_1">
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
                    <label>Nombre o Razon social</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.razon_social">
                  </div>
                </form>
                <h5 v-if="!detalleCliente.error">Direccion Fiscal</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Estado</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.estadofi">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Municipio</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.municipiofi">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Parroquia</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.parroquiafi">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Ciudad</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.ciudad_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Avenida o Calle</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.avenida_calle_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Quinta o Edificio</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.edificio_quinta_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Piso</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.piso_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Numero</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.numero_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Teléfono Principal</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.telefono_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Fax</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.fax_fiscal">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Email Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.email_fiscal">
                  </div>
                </form>
                <h5 v-if="!detalleCliente.error">Descripción para entrega de facturas</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Descripcion del trabajo</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.descripcion_factura">
                  </div>
                </form>
                <h5 v-if="!detalleCliente.error">Dirección para entrega de facturas</h5>
                <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Estado</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.estadofa">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Municipio</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.municipiofa">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Parroquia</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.parroquiafa">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Ciudad</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.ciudad_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Avenida o Calle</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.avenida_calle_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Quinta o Edificio</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.edificio_quinta_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Piso</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.piso_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Numero</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.numero_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Teléfono Principal</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.telefono_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Nº de Fax</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.fax_factura">
                  </div>
                  <div class="form-group col-12 col-sm-6">
                    <label>Email Cliente</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.correo_factura">
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn btn-primary"
                        data-dismiss="modal"
                        type="button">Ok</button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/buscarCliente.js') }}"></script>

    </body>
</html>
