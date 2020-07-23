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

        <b-row align-h="center" align-v="center" v-if="alert.mostrar === false && formFiltro.mostrar" v-cloak>
          <b-col cols="12" sm="11" md="9" v-if="formFiltro.mostrar" class="wrapper-forms">
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
                class="form-group col-12 col-sm-4"
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
              <b-form-group
                class="form-group col-12 col-sm-4"
                label="Divisiones"
                label-for="divisiones"
                id="group-divisiones">
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
                <small id="divisiones-Help" class="form-text text-muted">Cuando seleccione divisiones aparecerán los proyectos asociados</small>
              </b-form-group>
              <b-form-group class="col-12 col-sm-3">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formFiltro.btn.filtrar.disabled"
                  block
                  class="filtrar"
                  size="sm"
                  v-html="formFiltro.btn.filtrar.html"
                  v-on:click="buscar">
              </b-form-group>
              <b-form-group class="col-12 col-sm-3">
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
