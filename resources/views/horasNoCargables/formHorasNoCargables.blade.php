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
        <link href="{{ mix('/css/formHorasNoCargables.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-12 col-md-11 col-lg-10 wrapper-form" v-if="formFiltro.mostrar">
            <h5>Filtros de búsqueda</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-4">
                <label for="divisiones">Divisiones</label>
                <multiselect :clear-on-select="false"
                             :disabled="formFiltro.divisiones.disabled"
                             :multiple="true"
                             :options="comboDivisiones"
                             :preserve-search="true"
                             :show-labels="false"
                             id="divisiones"
                             label="descripcion"
                             placeholder="Seleccione..."
                             track-by="descripcion"
                             v-model="formFiltro.divisiones.value">
                   <template slot="selection"
                             slot-scope="{ values, search, isOpen }">
                             <span class="multiselect__single"
                                   v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} seleccionado(s)</span>
                   </template>
                </multiselect>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="empleados">Empleado</label>
                <multiselect :clear-on-select="false"
                             :disabled="formFiltro.empleados.disabled"
                             :multiple="true"
                             :options="comboEmpleados"
                             :preserve-search="true"
                             :show-labels="false"
                             id="empleados"
                             label="nombre"
                             placeholder="Seleccione..."
                             track-by="nombre"
                             v-model="formFiltro.empleados.value">
                  <template slot="selection"
                            slot-scope="{ values, search, isOpen }">
                            <span class="multiselect__single"
                                  v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} seleccionado(s)</span>
                  </template>
                </multiselect>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="conceptos">Conceptos</label>
                <multiselect :clear-on-select="false"
                             :disabled="formFiltro.conceptos.disabled"
                             :multiple="true"
                             :options="comboConceptos"
                             :preserve-search="true"
                             :show-labels="false"
                             id="conceptos"
                             label="descripcion"
                             placeholder="Seleccione..."
                             track-by="descripcion"
                             v-model="formFiltro.conceptos.value">
                   <template slot="selection"
                             slot-scope="{ values, search, isOpen }">
                             <span class="multiselect__single"
                                   v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} seleccionado(s)</span>
                   </template>
                </multiselect>
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
                <label for="fechaDesde">Fecha Desde</label>
                <datetime
                  @input="fechaDesdeFiltro($event)"
                  :disabled="formFiltro.fechaDesde.disabled"
                  :max-datetime="formFiltro.fechaDesde.maxValue"
                  :minute-step="30"
                  :use12-hour="true"
                  format="dd/LL/yyyy hh:mm a"
                  input-class="form-control form-control-sm fechaDesde"
                  v-model="formFiltro.fechaDesde.value"
                  value-zone='local'
                  type="datetime"
                  zone='local'>
                  <template slot="button-cancel">
                    <span v-on:click="limpiarFecha('formFiltro','fechaDesde')">Limpiar</span>
                  </template>
                </datetime>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label for="fechaHasta">Fecha Hasta</label>
                <datetime
                  @input="limpiarMensajeError"
                  :disabled="formFiltro.fechaHasta.disabled"
                  :max-datetime="formFiltro.fechaHasta.maxValue"
                  :min-datetime="formFiltro.fechaHasta.minValue"
                  :minute-step="30"
                  :use12-hour="true"
                  format="dd/LL/yyyy hh:mm a"
                  input-class="form-control form-control-sm fechaHasta"
                  v-model="formFiltro.fechaHasta.value"
                  value-zone='local'
                  type="datetime"
                  zone='local'>
                  <template slot="button-cancel">
                    <span v-on:click="limpiarFecha('formFiltro','fechaHasta')">Limpiar</span>
                  </template>
                </datetime>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn btn-sm btn-primary"
                        type="button"
                        v-on:click="buscar"
                        v-bind:disabled="formFiltro.btn.filtrar.disabled"
                        v-html="formFiltro.btn.filtrar.html"></button>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn btn-sm btn-outline-primary"
                        type="button"limpiarFiltro
                        v-on:click="limpiarFiltro"
                        v-bind:disabled="formFiltro.btn.limpiarFiltro.disabled"
                        v-html="formFiltro.btn.limpiarFiltro.html"></button>
              </div>
              <div class="form-group col-12 col-sm-4">
                <label>&nbsp;</label>
                <button class="btn btn-outline-success btn-sm"
                        type="button"
                        v-on:click="cargar"
                        v-bind:disabled="formFiltro.btn.cargar.disabled"
                        v-html="formFiltro.btn.cargar.html"></button>
              </div>
            </form>

          </div>
          <div class="col-12 wrapper-form" v-if="formFiltro.mostrar">

            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Nombre</th>
                  <th scope="col">Concepto</th>
                  <th scope="col">División</th>
                  <th scope="col">Fecha Desde / Hasta</th>
                  <th scope="col">Estatus</th>
                  <th scope="col"></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="registro in registros" v-if="registros.length > 0">
                  <th scope="row">@{{ registro.nombre }}</th>
                  <td>@{{ registro.concepto }}</td>
                  <td>@{{ registro.division }}</td>
                  <td>@{{ registro.fecha_desde+' - '+registro.fecha_hasta }}</td>
                  <td>@{{ registro.estatus }}</td>
                  <td>
                    <a v-on:click="modificarConcepto(registro)" target="_self">
                       <i class="fas fa-cog"></i>
                    </a>
                  </td>
                </tr>
                <tr v-if="registros.length < 1">
                  <td colspan="6">
                    <div class="alert alert-warning text-center" role="alert">
                      La busqueda no arrojó resultado!
                    </div>
                  </td>
                </tr>
              </tbody>
              <tfoot v-if="registros.length > 0">
                <tr>
                  <td colspan="7">
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

        <div id="modal-cargar" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Cargar Horas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="formCargarHoras" class="row">
                  <div class="form-group col-12" id="concepto">
                    <label for="concepto">Concepto</label>
                    <multiselect @input="limpiarMensajeErrorMultiselect"
                                 :clear-on-select="false"
                                 :disabled="formCargarHoras.concepto.disabled"
                                 :options="comboConceptos"
                                 :preserve-search="true"
                                 :show-labels="false"
                                 label="descripcion"
                                 placeholder="Seleccione..."
                                 track-by="descripcion"
                                 v-model="formCargarHoras.concepto.value">
                    </multiselect>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-6" id="fechaDesde">
                    <label>Fecha Desde</label>
                    <datetime
                      @input="fechaDesde('formCargarHoras', $event)"
                      :disabled="formCargarHoras.fechaDesde.disabled"
                      :max-datetime="formCargarHoras.fechaDesde.maxValue"
                      :minute-step="30"
                      :use12-hour="true"
                      format="dd/LL/yyyy hh:mm a"
                      input-class="form-control fechaDesde form-control-sm"
                      v-model="formCargarHoras.fechaDesde.value"
                      value-zone='local'
                      type="datetime"
                      zone='local'>
                      <template slot="button-cancel">
                        Cerrar
                      </template>
                    </datetime>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-6" id="fechaHasta">
                    <label>Fecha Hasta</label>
                    <datetime
                      @input="limpiarMensajeError"
                      :disabled="formCargarHoras.fechaHasta.disabled"
                      :max-datetime="formCargarHoras.fechaHasta.maxValue"
                      :min-datetime="formCargarHoras.fechaHasta.minValue"
                      :minute-step="30"
                      :use12-hour="true"
                      format="dd/LL/yyyy hh:mm a"
                      input-class="form-control fechaHasta form-control-sm"
                      v-model="formCargarHoras.fechaHasta.value"
                      value-zone='local'
                      type="datetime"
                      zone='local'>
                      <template slot="button-cancel">
                        Cerrar
                      </template>
                    </datetime>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-12">
                    <div class="form-group">
                      <label>Observacion</label>
                      <textarea :disabled="formCargarHoras.observacion.disabled"
                                :maxlength="formCargarHoras.observacion.maxlength"
                                class="form-control form-control-sm"
                                rows="3"
                                v-model="formCargarHoras.observacion.value"></textarea>
                    </div>
                    <small class="form-text text-muted">@{{ formCargarHoras.observacion.value.length }} de @{{ formCargarHoras.observacion.maxlength }} caracteres</small>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-12" v-if="supervisor">
                    <label for="estatus">Estatus</label>
                    <select aria-describedby="estatusHelp"
                            class="form-control form-control-sm"
                            id="estatus"
                            data-validar="true"
                            v-bind:disabled="formCargarHoras.estatus.disabled"
                            v-model="formCargarHoras.estatus.value"
                            v-on:click="limpiarMensajeError">
                      <option value="" selected disabled>Seleccione...</option>
                      <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
                    </select>
                    <div class="mensaje"></div>
                  </div>
                </form>
                <div v-bind:class="alertCargarHora.class" role="alert" v-if="alertCargarHora.show" v-html="alertCargarHora.message"></div>
              </div>
              <div class="modal-footer">
                <button class="btn btn-success btn-sm btn-block"
                        type="button"
                        v-bind:disabled="submitModalCargarHora.disabled"
                        v-if="submitModalCargarHora.show"
                        v-html="submitModalCargarHora.content"
                        v-on:click="cargarHoras"></button>
              </div>
            </div>
          </div>
        </div>

        <div id="modal-modificar" class="modal fade" tabindex="-1" role="dialog" v-cloak>
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h4>Modificar Horas</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form id="formModificarHoras" class="row">
                  <div class="form-group col-12" id="concepto">
                    <label for="concepto">Concepto</label>
                    <multiselect @input="limpiarMensajeErrorMultiselect"
                                 :clear-on-select="false"
                                 :disabled="formModificarHoras.concepto.disabled"
                                 :options="comboConceptos"
                                 :preserve-search="true"
                                 :show-labels="false"
                                 label="descripcion"
                                 placeholder="Seleccione..."
                                 track-by="descripcion"
                                 v-model="formModificarHoras.concepto.value">
                    </multiselect>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-6" id="fechaDesde">
                    <label>Fecha Desde</label>
                    <datetime
                      @input="fechaDesde('formModificarHoras', $event)"
                      :disabled="formModificarHoras.fechaDesde.disabled"
                      :max-datetime="formModificarHoras.fechaDesde.maxValue"
                      :minute-step="30"
                      :use12-hour="true"
                      format="dd/LL/yyyy hh:mm a"
                      input-class="form-control fechaDesde form-control-sm"
                      v-model="formModificarHoras.fechaDesde.value"
                      value-zone='local'
                      type="datetime"
                      zone='local'>
                      <template slot="button-cancel">
                        Cerrar
                      </template>
                    </datetime>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-6" id="fechaHasta">
                    <label>Fecha Hasta</label>
                    <datetime
                      @input="limpiarMensajeError"
                      :disabled="formModificarHoras.fechaHasta.disabled"
                      :max-datetime="formModificarHoras.fechaHasta.maxValue"
                      :min-datetime="formModificarHoras.fechaHasta.minValue"
                      :minute-step="30"
                      :use12-hour="true"
                      format="dd/LL/yyyy hh:mm a"
                      input-class="form-control fechaHasta form-control-sm"
                      v-model="formModificarHoras.fechaHasta.value"
                      value-zone='local'
                      type="datetime"
                      zone='local'>
                      <template slot="button-cancel">
                        Cerrar
                      </template>
                    </datetime>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-12">
                    <div class="form-group">
                      <label>Observacion</label>
                      <textarea :disabled="formModificarHoras.observacion.disabled"
                                :maxlength="formModificarHoras.observacion.maxlength"
                                class="form-control form-control-sm"
                                rows="3"
                                v-model="formModificarHoras.observacion.value"></textarea>
                    </div>
                    <small class="form-text text-muted">@{{ formModificarHoras.observacion.value.length }} de @{{ formModificarHoras.observacion.maxlength }} caracteres</small>
                    <div class="mensaje"></div>
                  </div>
                  <div class="form-group col-12">
                    <div class="form-group">
                      <label>Aprobado por</label>
                      <input class="form-control form-control-sm" v-model="formModificarHoras.aprobadoPor" disabled>
                    </div>
                  </div>
                  <div class="form-group col-12">
                    <div class="form-group">
                      <label>Fecha de Aprobación</label>
                      <input class="form-control form-control-sm" v-model="formModificarHoras.fechaAprobacion" disabled>
                    </div>
                  </div>
                  <div class="form-group col-12">
                    <label for="estatus">Estatus</label>
                    <select aria-describedby="estatusHelp"
                            class="form-control form-control-sm"
                            id="estatus"
                            data-validar="true"
                            v-bind:disabled="formModificarHoras.estatus.disabled"
                            v-model="formModificarHoras.estatus.value"
                            v-on:click="limpiarMensajeError">
                      <option value="" selected disabled>Seleccione...</option>
                      <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
                    </select>
                    <div class="mensaje"></div>
                  </div>
                </form>
              </div>
              <div class="modal-footer">
                <button class="btn btn-warning btn-sm btn-block"
                        type="button"
                        v-bind:disabled="submitModalModificarHora.disabled"
                        v-if="submitModalModificarHora.show"
                        v-html="submitModalModificarHora.content"
                        v-on:click="guardarModificar"></button>
                <button class="btn btn-danger btn-sm btn-block"
                        type="button"
                        v-bind:disabled="confirmarModificarHora.disabled"
                        v-if="confirmarModificarHora.show"
                        v-html="confirmarModificarHora.content"
                        v-on:click="confirmarEliminar"></button>
                <div v-bind:class="alertModificarHora.class"
                     role="alert" v-if="alertModificarHora.show"
                     v-html="alertModificarHora.message"></div>
                <button class="btn btn-danger btn-sm btn-block"
                        type="button"
                        v-bind:disabled="cancelarEliminarModificarHora.disabled"
                        v-if="cancelarEliminarModificarHora.show"
                        v-html="cancelarEliminarModificarHora.content"
                        v-on:click="cancelarEliminarHora"></button>
                <button class="btn btn-success btn-sm btn-block"
                        type="button"
                        v-bind:disabled="eliminarModificarHora.disabled"
                        v-if="eliminarModificarHora.show"
                        v-html="eliminarModificarHora.content"
                        v-on:click="eliminarHora"></button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/formHorasNoCargables.js') }}"></script>

    </body>
</html>
