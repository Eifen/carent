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
        <link href="{{ mix('/css/cambiarClave.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="cambiarClave" class="container-fluid">

        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-sm-9 col-md-6 col-lg-4">
            <h3>Estas cambiando tu contraseña</h3>
            <form>
              <div class="form-group">
                <label for="clave">Contraseña Actual</label>
                <input class="form-control"
                       ref="claveActual"
                       v-bind:disabled="form.claveActual.disabled"
                       v-bind:type="form.claveActual.type"
                       v-on:keyup="limpiarMensajeError('claveActual',$event)">
                <div class="ver-clave" v-on:click="verClave" data-input="claveActual">
                  <i v-bind:class="claseVerClaveIcon.claveActual"></i>
                </div>
                <div class="mensaje"></div>
              </div>
              <div class="form-group">
                <label for="clave">Nueva Contraseña</label>
                <input class="form-control"
                       data-min="8"
                       data-validar="true"
                       ref="nuevaClave"
                       v-bind:disabled="form.nuevaClave.disabled"
                       v-bind:type="form.nuevaClave.type"
                       v-on:keyup="limpiarMensajeError('nuevaClave',$event)">
                <div class="ver-clave" v-on:click="verClave" data-input="nuevaClave">
                  <i v-bind:class="claseVerClaveIcon.nuevaClave"></i>
                </div>
                <div class="mensaje"></div>
              </div>
              <div class="form-group">
                <label for="clave">Repite la Nueva Contraseña</label>
                <input class="form-control"
                       data-equal="nuevaClave"
                       data-validar="true"
                       ref="repetirNuevaClave"
                       v-bind:disabled="form.repetirNuevaClave.disabled"
                       v-bind:type="form.repetirNuevaClave.type"
                       v-on:keyup="limpiarMensajeError('repetirNuevaClave',$event)">
                <div class="ver-clave" v-on:click="verClave" data-input="repetirNuevaClave">
                  <i v-bind:class="claseVerClaveIcon.repetirNuevaClave"></i>
                </div>
                <div class="mensaje"></div>
              </div>
              <div>
                <button class="btn"
                        type="button"
                        v-on:click="cambiarContrasena"
                        v-bind:disabled="submit.disabled"
                        v-html="submit.content"
                        v-if="submit.show"></button>
              </div>
              <div v-bind:class="alert.class" role="alert" v-if="alert.show" v-html="alert.message"></div>
            </form>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/cambiarClave.js') }}"></script>

    </body>
</html>
