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

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

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
            </b-card-text>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-facturado">
              <b-card-text>
                <span class="titulo">MONTO FACTURADO</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_facturado }}</span>
              </b-card-text>
            </b-card-text>
          </b-col>
          <b-col cols="12" md="6" lg="4">
            <b-card class="text-left card-monto-gasto">
              <b-card-text>
                <span class="titulo">MONTO GASTOS</span>
              </b-card-text>
              <b-card-text>
                <span class="monto">@{{ form.info.monto_gastos }}</span>
              </b-card-text>
            </b-card-text>
          </b-col>
        </b-row>

        <b-row class="wrapper-forms" v-cloak v-if="form.mostrar">
          <b-col cols="12">
            <b-form class="row justify-content-center">
              <b-form-group
                :invalid-feedback="form.camposAtributos.concepto.invalidFeedback"
                class="col-12 col-sm-6 col-md-3"
                description="Aquí indicas si es un abono o un gasto"
                label="Concepto"
                label-for="concepto"
                id="group-concepto">
                <b-form-select
                  @change="limpiarMensajeError('concepto')"
                  :disabled="form.camposAtributos.concepto.disabled"
                  :options="comboConceptos"
                  :state="form.camposAtributos.concepto.state"
                  :value="null"
                  id="concepto"
                  ref="concepto"
                  size="sm"
                  v-model="$v.form.campos.concepto.$model">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione una opción</option>
                  </template>
                </b-form-select>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.numeroFactura.invalidFeedback"
                class="col-12 col-sm-6 col-md-3"
                description="Ejemplo: AABB0123C-5"
                label="N° de Factura"
                label-for="numeroFactura"
                id="group-numeroFactura">
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
                :invalid-feedback="form.camposAtributos.fechaFactura.invalidFeedback"
                class="col-12 col-sm-6 col-md-3"
                label="Fecha de la Factura:"
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
                :invalid-feedback="form.camposAtributos.numeroControl.invalidFeedback"
                class="col-12 col-sm-6 col-md-3"
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
                  v-model="$v.form.campos.observaciones.$model"></b-form-textarea>
              </b-form-group>
              <b-form-group
                class="col-12 col-md-4"
                label-for="registrar">
                <b-button
                  @click="registrar"
                  :disabled="form.botones.submit.disabled"
                  block
                  class="btn"
                  id="registrar"
                  size="sm"
                  v-html="form.botones.submit.html"
                  v-if="form.botones.submit.show"
                  variant="outline-success"></b-button>
              </b-form-group>
              <b-form-group
                class="col-12">
                <alert :contador="form.alert.contador"
                       :icono-cerrar="form.alert.iconCerrar"
                       :mensaje="form.alert.mensaje"
                       :mostrar="form.alert.mostrar"
                       :ocultar-seg="form.alert.ocultarSeg"
                       :variante="form.alert.variante">
                </alert>
              </b-form-group>
            </b-form>
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

      </b-container>

      <script src="{{ mix('/js/agregarIngresosGastos.js') }}"></script>

    </body>
</html>
