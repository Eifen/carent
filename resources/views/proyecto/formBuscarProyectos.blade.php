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
        <link href="{{ mix('/css/formBuscarProyectos.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-9 wrapper-form" v-if="formFiltro.mostrar">
            <h5>Filtros de búsqueda</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-4">
                <label for="descripcion">Proyecto</label>
                <input aria-describedby="descripcionHelp"
                       class="form-control form-control-sm"
                       id="descripcion"
                       maxlength="250"
                       v-bind:disabled="formFiltro.descripcion.disabled"
                       v-model.trim="formFiltro.descripcion.value"
                       type="text">
                <small id="descripcionHelp" class="form-text text-muted">Nombre que se le dío la proyecto</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="cliente">Cliente</label>
                <input aria-describedby="clienteHelp"
                       class="form-control form-control-sm"
                       id="cliente"
                       maxlength="250"
                       v-bind:disabled="formFiltro.cliente.disabled"
                       v-model.trim="formFiltro.cliente.value"
                       type="text">
                <small id="clienteHelp" class="form-text text-muted">Razón Social del cliente</small>
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
              <div class="form-group col-12 col-sm-6">
                <label for="divisiones">Divisiones</label>
                <multiselect @Open="limpiarMensajeErrorMultiselect"
                             :clear-on-select="false"
                             :close-on-select="false"
                             :disabled="formFiltro.divisiones.disabled"
                             :multiple="true"
                             :options="comboDivisiones"
                             :show-labels="false"
                             clase="form-control form-control-sm"
                             data-validar="true"
                             id="divisiones"
                             label="descripcion"
                             placeholder="Seleccione..."
                             track-by="descripcion"
                             v-model="formFiltro.divisiones.value"></multiselect>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-3">
                <label>&nbsp;</label>
                <button class="btn filtrar"
                        type="button"
                        v-on:click="buscar"
                        v-bind:disabled="formFiltro.btn.filtrar.disabled"
                        v-html="formFiltro.btn.filtrar.html"></button>
              </div>
              <div class="form-group col-12 col-sm-3">
                <label>&nbsp;</label>
                <button class="btn limpiar_filtro"
                        type="button"limpiarFiltro
                        v-on:click="limpiarFiltro"
                        v-bind:disabled="formFiltro.btn.limpiarFiltro.disabled"
                        v-html="formFiltro.btn.limpiarFiltro.html"></button>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="formFiltro.mostrar" v-cloak>

            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Proyecto</th>
                  <th scope="col">Horas Contratadas</th>
                  <th scope="col">Fecha Contratación</th>
                  <th scope="col">Clientes</th>
                  <th scope="col">Estatus</th>
                  <th scope="col" v-if="permisoActualizar"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="proyecto in proyectos" v-if="proyectos.length > 0">
                  <th scope="row">@{{ proyecto.descripcion }}</th>
                  <td>@{{ proyecto.horas_contratadas }}</td>
                  <td>@{{ proyecto.fecha_contratacion }}</td>
                  <td>@{{ proyecto.cliente }}</td>
                  <td>@{{ proyecto.estatus }}</td>
                  <td v-if="permisoActualizar">
                    <a v-bind:href="'/formModificarProyecto/'+proyecto.id" target="_self">
                       <i class="far fa-edit"></i>
                    </a>
                  </td>
                </tr>
                <tr v-if="proyectos.length < 1">
                  <td colspan="6">
                    <div class="alert alert-warning text-center" role="alert">
                      La busqueda no arrojó resultado!
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="proyectos.length > 0">
                <tr>
                  <td colspan="6">
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

          <div class="col-12 col-sm-11 col-md-9 col-lg-8" v-cloak>
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>

        </div>

      </div>

      <script src="{{ mix('/js/formBuscarProyectos.js') }}"></script>

    </body>
</html>
