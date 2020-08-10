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
        <link href="{{ mix('/css/detalleFactcliente.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>
      <div id="detalleFactcliente" class="container-fluid" v-on:submit.prevent="buscar">
        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
            <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Buscar Clientes por</option>
                  <option value="1">Código del Cliente</option>
                  <option value="2">Nombre o Razón Social</option>
                  <option value="3">Rif</option>
                </select>
              </div>
              <div class="form-group col-12 col-md-6">
                <!-- Se invoca el metodo evaluarCampo de detalleFactclienteCliente.js y abre la modal-->
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
              <div id="modal-detalle-cliente" class="modal fade" tabindex="-1" role="dialog" v-cloak>
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Código Cliente</th>
                          <th scope="col">Rif</th>
                          <th scope="col">Razón Social </th>
                          <th scope="col">Correo Electrónico</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- Se llena la tabla con los valores que tiene clientes.registro obtenidos de detalleFactclienteCliente.js -->
                        <tr v-for="cliente in clientes.registros">
                          <th scope="row">@{{ cliente.codigo  }}</th>
                          <td>@{{ cliente.rif }}</td>
                          <td>@{{ cliente.razon_social }}</td>
                          <td>@{{ cliente.email_fiscal }}</td>
                          <td>
                            <i class="fas fa-check-square" v-on:click="SelecionarCliente(cliente.id, $event)"></i> <!-- Se invoca el metodo SelecionarCliente de detalleFactclienteCliente.js -->
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <form class="row" v-if="!detalleCliente.error">
                  <div class="form-group col-12 col-sm-6" v-show="clientes.mostrar">
                    <label>Código</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.codigo">
                  </div>
                  <div class="form-group col-12 col-sm-6" v-show="clientes.mostrar">
                    <label>Razón social</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleCliente.data.razon_social">
                  </div>
            </form>
          </form>
          <!-- Al hacer clic Se invoca el metodo Selecionar de detalleFactclienteCliente.js y abre la modal-->
          <form class="row">
            <div class="form-group col-12 col-md-6">
              <button class="btn btn-primary"
                type="button"
                v-bind:disabled="formSearchP.submit.disabled"
                v-html="formSearchP.submit.html"
                v-on:click="Selecionar">
              </button>
            </div>
            <div id="modal-detalle-clienteProy" class="modal fade" tabindex="-1" role="dialog" v-cloak>
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Descripción</th>
                          <th scope="col">Fecha de Contratación</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="clienteProy in clienteProy.registros">
                          <th scope="row">@{{ clienteProy.descripcion  }}</th>
                          <td>@{{ clienteProy.fecha_contratacion }}</td>
                          <td>
                            <i class="fas fa-check-square" v-on:click="SelecionarClienteProy(clienteProy.id, $event)"></i>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <div class="form-group col-12 col-sm-6" v-show="clienteProy.mostrar">
                <input class="form-control" type="text" disabled v-bind:value="detalleClienteProy.data.descripcion">
              </div>
          </form>
          <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertFormP.class" role="alert" v-if="alertFormP.show" v-html="alertFormP.message"></div>
              </div>
            </div>
            <h5>Dirección para Entrega de Facturas</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-6">
                <label for="estadofa">Estado <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="estadoHelp"
                        class="form-control"
                        id="estadofa"
                        v-bind:data-validar="form.estadofa.validar"
                        v-bind:disabled="form.estadofa.disabled"
                        v-model="form.estadofa.value"
                        v-on:change="municipiosfa"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="estadofa.id" v-for="estadofa in comboEstadosfa">@{{ estadofa.estado }}</option>
                </select>
                <small id="estadofaHelp" class="form-text text-muted">Estado de la oficina en donde se desempeña</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estadofa">Municipio <span  class="campo-obligatorio">*</span></label>
                <select aria-describedby="municipiofaHelp"
                        class="form-control"
                        id="municipiofa"
                        v-bind:data-validar="form.municipiofa.validar"
                        v-bind:disabled="form.municipiofa.disabled"
                        v-model="form.municipiofa.value"
                        v-on:change="parroquiasfa"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="municipiofa.id" v-for="municipiofa in comboMunicipiosfa">@{{ municipiofa.municipio }}</option>
                </select>
                <small id="estadofaHelp" class="form-text text-muted" v-html="form.municipiofa.help"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estadofa">Parroquia <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="parroquiafaHelp"
                        class="form-control"
                        id="parroquiafa"
                        v-bind:data-validar="form.parroquiafa.validar"
                        v-bind:disabled="form.parroquiafa.disabled"
                        v-model="form.parroquiafa.value"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="parroquiafa.id" v-for="parroquiafa in comboParroquiasfa">@{{ parroquiafa.parroquia }}</option>
                </select>
                <small id="estadofaHelp" class="form-text text-muted" v-html="form.parroquiafa.help"></small>
                <div class="mensaje"></div>
              </div>
            <div class="form-group col-12 col-sm-6">
                <label for="ciudad_factura">Ciudad<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="ciudad_facturaHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="ciudad_factura"
                       v-bind:disabled="form.ciudad_factura.disabled"
                       v-model="form.ciudad_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="ciudad_factura" class="form-text text-muted">Ejemplo: Caracas</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="avenida_calle_factura">Avenida o Calle <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="avenida_calle_facturaHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="avenida_calle_factura"
                       v-bind:disabled="form.avenida_calle_factura.disabled"
                       v-model="form.avenida_calle_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="avenida_calle_facturaHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="edificio_quinta_factura">Quinta o Edificio<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="edificio_quinta_facturaHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="edificio_quinta_factura"
                       v-bind:disabled="form.edificio_quinta_factura.disabled"
                       v-model="form.edificio_quinta_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="edificio_quinta_facturaHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="piso_factura">Piso<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="piso_facturaHelp"
                       class="form-control text-lowercase"
                       id="piso_factura"
                       v-mask="'XXX'"
                       v-bind:disabled="form.piso_factura.disabled"
                       v-model="form.piso_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="piso_facturaHelp" class="form-text text-muted">ejemplo: 24</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="numero_factura">Número</label>
                <input aria-describedby="numero_factura"
                       class="form-control text-lowercase"
                       id="numero_factura"
                       v-bind:disabled="form.numero_factura.disabled"
                       v-mask="'XXXXX'"
                       v-model="form.numero_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="numero_factura" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="telefono_factura">Nº de Teléfono de Facturación Principal</label>
                <input aria-describedby="telefono_facturaHelp"
                       class="form-control"
                       id="telefono_factura"
                       v-bind:disabled="form.telefono_factura.disabled"
                       v-mask="'(####) - ### ####'"
                       v-model="form.telefono_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="telefono_facturaHelp" class="form-text text-muted">Ejemplo: 0424-1234567</small>
                <div class="mensaje"></div>
              </div>

               <div class="form-group col-12 col-sm-6">
                <label for="correo_factura">Email De Factura<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="correo_facturaHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="correo_factura"
                       v-bind:disabled="form.correo_factura.disabled"
                       v-model="form.correo_factura.value"
                       v-on:keyup="valuesForm"
                       type="email">
                <small id="correo_facturaHelp" class="form-text text-muted">Ejemplo: correo@dominio.com</small>
                <div class="mensaje"></div>
              </div>
            </form>
            <div class="row justify-content-center wrapper-subtmit" v-if="permisoActualizar">
              <div class="col-12 col-md-6 col-lg-4">
                <button class="btn subtmit"
                        type="button"
                        v-on:click="actualizar"
                        v-bind:disabled="submitActualizar.disabled"
                        v-html="submitActualizar.content"
                        v-if="submitActualizar.show"></button>
              </div>
            </div>
            <div class="row justify-content-center wrapper-subtmit" v-if="permisoCrear">
              <div class="col-12 col-md-6 col-lg-4">
                <!--Al hacer clic se invoca el metodo crear de detalleFactCliente.js -->
                <button class="btn"
                        type="button"
                        v-on:click="crear"
                        v-bind:disabled="submitCrear.disabled"
                        v-html="submitCrear.content"
                        v-if="submitCrear.show"></button>
              </div>
            </div>
            <div class="row justify-content-center wrapper-refrescar" v-if="refreshForm">
              <div class="col-12 col-md-6 col-lg-4">
                <button class="btn"
                        type="button"
                        v-on:click="refreshView">Nuevo Detalle de Facturación</button>
              </div>
            </div>
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script src="{{ mix('/js/detalleFactcliente.js') }}"></script>
    </body>
</html>
