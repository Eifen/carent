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
        <link href="{{ mix('/css/nuevoCliente.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="nuevoCliente" class="container-fluid" v-on:keypress="keyboard">
        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <h3>Estas Creando a un Nuevo Cliente</h3>
             <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Agregar Socio Por</option>
                  <option value="1">Código de usuario</option>
                  <option value="2">Cédula</option>
                  <option value="3">Primer o segundo nombre</option>
                  <option value="4">Primer o segundo apellido</option>
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
              <div id="modal-detalle-usuario" class="modal fade" tabindex="-1" role="dialog" v-cloak>
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
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </form>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
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
                       disabled
                       type="text">
                <small id="codigoClienteHelp" class="form-text">Ejemplo: 2209</small>
                <div class="mensaje"></div>
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
                <label for="pais">Pais<span class="campo-obligatorio">*</span></label>
                <v-select @input="pais"
                :options="comboPaises"
                          label="nombre"                          
                          id="pais"
                          v-model="form.pais.value"
                          v-bind:data-validar="form.pais.validar"
                          v-bind:disable="form.pais.disable"
                          placeholder="Seleccione..."
                          type="text"></v-select>
              </div>
              <div class="form-group col-12 col-sm-6"></div>
              <div class="form-group col-24 col-sm-12">
                <label for="direccion">Dirección<span class="campo-obligatorio">*</span></label>
                <textarea :disabled="form.direccion.disabled"
                          :maxlength="form.direccion.maxlength"
                          class="form-control form-control-sm"
                          data-validar="true"
                          rows="3"
                          v-model="form.direccion.value"
                          data-min="10"></textarea>
              </div>              
              <div class="form-group col-12 col-sm-6">
                <label for="telefono_fiscal">Nº de Teléfono Principal<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="telefono_fiscalHelp"
                       class="form-control"
                       id="telefono_fiscal"
                       v-bind:disabled="form.telefono_fiscal.disabled"
                       v-mask="'+ ################'"
                       v-model="form.telefono_fiscal.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="telefono_fiscalHelp" class="form-text text-muted">Ejemplo: + 584241234567</small>
                <div class="mensaje"></div>
              </div>

              <div class="form-group col-12 col-sm-6">
                <label for="pagina_web">Pagina web</label>
                <input aria-describedby="pagina_webHelp"
                       class="form-control"
                       id="pagina_web"
                       v-bind:disabled="form.pagina_web.disabled"
                       v-model="form.pagina_web.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="pagina_webHelp" class="form-text text-muted"></small>
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
