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
                    :invalid-feedback="formLogin.campos.codigoUsuario.invalidFeedback"
                    description="Ejemplo: 2209"
                    label-for="codigoUsuario"
                    id="group-codigoUsuario">
                    <b-input-group>
                      <b-input-group-prepend is-text>
                        <b-icon icon="person-fill"></b-icon>
                      </b-input-group-prepend>
                      <b-form-input
                        @input="limpiarMensajeError(formLogin.campos.codigoUsuario)"
                        :disabled="formLogin.campos.codigoUsuario.disabled"
                        :state="formLogin.campos.codigoUsuario.state"
                        autocomplete="off"
                        id="codigoUsuario"
                        placeholder="Código de usuario"
                        ref="codigoUsuario"
                        type="text"
                        v-model="$v.formLogin.campos.codigoUsuario.value.$model"></b-form-input>
                    </b-input-group>
                  </b-form-group>
                  <b-form-group
                    :invalid-feedback="formLogin.campos.clave.invalidFeedback"
                    label-for="clave"
                    id="group-clave">
                    <b-input-group>
                      <b-input-group-prepend is-text>
                        <b-icon icon="lock-fill"></b-icon>
                      </b-input-group-prepend>
                      <b-form-input
                        @input="limpiarMensajeError(formLogin.campos.clave)"
                        :disabled="formLogin.campos.clave.disabled"
                        :state="formLogin.campos.clave.state"
                        :type="formLogin.campos.clave.type"
                        autocomplete="off"
                        id="clave"
                        placeholder="Contraseña"
                        ref="clave"
                        v-model="$v.formLogin.campos.clave.value.$model">
                      </b-form-input>
                      <b-input-group-append is-text>
                        <b-icon @click="verClave" :icon="formLogin.campos.clave.iconShowPass.icon"></b-icon>
                      </b-input-group-append>
                    </b-input-group>
                  </b-form-group>
                  <div>
                    <b-button
                      @click="login('{{ Session::get('encrypt-key') }}', '{{ Session::get('encrypt-iv') }}')"
                      :disabled="formLogin.botones.submit.disabled"
                      block
                      v-html="formLogin.botones.submit.html"
                      variant="outline-success"></b-button>
                  </div>
                  <alert :contador="formLogin.alert.contador"
                         :icono-cerrar="formLogin.alert.iconCerrar"
                         :mensaje="formLogin.alert.mensaje"
                         :mostrar="formLogin.alert.mostrar"
                         :ocultar-seg="formLogin.alert.ocultarSeg"
                         :variante="formLogin.alert.variante">
                  </alert>
                  <div class="wrapper-recovery-pass">
                    <b-button
                      :disabled="formLogin.botones.recoveryPass.disabled"
                      block
                      v-b-modal.modal-recuperar-clave
                      v-html="formLogin.botones.recoveryPass.html"
                      variant="link"></b-button>
                  </div>
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

        <b-modal
          centered
          id="modal-recuperar-clave"
          ref="modal-recuperar-clave"
          v-cloak>
            Para recuperar su clave solo debe indicar su código de usuario y le llegará a su correo.
            <b-form>
              <b-form-group
                :invalid-feedback="formRecovery.campos.codigoUsuario.invalidFeedback"
                description="Ejemplo: 2209"
                label-for="codigoUsuarioR"
                id="group-codigoUsuarioR">
                <b-input-group>
                  <b-input-group-prepend is-text>
                    <b-icon icon="person-fill"></b-icon>
                  </b-input-group-prepend>
                  <b-form-input
                    @input="limpiarMensajeError(formRecovery.campos.codigoUsuario)"
                    :disabled="formRecovery.campos.codigoUsuario.disabled"
                    :state="formRecovery.campos.codigoUsuario.state"
                    autocomplete="off"
                    id="codigoUsuarioR"
                    placeholder="Código de usuario"
                    ref="codigoUsuarioR"
                    type="text"
                    v-model="$v.formRecovery.campos.codigoUsuario.value.$model"></b-form-input>
                </b-input-group>
              </b-form-group>
            </b-form>
            <template v-slot:modal-footer>
              <alert :contador="formRecovery.alert.contador"
                     :icono-cerrar="formRecovery.alert.iconCerrar"
                     :mensaje="formRecovery.alert.mensaje"
                     :mostrar="formRecovery.alert.mostrar"
                     :ocultar-seg="formRecovery.alert.ocultarSeg"
                     :variante="formRecovery.alert.variante">
              </alert>
              <b-button
                @click="recuperarClave"
                :disabled="formRecovery.botones.submit.disabled"
                block
                v-html="formRecovery.botones.submit.html"
                v-if="formRecovery.botones.submit.show"
                variant="outline-success"></b-button>
            </template>
        </b-modal>

      </b-container>

      <script src="{{ mix('/js/login.js') }}"></script>

    </body>
</html>
