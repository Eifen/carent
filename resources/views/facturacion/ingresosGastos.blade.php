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
