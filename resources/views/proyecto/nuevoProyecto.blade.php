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
        <link href="{{ mix('/css/nuevoProyecto.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <b-row align-h="center" align-v="center" class="wrapper-forms" v-cloak v-if="form.mostrar">
          <b-col cols="12" sm="11" md="9" lg="8">
            <h4>Estas creando un nuevo Proyecto</h4>
            <b-form class="row">
              <b-form-group
                :invalid-feedback="form.camposAtributos.descripcion.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: Proyecto 1"
                label="Descripción"
                label-for="descripcion"
                id="group-descripcion">
                <b-form-input
                  @input="limpiarMensajeError(form.camposAtributos.descripcion)"
                  :disabled="form.camposAtributos.descripcion.disabled"
                  :state="form.camposAtributos.descripcion.state"
                  autocomplete="off"
                  class="text-uppercase"
                  id="descripcion"
                  ref="descripcion"
                  size="sm"
                  type="text"
                  v-model="$v.form.campos.descripcion.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.cliente.invalidFeedback"
                class="col-12 col-sm-6"
                label="Cliente"
                label-for="cliente"
                id="group-cliente">
                <b-form-input
                  @blur="valorBlur('cliente')"
                  @input="buscarCliente"
                  :disabled="form.camposAtributos.cliente.disabled"
                  :state="form.camposAtributos.cliente.state"
                  autocomplete="off"
                  id="cliente"
                  ref="cliente"
                  size="sm"
                  type="text"
                  v-on:focus="valorFocus('cliente')"
                  v-model.trim="form.camposAtributos.cliente.valor"></b-form-input>
                <b-dropdown id="lista-cliente" variant="link" no-caret block ref="ref-lista-cliente">
                  <b-dropdown-item-button
                    :key="key"
                    v-for="(cliente, key) in form.camposAtributos.cliente.listaDropdown.listado"
                    v-if="form.camposAtributos.cliente.listaDropdown.listado.length > 0"
                    v-on:click="elegirCliente(cliente.id, cliente.razon_social)"> @{{ cliente.razon_social }} </b-dropdown-item-button>
                  <b-dropdown-item-button
                    v-if="form.camposAtributos.cliente.listaDropdown.noResultado"
                    v-on:click="listadoNoValido('cliente')">No se encontrarón clientes, intente con otro nombre!</b-dropdown-item-button>
                </b-dropdown>
                <b-form-text id="cliente-help" v-html="form.camposAtributos.cliente.help"></b-form-text>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.estatus.invalidFeedback"
                class="col-12 col-sm-6"
                label="Estatus:"
                label-for="estatus"
                id="group-estatus">
                <b-form-select
                  @change="limpiarMensajeError(form.camposAtributos.estatus)"
                  :disabled="form.camposAtributos.estatus.disabled"
                  :options="comboEstatus"
                  :state="form.camposAtributos.estatus.state"
                  :value="null"
                  id="estatus"
                  ref="estatus"
                  size="sm"
                  v-model="$v.form.campos.estatus.$model">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione una opción</option>
                  </template>
                </b-form-select>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.fechaContratacion.invalidFeedback"
                class="col-12 col-sm-6"
                label="Fecha de Contratación:"
                label-for="fechaContratacion"
                id="group-fechaContratacion">
                <b-form-datepicker
                  @input="limpiarMensajeError(form.camposAtributos.fechaContratacion)"
                  :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
                  :disabled="form.camposAtributos.fechaContratacion.disabled"
                  :max="form.camposAtributos.fechaContratacion.max"
                  :state="form.camposAtributos.fechaContratacion.state"
                  id="fechaContratacion"
                  label-help="Use las teclas del cursor para navegar por las fechas del calendario"
                  label-no-date-selected="Ninguna fecha seleccionada"
                  locale="es-ES"
                  placeholder="Seleccione una fecha"
                  ref="fechaContratacion"
                  size="sm"
                  v-model="$v.form.campos.fechaContratacion.$model"></b-form-datepicker>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.socio.invalidFeedback"
                class="col-12 col-sm-6"
                label="Socio"
                label-for="socio"
                id="group-socio">
                <b-form-input
                  @blur="valorBlur('socio')"
                  @input="buscarSocio"
                  :disabled="form.camposAtributos.socio.disabled"
                  :state="form.camposAtributos.socio.state"
                  autocomplete="off"
                  id="socio"
                  ref="socio"
                  size="sm"
                  type="text"
                  v-on:focus="valorFocus('socio')"
                  v-model.trim="form.camposAtributos.socio.valor"></b-form-input>
                <b-dropdown id="lista-socio" variant="link" no-caret block ref="ref-lista-socio">
                  <b-dropdown-item-button
                    :key="key"
                    v-for="(socio, key) in form.camposAtributos.socio.listaDropdown.listado"
                    v-if="form.camposAtributos.socio.listaDropdown.listado.length > 0"
                    v-on:click="elegirSocio(socio.id, socio.nombre)"> @{{ socio.nombre }} </b-dropdown-item-button>
                  <b-dropdown-item-button
                    v-if="form.camposAtributos.socio.listaDropdown.noResultado"
                    v-on:click="listadoNoValido('socio')">No se encontrarón socios, intente con otro nombre!</b-dropdown-item-button>
                </b-dropdown>
                <b-form-text id="socio-help" v-html="form.camposAtributos.socio.help"></b-form-text>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.gerente.invalidFeedback"
                class="col-12 col-sm-6"
                label="Gerente"
                label-for="gerente"
                id="group-gerente">
                <b-form-input
                  @blur="valorBlur('gerente')"
                  @input="buscarGerente"
                  :disabled="form.camposAtributos.gerente.disabled"
                  :state="form.camposAtributos.gerente.state"
                  autocomplete="off"
                  id="gerente"
                  ref="gerente"
                  size="sm"
                  type="text"
                  v-on:focus="valorFocus('gerente')"
                  v-model.trim="form.camposAtributos.gerente.valor"></b-form-input>
                <b-dropdown id="lista-gerente" variant="link" no-caret block ref="ref-lista-gerente">
                  <b-dropdown-item-button
                    :key="key"
                    v-for="(gerente, key) in form.camposAtributos.gerente.listaDropdown.listado"
                    v-if="form.camposAtributos.gerente.listaDropdown.listado.length > 0"
                    v-on:click="elegirGerente(gerente.id, gerente.nombre)"> @{{ gerente.nombre }} </b-dropdown-item-button>
                  <b-dropdown-item-button
                    v-if="form.camposAtributos.gerente.listaDropdown.noResultado"
                    v-on:click="listadoNoValido('gerente')">No se encontrarón gerentes, intente con otro nombre!</b-dropdown-item-button>
                </b-dropdown>
                <b-form-text id="gerente-help" v-html="form.camposAtributos.gerente.help"></b-form-text>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.montoEn.invalidFeedback"
                class="col-12 col-sm-6"
                label="Monto en:"
                label-for="montoEn"
                id="group-montoEn">
                <b-form-select
                  @change="monedaSeleccionada"
                  :disabled="form.camposAtributos.montoEn.disabled"
                  :state="form.camposAtributos.montoEn.state"
                  :value="null"
                  id="montoEn"
                  ref="montoEn"
                  size="sm"
                  v-model="$v.form.campos.montoEn.$model">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione una opción</option>
                  </template>
                  <option
                     @click="form.camposAtributos.montoEn.simbolo = moneda.simbolo"
                     :key="index"
                     :simbolo="moneda.simbolo"
                     :value="moneda.value"
                     v-for="(moneda, index) in comboMonedas">@{{ moneda.text }}</option>
                </b-form-select>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.monto.invalidFeedback"
                class="col-12 col-sm-6"
                label="Monto"
                label-for="monto"
                id="group-monto">
                <b-input-group :prepend="form.camposAtributos.montoEn.simbolo" size="sm">
                  <b-form-input
                    @input="limpiarMensajeError(form.camposAtributos.monto)"
                    :disabled="form.camposAtributos.monto.disabled"
                    :state="form.camposAtributos.monto.state"
                    autocomplete="off"
                    id="monto"
                    ref="monto"
                    type="text"
                    v-model.trim="$v.form.campos.monto.$model"></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.empresa.invalidFeedback"
                class="col-12 col-sm-6"
                description="Empresa que se llevará el proyecto"
                label="Empresa:"
                label-for="empresa"
                id="group-empresa">
                <b-form-select
                  @change="limpiarMensajeError(form.camposAtributos.empresa)"
                  :disabled="form.camposAtributos.empresa.disabled"
                  :options="comboEmpresas"
                  :state="form.camposAtributos.empresa.state"
                  :value="null"
                  id="empresa"
                  ref="empresa"
                  size="sm"
                  v-model="$v.form.campos.empresa.$model">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione una opción</option>
                  </template>
                </b-form-select>
              </b-form-group>
              <b-form-group
                class="col-12 col-sm-6"
                label="Divisiones"
                label-for="divisiones"
                id="group-divisiones">
                <multiselect @input="asignarHoras"
                             @Open="limpiarMensajeError(form.camposAtributos.divisiones)"
                             :clear-on-select="false"
                             :close-on-select="false"
                             :disabled="form.camposAtributos.divisiones.disabled"
                             :multiple="true"
                             :options="comboDivisiones"
                             :show-labels="false"
                             clase="form-control form-control-sm"
                             id="divisiones"
                             label="descripcion"
                             placeholder="Seleccione..."
                             ref="divisiones"
                             track-by="descripcion"
                             v-model="$v.form.campos.divisiones.$model">
                   <template slot="selection"
                             slot-scope="{ values, search, isOpen }">
                     <span class="multiselect__single" v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} Seleccionadas</span>
                   </template>
                </multiselect>
                <b-form-invalid-feedback :state="form.camposAtributos.divisiones.state">
                  @{{ form.camposAtributos.divisiones.invalidFeedback }}
                </b-form-invalid-feedback>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.horas.invalidFeedback"
                class="col-12 col-sm-6"
                label="Horas Contratadas"
                label-for="horas"
                id="group-horas">
                <b-form-input
                  :disabled="form.camposAtributos.horas.disabled"
                  :state="form.camposAtributos.horas.state"
                  autocomplete="off"
                  id="horas"
                  ref="horas"
                  size="sm"
                  type="text"
                  v-model="form.campos.horas"></b-form-input>
              </b-form-group>
              <b-form-group class="col-12" v-if="form.camposAtributos.divisiones.divisiones.length > 0">
                <b-badge variant="warning">Indica la cantidad de horas por división</b-badge>
              </b-form-group>
              <b-form-group :key="index" class="col-12" v-for="(division, index) in form.camposAtributos.divisiones.divisiones">
                <b-row>
                  <b-form-group
                    class="col-12 col-sm-4"
                    label="División">
                    <b-form-input
                      :value="division.descripcion+`:`"
                      plaintext
                      readonly
                      size="sm"></b-form-input>
                  </b-form-group>
                  <b-form-group
                    class="col-12 col-sm-5"
                    label="Gerente">
                    <b-form-select
                      @change="limpiarMensajeError(form.camposAtributos.divisiones.divisiones[index].gerente)"
                      :disabled="form.camposAtributos.divisiones.divisiones[index].gerente.disabled"
                      :options="form.camposAtributos.divisiones.divisiones[index].gerente.listado"
                      :ref="'division-'+index"
                      :state="form.camposAtributos.divisiones.divisiones[index].gerente.state"
                      :value="null"
                      size="sm"
                      v-model="form.camposAtributos.divisiones.divisiones[index].gerente.id">
                      <template v-slot:first>
                        <option :value="null" disabled="true">Seleccione un gerente</option>
                      </template>
                    </b-form-select>
                    <b-form-text v-html="form.camposAtributos.divisiones.divisiones[index].gerente.help"></b-form-text>
                    <b-form-invalid-feedback>
                        @{{ form.camposAtributos.divisiones.divisiones[index].gerente.invalidFeedback }}
                    </b-form-invalid-feedback>
                  </b-form-group>
                  <b-form-group
                    :invalid-feedback="form.camposAtributos.divisiones.divisiones[index].horas.invalidFeedback"
                    class="col-12 col-sm-3"
                    label="Horas">
                    <b-form-input
                      @input="horaDivision(index)"
                      :disabled="form.camposAtributos.divisiones.disabled"
                      :formatter="cantidadHora"
                      :number="true"
                      :ref="'hora-'+index"
                      :state="form.camposAtributos.divisiones.divisiones[index].horas.state"
                      class="form-control hora-asignada"
                      placeholder="0"
                      size="sm"
                      v-model="form.camposAtributos.divisiones.divisiones[index].horas.value"></b-form-input>
                  </b-form-group>
                </b-row>
              </b-form-group>
              <b-form-group class="col-12">
                <alert :contador="form.alert.contador"
                       :icono-cerrar="form.alert.iconCerrar"
                       :mensaje="form.alert.mensaje"
                       :mostrar="form.alert.mostrar"
                       :ocultar-seg="form.alert.ocultarSeg"
                       :variante="form.alert.variante">
                </alert>
                <b-button
                  @click="confirmarCrearProyecto"
                  block
                  size="sm"
                  v-html="form.botones.confirmar.html"
                  v-if="form.botones.confirmar.show"
                  variant="success"></b-button>
                <b-button
                  @click="cancelarCrearProyecto"
                  :disabled="form.botones.cancelar.disabled"
                  block
                  size="sm"
                  v-html="form.botones.cancelar.html"
                  v-if="form.botones.cancelar.show"
                  variant="danger"></b-button>
                <b-button
                  @click="crear"
                  :disabled="form.botones.submit.disabled"
                  block
                  size="sm"
                  v-html="form.botones.submit.html"
                  v-if="form.botones.submit.show"
                  variant="success"></b-button>
                <b-button
                  @click="refreshView"
                  block
                  size="sm"
                  v-html="form.botones.refresh.html"
                  v-if="form.botones.refresh.show"
                  variant="primary">Quiero crear un nuevo proyecto</b-button>
              </b-form-group>
            </b-form>

          </b-col>

          <b-col sm="11" md="9" lg="8" v-cloak>
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

      <script src="{{ mix('/js/nuevoProyecto.js') }}"></script>

    </body>
</html>
