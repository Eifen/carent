<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">
        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        @vite('resources/css/fontawesome-free-5.12.0.css')
        @vite('resources/css/nuevoCargo.css')
    </head>
    <body>
      <div id="nuevoCargo" class="container-fluid">
        <menu-principal></menu-principal>
        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <h3>Estas creando un nuevo cargo</h3>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="nuevoCargo">Nuevo Cargo<span class="campo-obligatorio">*</span></label>
                <input aria-describedby="nuevoCargoHelp"
                       class="form-control text-lowercase"
                       id="nuevoCargo"
                       v-bind:disabled="form.nuevoCargo.disabled"
                       v-model="form.nuevoCargo.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="nuevoCargoHelp" class="form-text text-muted">Ejemplo: Gerente...</small>
                <div class="mensaje"></div>
              </div>
            </form>
            <div class="row justify-content-center wrapper-subtmit">
              <div class="col-12 col-md-6 col-lg-4">
                <button class="btn"
                        type="button"
                        v-on:click="crear"
                        v-bind:disabled="submitCrear.disabled"
                        v-html="submitCrear.content"
                        v-if="submitCrear.show"></button>
              </div>
            </div>
            <div class="row justify-content-center wrapper-refrescar" v-if="refreshForm">
              <div class="col-12 col-md-6 col-lg-4">
                <button class="btn"
                        type="button"
                        v-on:click="refreshView">Agregar un nuevo cargo</button>
              </div>
            </div>
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      @vite('resources/js/nuevoCargo.js')
    </body>
</html>
