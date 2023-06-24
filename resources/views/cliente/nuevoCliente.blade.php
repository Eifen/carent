<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        @vite('resources/less/cliente/nuevoCliente.less')

    </head>
    <body>
      <b-container fluid id="nuevoCliente" v-on:keypress="keyboard">
        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>
        <b-row align-h="center" align-v="center" v-if="alert.mostrar === false && formFiltro.mostrar" class="wrapper-forms" v-cloak>
          <b-col cols="12 col-sm-10" v-if="formFiltro.mostrar">
            <b-row v-cloak>
              <b-col cols="12" sm="11" md="9" lg="8" v-if="form.mostrar">
                <h4 class="titulo-principal">Estas creando un nuevo cliente</h4>
              </b-col>
            </b-row>
            <b-form class="row">
              <b-form-group class="col-12">
                <h5>Agregar Socio:</h5>
              </b-form-group>
              <b-form-group
                class="form-group col-12 col-sm-6"
                label="Nombre del Socio:"
                label-for="nombre"
                id="group-nombre">
                <b-form-input
                  :disabled="formFiltro.nombre.disabled"
                  id="nombre"
                  ref="nombre"
                  size="sm"
                  type="text"
                  v-model.trim="formFiltro.nombre.value"></b-form-input>
              </b-form-group>
              <b-form-group class="col-12 col-sm-3" v-b-modal="'modal-detalle-usuario'">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formFiltro.btn.filtrar.disabled"
                  block
                  size="sm"
                  v-html="formFiltro.btn.filtrar.html"
                  variant="primary">
              </b-form-group>
              <b-form-group class="col-12 col-sm-3">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formFiltro.btn.limpiarFiltro.disabled"
                  block
                  size="sm"
                  v-html="formFiltro.btn.limpiarFiltro.html"
                  @click="limpiarFiltro"
                  variant="outline-primary">
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.codigoUsuario.invalidFeedback"
                class="col-12 col-sm-6"
                description="Código del Socio Seleccionado"
                label="Código del Socio"
                label-for="codigoUsuario"
                id="group-codigoUsuario">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.codigoUsuario)"
                  :disabled="form.camposAtributos.codigoUsuario.disabled"
                  :state="form.camposAtributos.codigoUsuario.state"
                  autocomplete="off"
                  class="text-uppercase"
                  id="codigoUsuario"
                  ref="codigoUsuario"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.codigoUsuario.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.nombre.invalidFeedback"
                class="col-12 col-sm-6"
                description="Nombre del Socio Seleccionado"
                label="Nombre"
                label-for="nombre"
                id="group-nombre">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.nombre)"
                  :disabled="form.camposAtributos.nombre.disabled"
                  :state="form.camposAtributos.nombre.state"
                  autocomplete="off"
                  class="text-uppercase"
                  id="nombre"
                  ref="nombre"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.nombre.$model"></b-form-input>
              </b-form-group>
              <b-form-group class="col-12">
                <h5>Datos del Clientes</h5>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.codigoCliente.invalidFeedback"
                class="col-12 col-sm-6"
                description="Código del Cliente"
                label="Código del Cliente"
                label-for="codigoCliente"
                id="group-codigoCliente">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.codigoCliente)"
                  :disabled="form.camposAtributos.codigoCliente.disabled"
                  :state="form.camposAtributos.codigoCliente.state"
                  autocomplete="off"
                  class="text-uppercase"
                  id="codigoCliente"
                  ref="codigoCliente"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.codigoCliente.$model"></b-form-input>
              </b-form-group>
              {{--NIT--}}
              <b-form-group
                :invalid-feedback="form.camposAtributos.nit.invalidFeedback"
                class="col-12 col-sm-6"
                description="NIT"
                label="Nit del Cliente"
                label-for="nit"
                id="group-nit">
                <b-form-input
                @input="cleanFieldForm(form.camposAtributos.nit)"
                :disabled="form.camposAtributos.nit.disabled"
                :state="form.camposAtributos.nit.state"
                autocomplete="off"
                class="text-uppercase"
                id="nit"
                ref="nit"
                size="sm"
                type="text"
                v-model="v$.form.campos.nit.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.rif.invalidFeedback"
                class="col-12 col-sm-6"
                description="Iniciar con: V-, E-, P-, G-, J-, C-"
                label="Rif"
                label-for="rif"
                id="rif">
                <the-mask
                  mask="F- MMMMMMMMMM" :tokens="hexTokens"
                  @input="cleanFieldForm(form.camposAtributos.rif)"
                  :disabled="form.camposAtributos.rif.disabled"
                  :state="form.camposAtributos.rif.state"
                  autocomplete="off"
                  class="text-uppercase form-control form-control-sm"
                  id="rif"
                  ref="rif"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.rif.$model"></the-mask>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.razon_social.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: Banco..."
                label="Nombre o Razón social"
                label-for="razon_social"
                id="group-razon_social">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.razon_social)"
                  :disabled="form.camposAtributos.razon_social.disabled"
                  :state="form.camposAtributos.razon_social.state"
                  autocomplete="off"
                  class="text-uppercase"
                  id="razon_social"
                  ref="razon_social"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.razon_social.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.direccion.invalidFeedback"
                class="col-12 col-sm-6"
                description="Dirección del cliente"
                label="Dirección"
                label-for="direccion"
                id="group-direccion">
                <b-form-textarea
                  @input="cleanFieldForm(form.camposAtributos.direccion)"
                  :disabled="form.camposAtributos.direccion.disabled"
                  :state="form.camposAtributos.direccion.state"
                  autocomplete="off"
                  class="text-none"
                  id="direccion"
                  ref="direccion"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.direccion.$model"></b-form-textarea>
              </b-form-group>
              {{-- SECTORES Y SERVICIOS --}}
              <b-form-group
                class="col-12 col-sm-6"
                id="group-sector"
                :invalid-feedback="form.camposAtributos.sector.invalidFeedback"
                description = "Sector asociado"
                label = "Sector"
                label-for = "sector">
                <b-form-select
                @change = "cleanFieldForm(form.camposAtributos.sector)"
                :disabled="form.camposAtributos.sector.disabled"
                :options="comboSectores"
                :state="form.camposAtributos.sector.state"
                :value="null"
                id="sector"
                ref="sector"
                size="sm"
                v-model="v$.form.campos.sector.$model">
                <template v-slot:first>
                  {{--Si esta vacio mostramos un mensaje--}}
                  <option :value="null" disabled="true">Seleccione una opción</option>
                </template>
                </b-form-select>
              </b-form-group>
              {{--SERVICIOS--}}
              <b-form-group
                {{--Anexamos el mensaje de feeback--}}
                :invalid-feedback="form.camposAtributos.servicios.invalidFeedback"
                class="col-12 col-sm-6"
                id="group-servicios"
                description="Servicio asociado"
                label="Servicio"
                label-for="servicios">
                <b-form-select
                @change="cleanFieldForm(form.camposAtributos.servicios)"
                :disabled="form.camposAtributos.servicios.disabled"
                :options="comboServicios"
                :state="form.camposAtributos.servicios.state"
                :value="null"
                id="servicios"
                ref="servicios"
                size="sm"
                v-model="v$.form.campos.servicios.$model">
                <template v-slot:first>
                  <option :value="null" disabled="true">Seleccione una opción</option>
                </template>
                </b-form-select>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.pais.invalidFeedback"
                class="col-12 col-sm-6"
                description="Pais del Cliente"
                label="Pais"
                label-for="pais"
                id="group-pais">
                <b-form-select
                  @change="cleanFieldForm(form.camposAtributos.pais)"
                  @input="pais"
                  :disabled="form.camposAtributos.pais.disabled"
                  :options="comboPaises"
                  :state="form.camposAtributos.pais.state"
                  :value="null"
                  id="pais"
                  ref="pais"
                  size="sm"
                  v-model="v$.form.campos.pais.$model">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione una opción</option>
                  </template>
                </b-form-select>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.telefono_fiscal.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: + 584241234567"
                label="Nº de Teléfono Principal"
                label-for="telefono_fiscal"
                id="group-telefono_fiscal">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.telefono_fiscal)"
                  :disabled="form.camposAtributos.telefono_fiscal.disabled"
                  :state="form.camposAtributos.telefono_fiscal.state"
                  autocomplete="off"
                  class="text-uppercase"
                  v-mask="'+ ################'"
                  id="telefono_fiscal"
                  ref="telefono_fiscal"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.telefono_fiscal.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.pagina_web.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: https://www.crowe.com/ve"
                label="Página web"
                label-for="pagina_web"
                id="group-pagina_web">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.pagina_web)"
                  :disabled="form.camposAtributos.pagina_web.disabled"
                  :state="form.camposAtributos.pagina_web.state"
                  autocomplete="off"
                  class="text-none"
                  id="pagina_web"
                  ref="pagina_web"
                  size="sm"
                  type="text"
                  v-model="v$.form.campos.pagina_web.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.email_fiscal.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: sistema.carent@crowe.com.ve"
                label="Correo Electrónico"
                label-for="email_fiscal"
                id="group-email_fiscal">
                <b-form-input
                  @input="cleanFieldForm(form.camposAtributos.email_fiscal)"
                  :disabled="form.camposAtributos.email_fiscal.disabled"
                  :state="form.camposAtributos.email_fiscal.state"
                  autocomplete="off"
                  class="text-none"
                  id="email_fiscal"
                  ref="email_fiscal"
                  size="sm"
                  type="email"
                  v-model="v$.form.campos.email_fiscal.$model"></b-form-input>
              </b-form-group>
              <b-form-group class="col-12">
                <alert :contador="form.alert.contador"
                       :icono-cerrar="form.alert.iconCerrar"
                       :mensaje="form.alert.mensaje"
                       :mostrar="form.alert.mostrar"
                       :ocultar-seg="form.alert.ocultarSeg"
                       :variante="form.alert.variante">
                </alert>
                <b-form-group></b-form-group>
                <b-button
                  @click="confirmarCrearCliente"
                  block
                  size="sm"
                  v-html="form.botones.confirmar.html"
                  v-if="form.botones.confirmar.show"
                  variant="warning"></b-button>
                <b-button
                  @click="cancelarCrearCliente"
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
                  variant="primary">Quiero crear un nuevo cliente</b-button>
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
          :hide-footer="modalDetalleUsuario.footer.hide"
          :id="'modal-detalle-usuario'"
          :no-close-on-backdrop="false"
          :ref="'modal-detalle-usuario'"
          centered
          size="lg">
          <template v-slot:modal-title>
            Agregar Socio
          </template>
          <b-table
            :busy="modalDetalleUsuario.agregarSocio.cargando"
            :fields="modalDetalleUsuario.agregarSocio.encabezado"
            :items="modalDetalleUsuario.agregarSocio.registros"
            :small="true"
            hover
            responsive
            show-empty>
            <template v-slot:table-busy>
              <div class="text-center text-primary">
                <b-spinner class="align-middle"></b-spinner>
              </div>
            </template>
            <template v-slot:empty="scope" v-if="modalDetalleUsuario.agregarSocio.alert.mostrar">
              <alert :contador="modalDetalleUsuario.agregarSocio.alert.contador"
                     :icono-cerrar="modalDetalleUsuario.agregarSocio.alert.iconCerrar"
                     :mensaje="modalDetalleUsuario.agregarSocio.alert.mensaje"
                     :mostrar="modalDetalleUsuario.agregarSocio.alert.mostrar"
                     :ocultar-seg="modalDetalleUsuario.agregarSocio.alert.ocultarSeg"
                     :variante="modalDetalleUsuario.agregarSocio.alert.variante">
              </alert>
            </template>
            <template v-slot:cell(opciones)="data">
              <b-icon-check
                :id="'selecionar-'+data.item.id"
                class="icono"
                v-on:click="SelecionarUsuario(data.item.id)">
              </b-icon-check>
              <b-tooltip :target="'selecionar-'+data.item.id" triggers="hover">
                Seleccionar Socio
              </b-tooltip>
            </template>
          </b-table>
        </b-modal>
      </b-container>
      <script src="{{ mix('/js/nuevoCliente.js') }}"></script>
    </body>
</html>
