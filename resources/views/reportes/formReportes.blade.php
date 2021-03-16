<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/formReportes.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>

        <menu-principal></menu-principal>

        <b-row align-h="center" align-v="center" v-if="alert.mostrar === false && formReportes.mostrar" class="wrapper-forms" v-cloak>
          <b-col cols="12 col-sm-10" v-if="formReportes.mostrar">
            <h5>Reportes Disponibles</h5>
            <b-form class="row">
              <b-form-group
                class="form-group col-12 col-sm-7 col-md-8"
                label="Tipo de reporte"
                label-for="proyecto"
                id="group-proyecto">
                <b-form-select
                  @change="reporteSeleccionado"
                  :disabled="formReportes.reportes.disabled"
                  :value="null"
                  :options="formReportes.reportes.listado"
                  id="reportes"
                  ref="reportes"
                  size="sm"
                  v-model="formReportes.reportes.value">
                  <template v-slot:first>
                    <option :value="null" disabled="true">Seleccione...</option>
                  </template>
                </b-form-select>
              </b-form-group>
              <b-form-group class="col-12 col-sm-5 col-md-4">
                <label>&nbsp;</label>
                <b-button
                  :disabled="formReportes.btn.generar.disabled"
                  block
                  size="sm"
                  v-html="formReportes.btn.generar.html"
                  v-on:click="generarReporte"
                  variant="primary">
              </b-form-group>
            </b-form>

          </b-col>
        </b-row>

        <b-row align-h="center" align-v="center" v-cloak>
          <b-col cols="12">
            <reporte-1 v-if="formReportes.reportes.verReporte.id === 18" :key="formReportes.reportes.verReporte.key"></reporte-1>
            <reporte-2 v-if="formReportes.reportes.verReporte.id === 19" :key="formReportes.reportes.verReporte.key"></reporte-2>
            <reporte-3 v-if="formReportes.reportes.verReporte.id === 20" :key="formReportes.reportes.verReporte.key"></reporte-3>
            <reporte-4 v-if="formReportes.reportes.verReporte.id === 21" :key="formReportes.reportes.verReporte.key"></reporte-4>
            <reporte-5 v-if="formReportes.reportes.verReporte.id === 22" :key="formReportes.reportes.verReporte.key"></reporte-5>
            <reporte-6 v-if="formReportes.reportes.verReporte.id === 23" :key="formReportes.reportes.verReporte.key"></reporte-6>
            <reporte-7 v-if="formReportes.reportes.verReporte.id === 24" :key="formReportes.reportes.verReporte.key"></reporte-7>
            <reporte-8 v-if="formReportes.reportes.verReporte.id === 25" :key="formReportes.reportes.verReporte.key"></reporte-8>
            <reporte-9 v-if="formReportes.reportes.verReporte.id === 26" :key="formReportes.reportes.verReporte.key"></reporte-9>
          </b-col>
        </b-row>

        <b-row align-h="center" align-v="center" v-if="alert.mostrar" v-cloak>
          <b-col cols="12" sm="11" md="10" lg="8" xl="7">
            <b-alert variant="warning" show class="text-center" v-html="alert.message"></b-alert>
          </b-col>
        </b-row>

      </b-container>

      <script src="{{ mix('/js/formReportes.js') }}"></script>

    </body>
</html>
