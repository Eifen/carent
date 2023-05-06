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
        <link href="{{ mix('/css/formConceptosHorasNoCargables.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-9 col-lg-7 wrapper-form" v-if="formFiltro.mostrar">
            <h5>Filtros de búsqueda</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-8">
                <label for="descripcion">Concepto / Descripción</label>
                <input aria-describedby="descripcionHelp"
                       class="form-control form-control-sm"
                       id="descripcion"
                       maxlength="250"
                       v-bind:disabled="formFiltro.descripcion.disabled"
                       v-model.trim="formFiltro.descripcion.value"
                       type="text">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="estatus">Estatus</label>
                <select aria-describedby="estatusHelp"
                        class="form-control form-control-sm"
                        id="estatus"
                        data-validar="true"
                        v-bind:disabled="formFiltro.estatus.disabled"
                        v-model="formFiltro.estatus.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" selected>Seleccione...</option>
                  <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
                </select>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn filtrar"
                        type="button"
                        v-on:click="buscar"
                        v-bind:disabled="formFiltro.btn.filtrar.disabled"
                        v-html="formFiltro.btn.filtrar.html"></button>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn limpiar_filtro"
                        type="button"limpiarFiltro
                        v-on:click="limpiarFiltro"
                        v-bind:disabled="formFiltro.btn.limpiarFiltro.disabled"
                        v-html="formFiltro.btn.limpiarFiltro.html"></button>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn btn-success"
                        type="button"
                        v-on:click="crearNuevo"
                        v-bind:disabled="formFiltro.btn.crear.disabled"
                        v-html="formFiltro.btn.crear.html"></button>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="formFiltro.mostrar">

            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Concepto</th>
                  <th scope="col">Estatus</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="concepto in conceptos" v-if="conceptos.length > 0">
                  <th scope="row">@{{ concepto.descripcion }}</th>
                  <td>@{{ concepto.estatus }}</td>
                  <td>
                    <a v-on:click="modificarConcepto(concepto.id,concepto.descripcion,concepto.id_estatus)" target="_self">
                       <i class="far fa-edit"></i>
                    </a>
                  </td>
                </tr>
                <tr v-if="conceptos.length < 1">
                  <td colspan="6">
                    <div class="alert alert-warning text-center" role="alert">
                      La busqueda no arrojó resultado!
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="conceptos.length > 0">
                <tr>
                  <td colspan="3">
                    <div>
                      <div><b>Página</b></div>
                      <div class="wrapper-input">
                        <vue-numeric :max="paginador.max"
                                     :min="1"
                                     :precision="0"
                                     class="form-control text-center"
                                     type="text"
                                     v-model="paginador.pagina"
                                     v-on:blur="numeroPagina"></vue-numeric>
                      </div>
                      <div><b>de @{{ paginador.numPaginas }}</b></div>
                      <div>
                        <i class="fas fa-chevron-left icono" v-on:click="paginaAnterior"></i>
                      </div>
                      <div>
                        <i class="fas fa-chevron-right icono" v-on:click="paginaSiguiente"></i>
                      </div>
                    </div>
                  </td>
                </tr>
              </tfoot>
            </table>

          </div>

          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>

        </div>

        <div id="modal-crear-concepto" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Nuevo Concepto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="formNuevoConcepto">
                  <div class="form-group">
                    <input class="form-control conceptoNuevo"
                           data-min="3"
                           data-validar="true"
                           id="conceptoNuevo"
                           ref="conceptoNuevo"
                           type="text"
                           v-bind:disabled="formNuevoConcepto.concepto.disabled"
                           v-model="formNuevoConcepto.concepto.value"
                           v-on:keyup="soloLetras">
                    <small id="conceptoNuevoHelp" class="form-text text-muted">Ejemplo: Vacaciones</small>
                    <div class="mensaje"></div>
                  </div>
                </form>
                <div v-bind:class="alertConceptoNuevo.class" role="alert" v-if="alertConceptoNuevo.show" v-html="alertConceptoNuevo.message"></div>
              </div>
              <div class="modal-footer">
                <button class="btn"
                        type="button"
                        v-bind:disabled="submitModalConceptoNuevo.disabled"
                        v-if="submitModalConceptoNuevo.show"
                        v-html="submitModalConceptoNuevo.content"
                        v-on:click="crearConcepto"></button>
              </div>
            </div>
          </div>
        </div>

        <div id="modal-modificar-concepto" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Modificar Concepto</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="formModificarConcepto">
                  <div class="form-group">
                    <input class="form-control"
                           data-min="3"
                           data-validar="true"
                           id="modificarConcepto"
                           ref="modificarConcepto"
                           type="text"
                           v-bind:disabled="formModificarConcepto.concepto.disabled"
                           v-model="formModificarConcepto.concepto.value"
                           v-on:keyup="soloLetras">
                    <small id="modificarConceptoHelp" class="form-text text-muted">Ejemplo: Vacaciones</small>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group">
                    <label for="estatus">Estatus</label>
                    <select aria-describedby="estatusHelp"
                            class="form-control form-control-sm"
                            data-validar="true"
                            v-bind:disabled="formModificarConcepto.estatus.disabled"
                            v-model="formModificarConcepto.estatus.value"
                            v-on:click="limpiarMensajeError">
                      <option value="" selected>Seleccione...</option>
                      <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
                    </select>
                  </div>
                </form>
                <div v-bind:class="alertModificarConcepto.class" role="alert" v-if="alertModificarConcepto.show" v-html="alertModificarConcepto.message"></div>
              </div>
              <div class="modal-footer">
                <button class="btn"
                        type="button"
                        v-bind:disabled="submitModalModificarConcepto.disabled"
                        v-if="submitModalModificarConcepto.show"
                        v-html="submitModalModificarConcepto.content"
                        v-on:click="guardarModificarConcepto"></button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/formConceptosHorasNoCargables.js') }}"></script>

    </body>
</html>
