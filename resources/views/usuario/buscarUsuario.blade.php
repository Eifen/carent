<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/buscarUsuario.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="buscarUsuario" class="container-fluid">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-10 col-lg-8 col-xl-7">
            <form class="row">
              <div class="form-group col-12 col-md-4">
                <select class="form-control"
                        v-bind:disabled="formSearch.select.disabled"
                        v-model="formSearch.select.value"
                        v-on:change="tipoFiltro">
                  <option value="" selected disabled>Consultar por</option>
                  <option value="1">Código de usuario</option>
                  <option value="2">Cédula</option>
                  <option value="3">Correo electrónico</option>
                  <option value="4">Nombres o Apellidos</option>
                </select>
              </div>
              <div class="form-group col-12 col-md-6">
                <input class="form-control"
                       id="inputSearch"
                       type="text"
                       v-bind:disabled="formSearch.inputSearch.disabled"
                       v-on:keyup="evaluarCampo"
                       v-model="formSearch.inputSearch.value">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-md-2">
                <button class="btn btn-primary"
                        type="button"
                        v-bind:disabled="formSearch.submit.disabled"
                        v-html="formSearch.submit.html"
                        v-on:click="buscar"></button>
              </div>
            </form>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/buscarUsuario.js') }}"></script>

    </body>
</html>
