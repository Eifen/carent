<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>.: CARENT :.</title>
        <link rel="shortcut icon" type="image/png" href="/images/favicon.png"/>
        <link href="{{ mix('/css/fontawesome-free-5.12.0.css') }}" rel="stylesheet" type="text/css">
        <link href="{{ mix('/css/cambiarClave.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <script>

        var mostrarModalCambioClave = {{ Session::get('cambiar_clave') }}
        mostrarModalCambioClave = (mostrarModalCambioClave) ? 1 : 0;

      </script>

      <b-container fluid id="app">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <b-row align-h="center" align-v="center" v-cloak>
          <b-col cols="12" sm="9" md="6" lg="4">

            <h4>Estas cambiando tu contraseña</h4>
            <b-form>
              <b-form-group
                :invalid-feedback="formCambiarClave.campos.claveActual.invalidFeedback"
                label-for="claveActual"
                id="group-claveActual">
                <b-input-group>
                  <b-form-input
                    @input="limpiarMensajeError(formCambiarClave.campos.claveActual)"
                    :disabled="formCambiarClave.campos.claveActual.disabled"
                    :state="formCambiarClave.campos.claveActual.state"
                    :type="formCambiarClave.campos.claveActual.type"
                    autocomplete="off"
                    id="claveActual"
                    placeholder="Contraseña Actual"
                    ref="claveActual"
                    v-model="$v.formCambiarClave.campos.claveActual.value.$model">
                  </b-form-input>
                  <b-input-group-append is-text>
                    <b-icon @click="verClave(formCambiarClave.campos.claveActual)" :icon="formCambiarClave.campos.claveActual.iconShowPass.icon"></b-icon>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <b-form-group
                :invalid-feedback="formCambiarClave.campos.nuevaClave.invalidFeedback"
                label-for="nuevaClave"
                id="group-nuevaClave">
                <b-input-group>
                  <b-form-input
                    @input="limpiarMensajeError(formCambiarClave.campos.nuevaClave)"
                    :disabled="formCambiarClave.campos.nuevaClave.disabled"
                    :state="formCambiarClave.campos.nuevaClave.state"
                    :type="formCambiarClave.campos.nuevaClave.type"
                    autocomplete="off"
                    id="nuevaClave"
                    placeholder="Nueva Contraseña"
                    ref="nuevaClave"
                    v-model="$v.formCambiarClave.campos.nuevaClave.value.$model">
                  </b-form-input>
                  <b-input-group-append is-text>
                    <b-icon @click="verClave(formCambiarClave.campos.nuevaClave)" :icon="formCambiarClave.campos.nuevaClave.iconShowPass.icon"></b-icon>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <b-form-group
                :invalid-feedback="formCambiarClave.campos.repetirNuevaClave.invalidFeedback"
                label-for="repetirNuevaClave"
                id="group-repetirNuevaClave">
                <b-input-group>
                  <b-form-input
                    @input="limpiarMensajeError(formCambiarClave.campos.repetirNuevaClave)"
                    :disabled="formCambiarClave.campos.repetirNuevaClave.disabled"
                    :state="formCambiarClave.campos.repetirNuevaClave.state"
                    :type="formCambiarClave.campos.repetirNuevaClave.type"
                    autocomplete="off"
                    data-equal="Nueva Contraseña"
                    id="repetirNuevaClave"
                    placeholder="Repite la Nueva Contraseña"
                    ref="repetirNuevaClave"
                    v-model="$v.formCambiarClave.campos.repetirNuevaClave.value.$model">
                  </b-form-input>
                  <b-input-group-append is-text>
                    <b-icon @click="verClave(formCambiarClave.campos.repetirNuevaClave)" :icon="formCambiarClave.campos.repetirNuevaClave.iconShowPass.icon"></b-icon>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <div>
                <b-button
                  @click="cambiarContrasena('{{ Session::get('encrypt-key') }}', '{{ Session::get('encrypt-iv') }}')"
                  :disabled="formCambiarClave.botones.submit.disabled"
                  block
                  v-html="formCambiarClave.botones.submit.html"
                  variant="outline-success"></b-button>
              </div>
              <alert :contador="formCambiarClave.alert.contador"
                     :icono-cerrar="formCambiarClave.alert.iconCerrar"
                     :mensaje="formCambiarClave.alert.mensaje"
                     :mostrar="formCambiarClave.alert.mostrar"
                     :ocultar-seg="formCambiarClave.alert.ocultarSeg"
                     :variante="formCambiarClave.alert.variante">
              </alert>
            </b-form>

          </b-col>
        </b-row>

        <b-modal
          :hide-header="true"
          :no-close-on-backdrop="true"
          :no-close-on-esc="true"
          centered
          id="mostrarModalCambioClave"
          ref="mostrarModalCambioClave"
          v-cloak>
            <p class="text-center">El sistema ha detectado que debe modificar su contraseña</p>
            <template v-slot:modal-footer>
              <b-button
                @click="$refs['mostrarModalCambioClave'].hide()"
                block
                variant="primary">Ok</b-button>
            </template>
        </b-modal>

      </b-container>

      <script src="{{ mix('/js/cambiarClave.js') }}"></script>

    </body>
</html>
