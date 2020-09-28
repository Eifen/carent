<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/ingresosGastos.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>

        <menu-principal></menu-principal>

        <b-row align-h="center" align-v="center" v-if="alert.mostrar === false && formFiltro.mostrar" class="wrapper-forms" v-cloak>
          <b-col cols="12" v-if="formFiltro.mostrar">
            <h5>Filtros de búsqueda</h5>
            <b-form class="row">
              <b-form-group
                class="form-group col-12 col-sm-4"
                label="Proyecto"
                label-for="proyecto"
                id="group-proyecto">
                <b-form-input
                  :disabled="formFiltro.proyecto.disabled"
                  id="proyecto"
                  ref="proyecto"
                  size="sm"
                  type="text"
                  v-model.trim="formFiltro.proyecto.value"></b-form-input>
                <b-form-text id="proyecto-help">Nombre que se le dío la proyecto</b-form-text>
              </b-form-group>
              <b-form-group
                class="form-group col-12 col-sm-8"
                label="Cliente"
                label-for="cliente"
                id="group-cliente">
                <b-form-input
                  :disabled="formFiltro.cliente.disabled"
                  id="cliente"
                  ref="cliente"
                  size="sm"
                  type="text"
                  v-model.trim="formFiltro.cliente.value"></b-form-input>
                <small id="cliente-Help" class="form-text text-muted">Razón Social del Cliente</small>
              </b-form-group>
              <b-form-group
                class="form-group col-12 col-sm-4"
                label="Estatus"
                label-for="estatus"
                id="group-estatus">
                <b-form-select
                  :disabled="formFiltro.estatus.disabled"
                  :value="null"
                  :options="comboEstatus"
                  id="estatus"
                  ref="estatus"
                  size="sm"
                  v-model="formFiltro.estatus.value">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione...</option>
                  </template>
                </b-form-select>
                <small id="estatus-Help" class="form-text text-muted">Estatus del proyecto</small>
              </b-form-group>
              <b-form-group class="col-12 col-sm-4">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formFiltro.btn.filtrar.disabled"
                  block
                  class="filtrar"
                  size="sm"
                  v-html="formFiltro.btn.filtrar.html"
                  v-on:click="buscar">
              </b-form-group>
              <b-form-group class="col-12 col-sm-4">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formFiltro.btn.limpiarFiltro.disabled"
                  block
                  class="limpiar_filtro"
                  size="sm"
                  v-html="formFiltro.btn.limpiarFiltro.html"
                  v-on:click="limpiarFiltro">
              </b-form-group>
            </b-form>

          </b-col>

          <b-col cols="12">
            <h5>Proyectos Disponibles</h5>
          </b-col>

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
              <template v-slot:cell(estatus)="data">
                <b-badge :variant="data.item.variante">@{{ data.item.estatus }}</b-badge>
              </template>
              <template v-slot:cell(editar)="data">
                <a :href="'/formAgregarIngresosGastos/'+data.item.id" target="_self" :id="'editar-'+data.item.id" v-if="permisos.permiso_actualizar">
                   <b-icon-gear class="icono"></b-icon-gear>
                </a>
                <b-tooltip :target="'editar-'+data.item.id" triggers="hover">
                  Agregar Factura/Gasto
                </b-tooltip>
              </template>
              <template v-slot:custom-foot v-if="tabla.registros.length > 0">
                <b-tr>
                  <b-td colspan="8">
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

        <b-row align-h="center" align-v="center" v-if="alert.mostrar" v-cloak>
          <b-col cols="12" sm="11" md="10" lg="8" xl="7">
            <b-alert variant="warning" show class="text-center" v-html="alert.message"></b-alert>
          </b-col>
        </b-row>

      </b-container>

      <script src="{{ mix('/js/ingresosGastos.js') }}"></script>

    </body>
</html>
