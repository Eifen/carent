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
            <h3>Estas Creando a un Nuevo Cliente</h3>
             <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Agregar Socio encargado por</option>
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
              <!--Al hacer clic se invoca el metodo buscar de crearCliente.js y abre la modal-->
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
                        <!-- Se llena la tabla con los valores que tiene usuarios.registros obtenidos de crearCliente.js -->
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
                  <div class="form-group col-12 col-sm-6" v-show="usuarios.mostrar">
                    <label>Código</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.codigo">
                  </div>
                  <div class="form-group col-12 col-sm-6" v-show="usuarios.mostrar">
                    <label>Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuario.data.nombre">
                  </div>
            </form>
          </form>
          <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearchG.selectG.disabled"
                        v-model="formSearchG.selectG.value"
                        v-on:change="tipoFiltroG">
                  <option value="" selected disabled>Agregar Gerente encargado por</option>
                  <option value="1">Código de usuario</option>
                  <option value="2">Cédula</option>
                  <option value="4">Primer o segundo nombre</option>
                  <option value="5">Primer o segundo apellido</option>
                </select>
              </div>
              <div class="form-group col-12 col-md-6">
                <input class="form-control inputSearch"
                       ref="inputSearchG"
                       type="text"
                       v-bind:disabled="formSearchG.inputSearchG.disabled"
                       v-on:keyup="evaluarCampoG('inputSearchG', $event)"
                       v-model="formSearchG.inputSearchG.value">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-md-2">
                <!--Al hacer clic se invoca el metodo buscarG de crearCliente.js y abre la modal-->
                <button class="btn btn-primary"
                        type="button"
                        v-bind:disabled="formSearchG.submitG.disabled"
                        v-html="formSearchG.submitG.html"
                        v-on:click="buscarG">                          
                </button>
              </div>
              <div id="modal-detalle-usuarioG" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                  <div class="modal-content">
                    <table class="table">
                      <thead>
                        <tr>
                          <th scope="col">Código</th>
                          <th scope="col">Cédula</th>
                          <th scope="col">Nombre</th>
                          <th scope="col">Cargo</th>
                          <th scope="col"></th>
                        </tr>
                      </thead>
                      <tbody>
                        <!-- Se llena la tabla con los valores que tiene usuariosG.registros obtenidos de crearCliente.js -->
                        <tr v-for="usuario in usuariosG.registros">
                          <th scope="row">@{{ usuario.codigo }}</th>
                          <td>@{{ usuario.cedula }}</td>
                          <td>@{{ usuario.nombre }}</td>
                          <td>@{{ usuario.cargo }}</td>
                          <td>
                            <i class="fas fa-check-square" data-dismiss="modal" v-on:click="SelecionarUsuarioG(usuario.id, $event)"></i>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            <form class="row" v-if="!detalleUsuarioG.error">
                  <div class="form-group col-12 col-sm-6" v-show="usuariosG.mostrar">
                    <label>Código</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuarioG.data.codigo">
                  </div>
                  <div class="form-group col-12 col-sm-6" v-show="usuariosG.mostrar">
                    <label>Nombre</label>
                    <input class="form-control" type="text" disabled v-bind:value="detalleUsuarioG.data.nombre">
                  </div>
            </form>
          </form>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="rif">Rif<span class="campo-obligatorio">*</span> </label>
                <the-mask mask="F- MMMMMMMMMM" :tokens="hexTokens" 
                          class="form-control"
                          id="rif"
                          v-bind:disabled="form.rif.disabled"
                          v-model="form.rif.value"
                          v-on:keyup="valuesForm"
                          type="text"></the-mask>
                <small id="rifHelp" class="form-text">V-, E-, P-, G-, J-, C-</small>
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
                <label for="razon_social">Nombre o Razón social<span class="campo-obligatorio">*</span></label>
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
                <label for="estadofi">Estado<span class="campo-obligatorio">*</span></label>
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
                <label for="ciudad_fiscal">Ciudad<span class="campo-obligatorio">*</span></label>
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
                <label for="avenida_calle_fiscal">Avenida o Calle<span class="campo-obligatorio">*</span></label>
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
                <label for="piso_fiscal">Pisó<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="piso_ficalHelp"
                       class="form-control text-lowercase"
                       id="piso_fiscal"
                       v-bind:disabled="form.piso_fiscal.disabled"
                       v-mask="'XXX'"
                       v-model="form.piso_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="piso_ficalHelp" class="form-text text-muted">ejemplo: 24</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="numero_fiscal">Número</label>
                <input aria-describedby="numero_fiscal"
                       class="form-control text-lowercase"
                       v-mask="'XXXXX'"
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
                       v-mask="'###########'"
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
            <div class="row justify-content-center wrapper-subtmit">
              <div class="col-12 col-md-6 col-lg-4">
                <!--Al hacer clic se invoca el metodo crear de crearCliente.js y envia los valores de las variables para su modificacion-->
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