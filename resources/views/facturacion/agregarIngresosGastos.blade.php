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
        <link href="{{ mix('/css/agregarIngresosGastos.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <script>
        // Se obtiene el valor proveniente del controlador
        // para luego asignarselo a un variable dentro de vue
        const proyecto_id = "{{ $id_proyecto }}";
      </script>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <b-row align-h="center" v-cloak>
          <b-col cols="12" md="4" lg="2">
            <b-button
              block
              href="{{ url()->previous() }}"
              size="sm"
              variant="primary">Regresar</b-button>
          </b-col>
        </b-row>

        <b-row v-cloak>
          <b-col cols="12" sm="11" md="9" lg="8" v-if="form.mostrar">
            <h4 class="titulo-principal">Estas agregando facturas/gastos al Proyecto</h4>
          </b-col>
        </b-row>

        <b-row v-cloak v-if="form.mostrar">
          <b-col cols="12">
            <b-card class="text-left card-proyecto">
              <b-card-text>
                <b-icon-folder></b-icon-folder>
                <span class="titulo">PROYECTO:</span>
                <span class="text-lowercase">@{{ form.info.proyecto }}</span>
              </b-card-text>
              <b-card-text>
                <b-icon-calendar2></b-icon-calendar2>
                <span class="titulo">FECHA DE CONTRATACIÓN/APERTURA:</span>
                <span>@{{ form.info.fecha_contratacion }}</span>
              </b-card-text>
              <b-card-text>
                <b-icon-person></b-icon-person>
                <span class="titulo">SOCIO:</span>
                <span class="text-capitalize">@{{ form.info.socio }}</span>
              </b-card-text>
              <b-card-text>
                <b-icon-person></b-icon-person>
                <span class="titulo">GERENTE:</span>
                <span class="text-capitalize">@{{ form.info.gerente }}</span>
              </b-card-text>
            </b-card>
          </b-col>
        </b-row>

        <b-row v-cloak v-if="form.mostrar">
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-contratado">
              <b-card-text>
                <span class="titulo">MONTO CONTRATADO</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_contratado }}</span>
              </b-card-text>
            </b-card>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-facturado">
              <b-card-text>
                <span class="titulo">MONTO FACTURADO</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_facturado }}</span>
              </b-card-text>
            </b-card>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-notas-credito">
              <b-card-text>
                <span class="titulo">MONTO NOTAS DE CRÉDITO</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_notas_credito }}</span>
              </b-card-text>
            </b-card>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-gasto">
              <b-card-text>
                <span class="titulo">MONTO GASTOS NO FACTURABLES</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_gastos }}</span>
              </b-card-text>
            </b-card>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-otros-gastos">
              <b-card-text>
                <span class="titulo">OTROS GASTOS (COMISIONES)</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_otros_gastos }}</span>
              </b-card-text>
            </b-card>
          </b-col>
          <b-col cols="12" md="6" lg="4" class="wrapper-btn-agregar-factura">
            <b-button
              :disabled="botones.agregarFactura.disabled"
              block
              class="btn"
              size="sm"
              v-b-modal="'agregar-factura'"
              v-html="botones.agregarFactura.html"
              v-if="botones.agregarFactura.show"
              variant="success"></b-button>
          </b-col>
        </b-row>

        <b-row v-cloak>
          <b-col cols="12" sm="11" md="9" lg="8" v-if="form.mostrar">
            <h5 class="titulo-secundario">Facturas Asociadas al Proyecto</h5>
          </b-col>
        </b-row>

        <b-row class="wrapper-table" v-cloak v-if="form.mostrar">
          <b-col cols="12">
            <b-table hover :fields="tabla.encabezado" :items="tabla.registros" responsive show-empty :busy="tabla.cargando" :small="true">
              <template v-slot:table-busy>
                <div class="text-center text-primary">
                  <b-spinner class="align-middle"></b-spinner>
                </div>
              </template>
              <template v-slot:empty="scope" v-if="tabla.alert.mostrar">
                <alert :contador="tabla.alert.contador"
                       :icono-cerrar="tabla.alert.iconCerrar"
                       :mensaje="tabla.alert.mensaje"
                       :mostrar="tabla.alert.mostrar"
                       :ocultar-seg="tabla.alert.ocultarSeg"
                       :variante="tabla.alert.variante">
                </alert>
              </template>
              <template v-slot:cell(numero)="data">
                <b>@{{ data.item.numero }}</b>
              </template>
              <template v-slot:cell(movimiento)="data">
                <b-badge :variant="data.item.varianteMovimiento" class="text-capitalize">@{{ data.item.movimiento }}</b-badge>
              </template>
              <template v-slot:cell(opciones)="data">
                <b-icon-search :id="'info-'+data.item.id" v-b-modal="'modal-info-'+data.item.id" class="icono"></b-icon-search>
                <b-tooltip :target="'info-'+data.item.id" triggers="hover">
                  Ver más info
                </b-tooltip>
                <b-modal
                  :id="'modal-info-'+data.item.id"
                  :ok-only="true"
                  button-size="sm"
                  centered
                  ok-title="Cerrar"
                  ok-variant="primary"
                  size="lg">
                  <template v-slot:modal-title>
                    Más información @{{ data.item.numero_factura }}
                  </template>
                  <b-form-group
                    label="Concepto">
                    <b-form-textarea
                      readonly
                      rows="3"
                      size="sm"
                      v-model="data.item.concepto"></b-form-textarea>
                  </b-form-group>
                  <b-form-group
                    label="N° Control">
                    <b-form-input
                      :value="data.item.numero_control"
                      readonly
                      size="sm"></b-form-input>
                  </b-form-group>
                  <b-form-group
                    label="Observaciones">
                    <b-form-textarea
                      readonly
                      rows="3"
                      size="sm"
                      v-model="data.item.observaciones"></b-form-textarea>
                  </b-form-group>
                  <b-form-group
                    label="Fecha de Cobro">
                    <b-form-datepicker
                      :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
                      locale="es-ES"
                      readonly
                      size="sm"
                      v-model="data.item.fecha_cobro_factura"></b-form-datepicker>
                  </b-form-group>
                </b-modal>
                <b-icon-trash
                  :id="'eliminar-'+data.item.id"
                  class="icono"
                  v-b-modal="'modal-eliminar-'+data.item.id"
                  v-if="permisos.permiso_eliminar"></b-icon-trash>
                <b-tooltip :target="'eliminar-'+data.item.id" triggers="hover">
                  Anular Factura
                </b-tooltip>
                <b-modal
                  :hide-header-close="true"
                  :id="'modal-eliminar-'+data.item.id"
                  :no-close-on-backdrop="true"
                  :ref="'modal-eliminar-factura-'data.item.id"
                  body-bg-variant="warning"
                  centered>
                  <div class="text-center"><b>¿Estas seguro de eliminar esta Factura / Gasto?</b></div>
                  <template v-slot:modal-footer="{ ok, cancel, hide  }">
                    <alert :contador="modalEliminar.alert.contador"
                           :icono-cerrar="modalEliminar.alert.iconCerrar"
                           :mensaje="modalEliminar.alert.mensaje"
                           :mostrar="modalEliminar.alert.mostrar"
                           :ocultar-seg="modalEliminar.alert.ocultarSeg"
                           :variante="modalEliminar.alert.variante">
                    </alert>
                    <b-button
                      @click="cancel()"
                      :disabled="modalEliminar.botones.cancelar.disabled"
                      size="sm"
                      v-html="modalEliminar.botones.cancelar.html"
                      v-if="modalEliminar.botones.cancelar.show"
                      variant="danger">
                    </b-button>
                    <b-button
                      @click="eliminar_factura(data.item.id)"
                      :disabled="modalEliminar.botones.submit.disabled"
                      size="sm"
                      v-html="modalEliminar.botones.submit.html"
                      v-if="modalEliminar.botones.submit.show"
                      variant="success">
                    </b-button>
                    <b-button
                      @click="hide()"
                      size="sm"
                      v-if="modalEliminar.botones.hide.show"
                      variant="primary">
                      Cerrar
                    </b-button>
                  </template>
                </b-modal>
              </template>
              <template v-slot:custom-foot v-if="tabla.registros.length > 0">
                <b-tr>
                  <b-td colspan="9">
                    <div>
                      <div><b>Página</b></div>
                      <div class="wrapper-input" v-on:keyup="numeroPagina">
                        <vue-numeric :max="paginador.max"
                                     :min="1"
                                     :precision="0"
                                     class="form-control text-center form-control-sm"
                                     type="text"
                                     v-model="paginador.pagina"></vue-numeric>
                      </div>
                      <div><b>de @{{ paginador.numPaginas }}</b></div>
                      <div>
                        <b-icon-chevron-compact-left class="icono border rounded" v-on:click="paginaAnterior"></b-icon-chevron-compact-left>
                      </div>
                      <div>
                        <b-icon-chevron-compact-right class="icono border rounded" v-on:click="paginaSiguiente"></b-icon-chevron-compact-right>
                      </div>
                    </div>
                  </b-td>
                </b-tr>
              </template>
            </b-table>
          </b-col>
        </b-row>

        <b-row align-h="center" align-v="center" class="wrapper-alert-general" v-cloak v-if="alertGeneral.mostrar">
          <b-col sm="11" md="9" lg="8">
            <b-row class="row wrapper-alert">
              <b-col cols="12">
                <alert :contador="alertGeneral.contador"
                       :icono-cerrar="alertGeneral.iconCerrar"
                       :mensaje="alertGeneral.mensaje"
                       :mostrar="alertGeneral.mostrar"
                       :ocultar-seg="alertGeneral.ocultarSeg"
                       :variante="alertGeneral.variante">
                </alert>
              </b-col>
            </b-row>
          </b-col>

        </b-row>

        <b-modal
          id="agregar-factura"
          ref="agregar-factura"
          size="xl"
          v-cloak
          v-if="form.mostrar">
          <template v-slot:modal-title>
            Agregar factura o gasto
          </template>
          <b-form class="row justify-content-center">
            <b-form-group
              :invalid-feedback="form.camposAtributos.tipoConcepto.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              description="Aquí indicas si es un abono o un gasto"
              label="Tipo de Concepto"
              label-for="tipoConcepto"
              id="group-tipoConcepto">
              <b-form-select
                @change="tipoConcepto"
                :disabled="form.camposAtributos.tipoConcepto.disabled"
                :options="comboTipoConceptos"
                :state="form.camposAtributos.tipoConcepto.state"
                :value="null"
                id="tipoConcepto"
                ref="tipoConcepto"
                size="sm"
                v-model="$v.form.campos.tipoConcepto.$model">
                <template v-slot:first>
                  <option :value="null" disabled="true">Seleccione una opción</option>
                </template>
              </b-form-select>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.numeroFactura.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              label="N° de Factura"
              label-for="numeroFactura"
              id="group-numeroFactura"
              v-if="form.camposAtributos.numeroFactura.busqueda === true">
              <b-form-input
                @blur="valorBlur('numeroFactura')"
                @input="buscarFactura"
                :disabled="form.camposAtributos.numeroFactura.disabled"
                :state="form.camposAtributos.numeroFactura.state"
                autocomplete="off"
                class="text-uppercase"
                id="numeroFactura"
                ref="numeroFactura"
                size="sm"
                type="text"
                v-on:focus="valorFocus('numeroFactura')"
                v-model.trim="form.camposAtributos.numeroFactura.valor"></b-form-input>
              <b-dropdown id="lista-facturas" variant="link" no-caret block ref="ref-lista-facturas">
                <b-dropdown-item-button
                  :key="key"
                  v-for="(factura, key) in form.camposAtributos.numeroFactura.listaDropdown.listado"
                  v-if="form.camposAtributos.numeroFactura.listaDropdown.listado.length > 0"
                  v-on:click="elegirFactura(factura)"> @{{ factura.numero_factura }} </b-dropdown-item-button>
                <b-dropdown-item-button
                  v-if="form.camposAtributos.numeroFactura.listaDropdown.noResultado"
                  v-on:click="listadoNoValido('numeroFactura')">No se encontrarón facturas, intente con otro número!</b-dropdown-item-button>
              </b-dropdown>
              <b-form-text id="factura-help" v-html="form.camposAtributos.numeroFactura.help"></b-form-text>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.numeroFactura.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              description="Ejemplo: AABB0123C-5"
              label="N° de Factura"
              label-for="numeroFactura"
              id="group-numeroFactura"
              v-else="form.camposAtributos.numeroFactura.busqueda === false">
              <b-form-input
                @input="limpiarMensajeError('numeroFactura')"
                :disabled="form.camposAtributos.numeroFactura.disabled"
                :state="form.camposAtributos.numeroFactura.state"
                autocomplete="off"
                class="text-uppercase"
                id="numeroFactura"
                ref="numeroFactura"
                size="sm"
                type="text"
                v-model="$v.form.campos.numeroFactura.$model"></b-form-input>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.montoFactura.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              label="Monto Factura"
              label-for="montoFactura"
              id="group-montoFactura">
              <b-input-group :prepend="form.camposAtributos.montoFactura.simboloMoneda" size="sm">
                <b-form-input
                  @input="limpiarMensajeError('montoFactura')"
                  :disabled="form.camposAtributos.montoFactura.disabled"
                  :state="form.camposAtributos.montoFactura.state"
                  autocomplete="off"
                  id="montoFactura"
                  ref="montoFactura"
                  type="text"
                  v-model.trim="$v.form.campos.montoFactura.$model"></b-form-input>
              </b-input-group>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.fechaFactura.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              description="Fecha en que se emite la factura"
              label="Fecha de Emisión:"
              label-for="fechaFactura"
              id="group-fechaFactura">
              <b-form-datepicker
                @input="limpiarMensajeError('fechaFactura')"
                :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
                :disabled="form.camposAtributos.fechaFactura.disabled"
                :max="form.camposAtributos.fechaFactura.max"
                :state="form.camposAtributos.fechaFactura.state"
                id="fechaFactura"
                label-help="Use las teclas del cursor para navegar por las fechas del calendario"
                label-no-date-selected="Ninguna fecha seleccionada"
                locale="es-ES"
                placeholder="Seleccione una fecha"
                ref="fechaFactura"
                size="sm"
                v-model="$v.form.campos.fechaFactura.$model"></b-form-datepicker>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.concepto.invalidFeedback"
              class="col-12"
              description="Descripción por el cual se esta facturando"
              label="Concepto"
              label-for="concepto"
              id="group-concepto">
              <b-form-textarea
                @input="limpiarMensajeError('concepto')"
                :disabled="form.camposAtributos.concepto.disabled"
                :state="form.camposAtributos.concepto.state"
                autocomplete="off"
                id="concepto"
                ref="concepto"
                rows="3"
                size="sm"
                type="text"
                v-model="$v.form.campos.concepto.$model"></b-form-textarea>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.numeroControl.invalidFeedback"
              class="col-12 col-sm-6 col-md-9"
              description="Ejemplo: CONTROL-1"
              label="N° de Control"
              label-for="numeroControl"
              id="group-numeroControl">
              <b-form-input
                @input="limpiarMensajeError('numeroControl')"
                :disabled="form.camposAtributos.numeroControl.disabled"
                :state="form.camposAtributos.numeroControl.state"
                autocomplete="off"
                class="text-uppercase"
                id="numeroControl"
                ref="numeroControl"
                size="sm"
                type="text"
                v-model="$v.form.campos.numeroControl.$model"></b-form-input>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.fechaCobroFactura.invalidFeedback"
              class="col-12 col-sm-6 col-md-3"
              description="Fecha de cobro de la factura"
              label="Fecha de Cobro:"
              label-for="fechaCobroFactura"
              id="group-fechaCobroFactura">
              <b-form-datepicker
                @input="limpiarMensajeError('fechaCobroFactura')"
                :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
                :disabled="form.camposAtributos.fechaCobroFactura.disabled"
                :max="form.camposAtributos.fechaCobroFactura.max"
                :state="form.camposAtributos.fechaCobroFactura.state"
                id="fechaCobroFactura"
                label-help="Use las teclas del cursor para navegar por las fechas del calendario"
                label-no-date-selected="Ninguna fecha seleccionada"
                locale="es-ES"
                placeholder="Seleccione una fecha"
                ref="fechaCobroFactura"
                size="sm"
                v-model="form.camposAtributos.fechaCobroFactura.value"></b-form-datepicker>
            </b-form-group>
            <b-form-group
              :invalid-feedback="form.camposAtributos.observaciones.invalidFeedback"
              class="col-12"
              description="Algún comentario relacionado a la acción de facturar"
              label="Observaciones"
              label-for="observaciones"
              id="group-observaciones">
              <b-form-textarea
                @input="limpiarMensajeError('observaciones')"
                :disabled="form.camposAtributos.observaciones.disabled"
                :state="form.camposAtributos.observaciones.state"
                autocomplete="off"
                id="observaciones"
                ref="observaciones"
                rows="3"
                size="sm"
                type="text"
                v-model="form.camposAtributos.observaciones.value"></b-form-textarea>
            </b-form-group>
          </b-form>
          <template v-slot:modal-footer>
            <alert :contador="form.alert.contador"
                   :icono-cerrar="form.alert.iconCerrar"
                   :mensaje="form.alert.mensaje"
                   :mostrar="form.alert.mostrar"
                   :ocultar-seg="form.alert.ocultarSeg"
                   :variante="form.alert.variante">
            </alert>
            <b-button
              @click="confirmaRegistrarFactura"
              block
              size="sm"
              v-html="form.botones.confirmar.html"
              v-if="form.botones.confirmar.show"
              variant="success"></b-button>
            <b-button
              @click="cancelarRegistrarFactura"
              :disabled="form.botones.cancelar.disabled"
              block
              size="sm"
              v-html="form.botones.cancelar.html"
              v-if="form.botones.cancelar.show"
              variant="danger"></b-button>
            <b-button
              @click="registrar"
              :disabled="form.botones.submit.disabled"
              block
              class="btn"
              id="registrar"
              size="sm"
              v-html="form.botones.submit.html"
              v-if="form.botones.submit.show"
              variant="success"></b-button>
          </template>
        </b-modal>

      </b-container>

      <script src="{{ mix('/js/agregarIngresosGastos.js') }}"></script>

    </body>
</html>
