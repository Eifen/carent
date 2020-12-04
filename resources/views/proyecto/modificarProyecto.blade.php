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
        <link href="{{ mix('/css/modificarProyecto.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid" v-on:keypress="keyboard" v-cloak>

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal></menu-principal>

        <b-row align-h="center">
          <b-col cols="12" md="4" lg="2">
            <b-button
              block
              href="{{ url()->previous() }}"
              size="sm"
              variant="primary">Regresar</b-button>
          </b-col>
        </b-row>

        <b-row align-h="center" align-v="center">
          <b-col cols="12" md="9" lg="8" v-if="form.mostrar">
            <h4>Estas Modificando un Proyecto</h4>
            <b-form class="row">
              <b-form-group
                :invalid-feedback="form.camposAtributos.descripcion.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: Proyecto 1"
                label="Descripción"
                label-for="descripcion"
                id="group-descripcion">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.descripcion)"
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
                  @change="cleanFieldForm(form.camposAtributos.estatus)"
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
                  @input="cleanFieldForm(form.camposAtributos.fechaContratacion)"
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
                    @input="cleanFieldForm(form.camposAtributos.monto)"
                    :disabled="form.camposAtributos.monto.disabled"
                    :state="form.camposAtributos.monto.state"
                    autocomplete="off"
                    id="monto"
                    ref="monto"
                    type="text"
                    v-model.trim="$v.form.campos.monto.$model"></b-form-input>
                    <b-input-group-append v-b-modal="'modal-agregar-monto'">
                      <b-button
                        size="sm"
                        variant="success">
                        Montos Adicionales
                      </b-button>
                    </b-input-group-append>
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
                  @change="cleanFieldForm(form.camposAtributos.empresa)"
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
                             @Open="cleanFieldForm(form.camposAtributos.divisiones)"
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
                      @change="cleanFieldForm(form.camposAtributos.divisiones.divisiones[index].gerente)"
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
                    <b-input-group>
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
                        <b-input-group-append>
                          <b-button
                            @click="modalAgregarHoraAdicional(division.idDivProy, division.descripcion)"
                            :disabled="modalAgregarMonto.botones.submit.disabled"
                            size="sm"
                            v-b-tooltip.hover title="Agregar horas adicionales"
                            variant="success">
                            <b-icon icon="plus"></b-icon>
                          </b-button>
                        </b-input-group-append>
                      </b-input-group>
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
                  @click="confirmarModificarProyecto"
                  block
                  size="sm"
                  v-html="form.botones.confirmar.html"
                  v-if="form.botones.confirmar.show"
                  variant="warning"></b-button>
                <b-button
                  @click="cancelarModificarProyecto"
                  :disabled="form.botones.cancelar.disabled"
                  block
                  size="sm"
                  v-html="form.botones.cancelar.html"
                  v-if="form.botones.cancelar.show"
                  variant="danger"></b-button>
                <b-button
                  @click="modificar"
                  :disabled="form.botones.submit.disabled"
                  block
                  size="sm"
                  v-html="form.botones.submit.html"
                  v-if="form.botones.submit.show"
                  variant="success"></b-button>
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

        <b-modal
          :hide-footer="modalAgregarMonto.footer.hide"
          :id="'modal-agregar-monto'"
          :no-close-on-backdrop="true"
          :ref="'modal-agregar-monto'"
          centered
          size="lg">
          <template v-slot:modal-title>
            Montos adicionales para el proyecto
          </template>
          <b-form>
            <b-form-group
              label="Monto Adicional"
              label-for="montoAdicional"
              id="laber-montoAdicional">
              <b-input-group size="sm" :prepend="form.camposAtributos.montoEn.simbolo">
                <b-form-input
                  @input="cleanFieldForm(modalAgregarMonto.form.campos.montoAdicional)"
                  :disabled="modalAgregarMonto.form.campos.montoAdicional.disabled"
                  :state="modalAgregarMonto.form.campos.montoAdicional.state"
                  autocomplete="off"
                  id="montoAdicional"
                  ref="montoAdicional"
                  type="text"
                  v-model.trim="$v.modalAgregarMonto.form.campos.montoAdicional.value.$model"></b-form-input>
                <b-input-group-append>
                  <b-button
                    @click="agregar_monto_adicional"
                    :disabled="modalAgregarMonto.botones.submit.disabled"
                    size="sm"
                    v-html="modalAgregarMonto.botones.submit.html"
                    v-if="modalAgregarMonto.botones.submit.show"
                    variant="success">
                  </b-button>
                </b-input-group-append>
                <b-form-invalid-feedback>
                    @{{ modalAgregarMonto.form.campos.montoAdicional.invalidFeedback }}
                </b-form-invalid-feedback>
              </b-input-group>
            </b-form-group>
          </b-form>

          <b-table
            :busy="modalAgregarMonto.montosAdicionales.cargando"
            :fields="modalAgregarMonto.montosAdicionales.encabezado"
            :items="modalAgregarMonto.montosAdicionales.registros"
            :small="true"
            foot-clone
            hover
            responsive
            show-empty>
            <template v-slot:table-busy>
              <div class="text-center text-primary">
                <b-spinner class="align-middle"></b-spinner>
              </div>
            </template>
            <template v-slot:empty="scope" v-if="modalAgregarMonto.montosAdicionales.alert.mostrar">
              <alert :contador="modalAgregarMonto.montosAdicionales.alert.contador"
                     :icono-cerrar="modalAgregarMonto.montosAdicionales.alert.iconCerrar"
                     :mensaje="modalAgregarMonto.montosAdicionales.alert.mensaje"
                     :mostrar="modalAgregarMonto.montosAdicionales.alert.mostrar"
                     :ocultar-seg="modalAgregarMonto.montosAdicionales.alert.ocultarSeg"
                     :variante="modalAgregarMonto.montosAdicionales.alert.variante">
              </alert>
            </template>
            <template v-slot:cell(numero)="data">
              <b>@{{ data.item.numero }}</b>
            </template>
            <template v-slot:cell(opciones)="data">
              <b-icon-trash
                :id="'eliminar-'+data.item.id"
                class="icono"
                v-on:click="eliminar_monto(data.item.id)"></b-icon-trash>
              <b-tooltip :target="'eliminar-'+data.item.id" triggers="hover">
                Eliminar monto
              </b-tooltip>
            </template>
            <template #foot(numero)="numero">
              <div class="text-center">Total</div>
            </template>
            <template #foot(monto)="data">
              <div class="text-center">@{{ modalAgregarMonto.montosAdicionales.total }}</div>
            </template>
            <template #foot(fecha)="data">
              <div class="text-center"></div>
            </template>
          </b-table>

          <template v-slot:modal-footer="{ ok, cancel, hide }">
            <alert :contador="modalAgregarMonto.alert.contador"
                   :icono-cerrar="modalAgregarMonto.alert.iconCerrar"
                   :mensaje="modalAgregarMonto.alert.mensaje"
                   :mostrar="modalAgregarMonto.alert.mostrar"
                   :ocultar-seg="modalAgregarMonto.alert.ocultarSeg"
                   :variante="modalAgregarMonto.alert.variante">
            </alert>
            <b-button
              @click="cancelarAgregarMonto"
              :disabled="modalAgregarMonto.botones.cancelar.disabled"
              block
              size="sm"
              v-html="modalAgregarMonto.botones.cancelar.html"
              v-if="modalAgregarMonto.botones.cancelar.show"
              variant="danger">
            </b-button>
            <b-button
              @click="agregarMontoAdicional"
              :disabled="modalAgregarMonto.botones.confirmar.disabled"
              block
              size="sm"
              v-html="modalAgregarMonto.botones.confirmar.html"
              v-if="modalAgregarMonto.botones.confirmar.show"
              variant="success"></b-button>
          </template>
        </b-modal>

        <b-modal
          :hide-footer="modalAgregarHora.footer.hide"
          :id="'modal-agregar-hora'"
          :no-close-on-backdrop="true"
          :ref="'modal-agregar-hora'"
          centered
          size="lg">
          <template v-slot:modal-title>
            Horas adicionales para @{{ modalAgregarHora.division }}
          </template>
          <b-form>
            <b-form-group
              label="Horas Adicionales"
              label-for="horaAdicional"
              id="laber-horaAdicional">
              <b-input-group size="sm">
                <b-form-input
                  @input="cleanFieldForm(modalAgregarHora.form.campos.horaAdicional)"
                  :disabled="modalAgregarHora.form.campos.horaAdicional.disabled"
                  :state="modalAgregarHora.form.campos.horaAdicional.state"
                  autocomplete="off"
                  id="horaAdicional"
                  ref="horaAdicional"
                  type="text"
                  v-model.trim="$v.modalAgregarHora.form.campos.horaAdicional.value.$model"></b-form-input>
                <b-input-group-append>
                  <b-button
                    @click="agregarHoraAdicional"
                    :disabled="modalAgregarHora.botones.submit.disabled"
                    size="sm"
                    v-html="modalAgregarHora.botones.submit.html"
                    v-if="modalAgregarHora.botones.submit.show"
                    variant="success">
                  </b-button>
                </b-input-group-append>
                <b-form-invalid-feedback>
                    @{{ modalAgregarHora.form.campos.horaAdicional.invalidFeedback }}
                </b-form-invalid-feedback>
              </b-input-group>
            </b-form-group>
          </b-form>

          <b-table
            :busy="modalAgregarHora.horasAdicionales.cargando"
            :fields="modalAgregarHora.horasAdicionales.encabezado"
            :items="modalAgregarHora.horasAdicionales.registros"
            :small="true"
            foot-clone
            hover
            responsive
            show-empty>
            <template v-slot:table-busy>
              <div class="text-center text-primary">
                <b-spinner class="align-middle"></b-spinner>
              </div>
            </template>
            <template v-slot:empty="scope" v-if="modalAgregarHora.horasAdicionales.alert.mostrar">
              <alert :contador="modalAgregarHora.horasAdicionales.alert.contador"
                     :icono-cerrar="modalAgregarHora.horasAdicionales.alert.iconCerrar"
                     :mensaje="modalAgregarHora.horasAdicionales.alert.mensaje"
                     :mostrar="modalAgregarHora.horasAdicionales.alert.mostrar"
                     :ocultar-seg="modalAgregarHora.horasAdicionales.alert.ocultarSeg"
                     :variante="modalAgregarHora.horasAdicionales.alert.variante">
              </alert>
            </template>
            <template v-slot:cell(numero)="data">
              <b>@{{ data.item.numero }}</b>
            </template>
            <template v-slot:cell(opciones)="data">
              <b-icon-trash
                :id="'eliminar-'+data.item.id"
                class="icono"
                v-on:click="eliminar_hora(data.item.id)"></b-icon-trash>
              <b-tooltip :target="'eliminar-'+data.item.id" triggers="hover">
                Eliminar hora
              </b-tooltip>
            </template>
            <template #foot(numero)="numero">
              <div class="text-center">Total</div>
            </template>
            <template #foot(horas)="data">
              <div class="text-center">@{{ modalAgregarHora.horasAdicionales.total }}</div>
            </template>
            <template #foot(fecha)="data">
              <div class="text-center"></div>
            </template>
          </b-table>

          <template v-slot:modal-footer="{ ok, cancel, hide }">
            <alert :contador="modalAgregarHora.alert.contador"
                   :icono-cerrar="modalAgregarHora.alert.iconCerrar"
                   :mensaje="modalAgregarHora.alert.mensaje"
                   :mostrar="modalAgregarHora.alert.mostrar"
                   :ocultar-seg="modalAgregarHora.alert.ocultarSeg"
                   :variante="modalAgregarHora.alert.variante">
            </alert>
            <b-button
              @click="cancelarAgregarHora"
              :disabled="modalAgregarHora.botones.cancelar.disabled"
              block
              size="sm"
              v-html="modalAgregarHora.botones.cancelar.html"
              v-if="modalAgregarHora.botones.cancelar.show"
              variant="danger">
            </b-button>
            <b-button
              @click="confirmarAgregarHoraAdicional"
              :disabled="modalAgregarHora.botones.confirmar.disabled"
              block
              size="sm"
              v-html="modalAgregarHora.botones.confirmar.html"
              v-if="modalAgregarHora.botones.confirmar.show"
              variant="success"></b-button>
          </template>
        </b-modal>

      </div>
      <script src="{{ mix('/js/modificarProyecto.js') }}"></script>
    </body>
</html>
