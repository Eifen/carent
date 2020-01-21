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
        <link href="{{ mix('/css/login.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="login" class="container-fluid" v-on:keypress="keyboard">
        <div class="row align-items-center justify-content-center">
          <div class="col-12 col-sm-9 col-md-6 col-lg-4">
            <form id="formLogin">
              <div class="logo">
                <img src="/images/logo-carent.png">
              </div>
              <div class="form-group">
                <label for="codigoUsuario">Código de usuario</label>
                <input aria-describedby="codigoUsuarioHelp"
                       class="form-control codigoUsuario"
                       data-validar="true"
                       data-only-number="true"
                       ref="codigoUsuario"
                       type="text"
                       v-bind:disabled="formLogin.codigoUsuario.disabled"
                       v-model="formLogin.codigoUsuario.value"
                       v-on:keyup="limpiarMensajeError">
                <small id="codigoUsuarioHelp" class="form-text text-muted">Ejemplo: 2209</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group">
                <label for="clave">Contraseña</label>
                <input class="form-control"
                       ref="clave"
                       v-bind:disabled="formLogin.clave.disabled"
                       v-bind:type="formLogin.clave.type"
                       v-model="formLogin.clave.value"
                       v-on:keyup="limpiarMensajeError">
                <div class="ver-clave" v-on:click="verClave">
                  <i v-bind:class="claseVerClaveIcon"></i>
                </div>
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
              <div class="wrapper-recovery-pass">
                <a class="recuperarClave" v-on:click="modalRecuperarClave" v-if="linkRecoveryPass">Olvidé mi contraseña</a>
              </div>
              <div v-bind:class="alertLogin.class" role="alert" v-if="alertLogin.show" v-html="alertLogin.message"></div>
            </form>
          </div>
        </div>

        <div id="modal-recuperar-clave" class="modal fade" tabindex="-1" role="dialog">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                Para recuperar su clave solo debe indicar su código de usuario y le llegará a su correo.
                <form id="formRecoveryPass">
                  <div class="form-group">
                    <input class="form-control codigoRecuperacion"
                           data-validar="true"
                           data-only-number="true"
                           ref="codigoRecuperacion" type="text"
                           v-bind:disabled="formRecovery.codigoRecuperacion.disabled"
                           v-model="formRecovery.codigoRecuperacion.value"
                           v-on:keyup="limpiarMensajeError">
                    <small id="codigoRecuperacionHelp" class="form-text text-muted">Ejemplo: 2209</small>
                    <div class="mensaje"></div>
                  </div>
                </form>
                <div v-bind:class="alertRecoveryPass.class" role="alert" v-if="alertRecoveryPass.show" v-html="alertRecoveryPass.message"></div>
              </div>
              <div class="modal-footer">
                <button class="btn"
                        type="button"
                        v-bind:disabled="submitModalRecoveryPass.disabled"
                        v-if="submitModalRecoveryPass.show"
                        v-html="submitModalRecoveryPass.content"
                        v-on:click="recuperarClave"></button>
              </div>
            </div>
          </div>
        </div>

      </div>

      <script src="{{ mix('/js/login.js') }}"></script>

    </body>
</html>
