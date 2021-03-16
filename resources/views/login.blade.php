<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="{{ env('META_ROBOT') }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/login.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>

        <b-row align-h="center" align-v="center" v-cloak>
          <b-col cols="12" sm="9" md="6" lg="4">
            <b-row>
              <b-col cols="12">
                <b-form>
                  <div class="logo">
                    <b-img src="/images/logo-carent.png" alt="Logo CARENT" center></b-img>
                  </div>
                  <b-form-group
                    :invalid-feedback="formLogin.codigoUsuario.invalidFeedback"
                    description="Ejemplo: 2209"
                    label-for="descripcion"
                    id="group-codigoUsuario">
                    <b-input-group>
                      <b-input-group-prepend is-text>
                        <b-icon icon="person-fill"></b-icon>
                      </b-input-group-prepend>
                      <b-form-input
                        @input="limpiarMensajeError(formLogin.codigoUsuario)"
                        :disabled="formLogin.codigoUsuario.disabled"
                        :state="formLogin.codigoUsuario.state"
                        autocomplete="off"
                        id="codigoUsuario"
                        placeholder="Código de usuario"
                        ref="codigoUsuario"
                        type="text"></b-form-input>
                    </b-input-group>
                  </b-form-group>
                  <b-form-group
                    :invalid-feedback="formLogin.clave.invalidFeedback"
                    label-for="clave"
                    id="group-clave">
                    <b-input-group>
                      <b-input-group-prepend is-text>
                        <b-icon icon="lock-fill"></b-icon>
                      </b-input-group-prepend>
                      <b-form-input
                        @input="limpiarMensajeError(formLogin.clave)"
                        :disabled="formLogin.clave.disabled"
                        :state="formLogin.clave.state"
                        :type="formLogin.clave.type"
                        autocomplete="off"
                        id="clave"
                        placeholder="Contraseña"
                        ref="clave">
                      </b-form-input>
                      <b-input-group-append is-text>
                        <b-icon @click="verClave" :icon="formLogin.clave.iconShowPass.icon"></b-icon>
                      </b-input-group-append>
                    </b-input-group>
                  </b-form-group>
                  <div>
                    <b-button
                      @click="login"
                      :disabled="formLogin.submit.disabled"
                      block
                      v-html="formLogin.submit.html"
                      variant="outline-success"></b-button>
                  </div>
                  <div class="wrapper-recovery-pass">
                    <b-button @click="modalRecuperarClave" block v-if="linkRecoveryPass" variant="link">Olvidé mi contraseña</b-button>
                  </div>
                  <div v-bind:class="alertLogin.class" role="alert" v-if="alertLogin.show" v-html="alertLogin.message"></div>
                </b-form>
              </b-col>
            </b-row>
            <b-row>
              <b-col cols="12" class="text-center wrapper-social-icons">
                <b-link href="https://www.instagram.com/crowe.ve" target="_blank" v-b-tooltip.hover title="Instagram">
                  <i class="fab fa-instagram"></i>
                </b-link>
                <b-link href="https://www.facebook.com/CroweVzla" target="_blank" v-b-tooltip.hover title="Facebook">
                  <i class="fab fa-facebook-f"></i>
                </b-link>
                <b-link href="https://www.youtube.com/channel/UCx7ekjvHFTuGkenLjap-oFQ" target="_blank" v-b-tooltip.hover title="Youtube">
                  <i class="fab fa-youtube"></i>
                </b-link>
                <b-link href="https://twitter.com/crowe_vzla" target="_blank" v-b-tooltip.hover title="Twitter">
                  <i class="fab fa-twitter"></i>
                </b-link>
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <div id="modal-recuperar-clave" class="modal fade" tabindex="-1" role="dialog" v-cloak>
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

      </b-container>

      <script src="{{ mix('/js/login.js') }}"></script>

    </body>
</html>
