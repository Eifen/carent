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
        <link href="{{ mix('/css/nuevoCliente.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="nuevoCliente" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <h3>Estas creando a un nuevo Cliente</h3>
             <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Buscar Socio por</option>
                  <option value="1">Código de usuario</option>
                  <option value="2">Cédula</option>
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
                        v-on:click="buscar">                          
                </button>
              </div>
              <div id="modal-detalle-usuario" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Código</th>
                          <th scope="col">Cédula</th>
                          <th scope="col">Nombre</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="usuario in usuarios.registros">
                          <th scope="row">@{{ usuario.codigo }}</th>
                          <td>@{{ usuario.cedula }}</td>
                          <td>@{{ usuario.nombre }}</td>
                          <td>
                            <i class="fas fa-check-square" data-dismiss="modal" v-on:click="SelecionarUsuario(usuario.id, $event)"></i>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <form class="row" v-if="!detalleUsuario.error">
                  <div class="form-group col-12 col-sm-6">
                    <label>Codigo</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.codigo">
                  </div>
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
            </form>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group form-check col-12">
                <input class="form-check-input"
                       id="empleado"
                       type="checkbox"
                       v-model="form.empleado.checked"
                       v-on:change="esEmpleado">
                <label class="form-check-label label-warning" for="empleado">Crear Nuevo Cliente</label>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="codigoCliente">Código del Cliente <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="codigoClienteHelp"
                       class="form-control"
                       data-validar="true"
                       data-only-number="true"
                       id="codigoCliente"
                       v-bind:disabled="form.codigoCliente.disabled"
                       v-model="form.codigoCliente.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="codigoClienteHelp" class="form-text">Ejemplo: 2209</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="rif">Rif<span class="campo-obligatorio">*</span> </label>
                <input class="form-control"
                       data-formated-number="true"
                       data-only-number="true"
                       data-validar="true"  
                       id="rif"
                       v-bind:disabled="form.rif.disabled"
                       v-model="form.rif.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="rifHelp" class="form-text">Ejemplo: 123456789</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="nit">Nit</label>
                <input class="form-control"
                       data-formated-number="true"
                       data-only-number="true"
                       data-validar="true"
                       id="nit"
                       v-bind:disabled="form.nit.disabled"
                       v-model="form.nit.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="nitHelp" class="form-text">Ejemplo: 123456789</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="razon_social">Nombre o Razon social<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="razon_socialHelp"
                       class="form-control text-lowercase"
                       data-min="3"
                       data-validar="true"
                       id="razon_social"
                       v-bind:disabled="form.razon_social.disabled"
                       v-model="form.razon_social.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="razon_socialHelp" class="form-text text-muted">Ejemplo: Banco...</small>
                <div class="mensaje"></div>
              </div>
            </form>
            <h5>Direccion Fiscal</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-6">
                <label for="estadofi">Estado <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="estadoHelp"
                        class="form-control"
                        id="estadofi"
                        v-bind:data-validar="form.estadofi.validar"
                        v-bind:disabled="form.estadofi.disabled"
                        v-model="form.estadofi.value"
                        v-on:change="municipiosfi"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="estadofi.id" v-for="estadofi in comboEstadosfi">@{{ estadofi.estado }}</option>
                </select>
                <small id="estadofiHelp" class="form-text text-muted">Estado de la oficina en donde se desempeña</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estadofi">Municipio <span  class="campo-obligatorio">*</span></label>
                <select aria-describedby="municipiofiHelp"
                        class="form-control"
                        id="municipiofi"
                        v-bind:data-validar="form.municipiofi.validar"
                        v-bind:disabled="form.municipiofi.disabled"
                        v-model="form.municipiofi.value"
                        v-on:change="parroquiasfi"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="municipiofi.id" v-for="municipiofi in comboMunicipiosfi">@{{ municipiofi.municipio }}</option>
                </select>
                <small id="estadofiHelp" class="form-text text-muted" v-html="form.municipiofi.help"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estadofi">Parroquia <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="parroquiafiHelp"
                        class="form-control"
                        id="parroquiafi"
                        v-bind:data-validar="form.parroquiafi.validar"
                        v-bind:disabled="form.parroquiafi.disabled"
                        v-model="form.parroquiafi.value"
                        v-on:click="limpiarMensajeError"
                        type="text">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="parroquiafi.id" v-for="parroquiafi in comboParroquiasfi">@{{ parroquiafi.parroquia }}</option>
                </select>
                <small id="estadofiHelp" class="form-text text-muted" v-html="form.parroquiafi.help"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="ciudad_fiscal">Ciudad <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="ciudad_fiscalHelp"
                       class="form-control text-lowercase"                       
                       data-validar="true"
                       id="ciudad_fiscal"
                       v-bind:disabled="form.ciudad_fiscal.disabled"
                       v-model="form.ciudad_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="ciudad_fiscal" class="form-text text-muted">Ejemplo: caracas</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="avenida_calle_fiscal">Avenida o Calle <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="avenida_calle_fiscalHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="avenida_calle_fiscal"
                       v-bind:disabled="form.avenida_calle_fiscal.disabled"
                       v-model="form.avenida_calle_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="avenida_calle_fiscalHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="edificio_quinta_fiscal">Quinta o Edificio<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="edificio_quinta_fiscalHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="edificio_quinta_fiscal"
                       v-bind:disabled="form.edificio_quinta_fiscal.disabled"
                       v-model="form.edificio_quinta_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="edificio_quinta_fiscalHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>


              <div class="form-group col-12 col-sm-6">
                <label for="piso_fiscal">Piso<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="piso_ficalHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="piso_fiscal"
                       v-bind:disabled="form.piso_fiscal.disabled"
                       v-model="form.piso_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="piso_ficalHelp" class="form-text text-muted">ejemplo: 24E</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="numero_fiscal">Numero</label>
                <input aria-describedby="numero_fiscal"
                       class="form-control text-lowercase"
                       data-formated-number="true"
                       data-only-number="true"
                       data-validar="true"
                       id="numero_fiscal"
                       v-bind:disabled="form.numero_fiscal.disabled"
                       v-model="form.numero_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="numero_fiscal" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="telefono_fiscal">Nº de Teléfono Principal</label>
                <input aria-describedby="telefono_fiscalHelp"
                       class="form-control"
                       id="telefono_fiscal"
                       v-bind:disabled="form.telefono_fiscal.disabled"
                       v-mask="'(####) - ### ####'"
                       v-model="form.telefono_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="telefono_fiscalHelp" class="form-text text-muted">Ejemplo: 0424-1234567</small>
                <div class="mensaje"></div>
              </div>
                        
              <div class="form-group col-12 col-sm-6">
                <label for="fax_fiscal">Nº de Fax</label>
                <input aria-describedby="fax_fiscalHelp"
                       class="form-control"
                       id="fax_fiscal"
                       v-bind:disabled="form.fax_fiscal.disabled"
                       v-model="form.fax_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="fax_fiscalHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

               <div class="form-group col-12 col-sm-6">
                <label for="email_fiscal">Email Cliente <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="email_fiscalHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="email_fiscal"
                       v-bind:disabled="form.email_fiscal.disabled"
                       v-model="form.email_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="email">
                <small id="email_fiscalHelp" class="form-text text-muted">Ejemplo: correo@dominio.com</small>
                <div class="mensaje"></div>
              </div>            
            </form>
            <h5>Descripción para entrega de facturas</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-6">
                <label for="descripcion_factura">Descripcion del trabajo <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="descripcion_facturaHelp"
                       class="form-control text-lowercase"
                       data-min="3"
                       data-validar="true"
                       id="descripcion_factura"
                       v-bind:disabled="form.descripcion_factura.disabled"
                       v-model="form.descripcion_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="descripcion_facturaHelp" class="form-text text-muted">Ejemplo: Inventario en la empresa...</small>
                <div class="mensaje"></div>
              </div>
            </form>
            <h5>Dirección para entrega de facturas</h5>
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
                <label for="ciudad_factura">Ciudad <span class="campo-obligatorio">*</span></label>
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
                       data-validar="true"
                       id="piso_factura"
                       v-bind:disabled="form.piso_factura.disabled"
                       v-model="form.piso_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="piso_facturaHelp" class="form-text text-muted">ejemplo: 24E</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="numero_factura">Numero</label>
                <input aria-describedby="numero_factura"
                       class="form-control text-lowercase"
                       data-formated-number="true"
                       data-only-number="true"
                       data-validar="true"
                       id="numero_factura"
                       v-bind:disabled="form.numero_factura.disabled"
                       v-model="form.numero_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="numero_factura" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="telefono_factura">Nº de Teléfono de Facturacion Principal</label>
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
                <label for="fax_factura">Nº de Fax</label>
                <input aria-describedby="fax_facturaHelp"
                       class="form-control"
                       id="fax_factura"
                       v-bind:disabled="form.fax_factura.disabled"
                       v-model="form.fax_factura.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="fax_facturaHelp" class="form-text text-muted"></small>
                <div class="mensaje"></div>
              </div>

               <div class="form-group col-12 col-sm-6">
                <label for="correo_factura">Email De Facturaa <span class="campo-obligatorio">*</span></label>
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
            <div class="row justify-content-center wrapper-subtmit">
              <div class="col-12 col-md-6 col-lg-4">
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
                        v-on:click="refreshView">Agregar un nuevo cliente</button>
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
      <script src="{{ mix('/js/nuevoCliente.js') }}"></script>
    </body>
</html>