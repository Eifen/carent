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
        <link href="{{ mix('/css/cambiarClave.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="cambiarClave" class="container-fluid">
        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-sm-9 col-md-6 col-lg-4">
            <form id="formLogin">
              <div class="form-group">
                <label for="clave">Contraseña Actual</label>
                <input class="form-control"
                       id="claveActual"
                       v-bind:value="formLogin.clave.value"
                       v-bind:disabled="formLogin.clave.disabled"
                       v-on:keyup="valuesFormLogin"
                       type="password">
                <div class="mensaje"></div>
              </div>
              <div class="form-group">
                <label for="clave">Nueva Contraseña</label>
                <input class="form-control"
                       id="nuevaClave"
                       v-bind:value="formLogin.clave.value"
                       v-bind:disabled="formLogin.clave.disabled"
                       v-on:keyup="valuesFormLogin"
                       type="password">
                <div class="mensaje"></div>
              </div>
              <div class="form-group">
                <label for="clave">Repite la Nueva Contraseña</label>
                <input class="form-control"
                       id="repiteClave"
                       v-bind:value="formLogin.clave.value"
                       v-bind:disabled="formLogin.clave.disabled"
                       v-on:keyup="valuesFormLogin"
                       type="password">
                <div class="mensaje"></div>
              </div>
              <div>
                <button class="btn"
                        type="button"
                        v-on:click="login"
                        v-bind:disabled="submitLogin.disabled"
                        v-html="submitLogin.content"
                        v-if="submitLogin.show"></button>
              </div>
              <div v-bind:class="alertLogin.class" role="alert" v-if="alertLogin.show" v-html="alertLogin.message"></div>
            </form>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/fontawesome-free-5.12.0.js') }}"></script>
      <script src="{{ mix('/js/cambiarClave.js') }}"></script>

    </body>
</html>
