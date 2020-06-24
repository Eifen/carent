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
        <link href="{{ mix('/css/nuevoUsuario.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="nuevoUsuario" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <h3>Estas creando a un nuevo usuario</h3>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="nombre1">Primer Nombre <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="nombre1Help"
                       class="form-control text-lowercase"
                       data-min="3"
                       data-name-lastname="true"
                       data-validar="true"
                       id="nombre1"
                       v-bind:disabled="form.nombre1.disabled"
                       v-model="form.nombre1.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="nombre1Help" class="form-text text-muted">Ejemplo: Pedro</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="nombre2">Segundo Nombre</label>
                <input aria-describedby="nombre2Help"
                       class="form-control text-lowercase"
                       data-validar="true"
                       data-name-lastname="true"
                       id="nombre2"
                       v-bind:disabled="form.nombre2.disabled"
                       v-model="form.nombre2.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="nombre2Help" class="form-text text-muted">Ejemplo: Emilio</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="apellido1">Primer Apellido <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="apellido1Help"
                       class="form-control text-lowercase"
                       data-min="3"
                       data-name-lastname="true"
                       data-validar="true"
                       id="apellido1"
                       v-bind:disabled="form.apellido1.disabled"
                       v-model="form.apellido1.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="apellido1Help" class="form-text text-muted">Ejemplo: Silva</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="apellido2">Segundo Apellido</label>
                <input aria-describedby="apellido2Help"
                       class="form-control text-lowercase"
                       data-validar="true"
                       data-name-lastname="true"
                       id="apellido2"
                       v-bind:disabled="form.apellido2.disabled"
                       v-model="form.apellido2.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="apellido2Help" class="form-text text-muted">Ejemplo: Ruíz</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="cedula">Cédula de Identidad <span class="campo-obligatorio">*</span></label>
                <input class="form-control"
                       data-formated-number="true"
                       data-only-number="true"
                       data-validar="true"
                       id="cedula"
                       v-bind:disabled="form.cedula.disabled"
                       v-model="form.cedula.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="cedulaHelp" class="form-text text-muted">Ejemplo: 17.471.899</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="fechaNacimiento">Fecha de Nacimiento</label>
                <input aria-describedby="fechaNacimientoHelp"
                       class="form-control"
                       id="fechaNacimiento"
                       v-bind:disabled="form.fechaNacimiento.disabled"
                       v-mask="'##/##/####'"
                       v-model="form.fechaNacimiento.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="fechaNacimientoHelp" class="form-text text-muted">Ejemplo: 20/02/1985</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="codigoUsuario">Código de usuario <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="codigoUsuarioHelp"
                       class="form-control"
                       data-validar="true"
                       data-only-number="true"
                       id="codigoUsuario"
                       v-bind:disabled="form.codigoUsuario.disabled"
                       v-model="form.codigoUsuario.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="codigoUsuarioHelp" class="form-text text-muted">Ejemplo: 2209</small>
                <div class="mensaje"></div>
              </div>
            </form>

            <h5>Datos de contacto</h5>
            <form class="row">
              <div class="form-group col-12 col-sm-6">
                <label for="correo_principal">Correó Principal <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="correoPrincipalHelp"
                       class="form-control text-lowercase"
                       data-validar="true"
                       id="correoPrincipal"
                       v-bind:disabled="form.correoPrincipal.disabled"
                       v-model="form.correoPrincipal.value"
                       v-on:keyup="valuesForm"
                       type="email">
                <small id="correoPrincipalHelp" class="form-text text-muted">Ejemplo: correo@dominio.com</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="correo_principal">Correó Secundario</label>
                <input aria-describedby="correoSecundariolHelp"
                       class="form-control text-lowercase"
                       id="correoSecundario"
                       v-bind:data-validar="form.correoSecundario.validar"
                       v-bind:disabled="form.correoSecundario.disabled"
                       v-model="form.correoSecundario.value"
                       v-on:keyup="campoOpcionalARequerido"
                       type="email">
                <small id="correoSecundarioHelp" class="form-text text-muted">Ejemplo: correo_2@dominio_2.com</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="correo_principal">Nº de Teléfono Principal</label>
                <input aria-describedby="telefono1Help"
                       class="form-control"
                       id="telefono1"
                       v-bind:disabled="form.telefono1.disabled"
                       v-mask="'(####) - ### ####'"
                       v-model="form.telefono1.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="telefono1Help" class="form-text text-muted">Ejemplo: 0424-1234567</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="correo_principal">Nº de Teléfono Secundario</label>
                <input aria-describedby="telefono2Help"
                       class="form-control"
                       id="telefono2"
                       v-bind:disabled="form.telefono2.disabled"
                       v-mask="'(####) - ### ####'"
                       v-model="form.telefono2.value"
                       v-on:keyup="valuesForm"
                       type="text">
                <small id="telefono2Help" class="form-text text-muted">Ejemplo: 0424-7654321</small>
                <div class="mensaje"></div>
              </div>
            </form>

            <h5>Datos para el empleado</h5>
            <form class="row">
              <div class="form-group form-check col-12">
                <input class="form-check-input"
                       id="empleado"
                       type="checkbox"
                       v-model="form.empleado.checked"
                       v-on:change="esEmpleado">
                <label class="form-check-label label-warning" for="empleado">Este usuario es un empleado de <b>CROWE</b>?</label>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estado">Estado <span v-if="form.empleado.checked" class="campo-obligatorio">*</span></label>
                <select aria-describedby="estadoHelp"
                        class="form-control"
                        id="estado"
                        v-bind:data-validar="form.estado.validar"
                        v-bind:disabled="form.estado.disabled"
                        v-model="form.estado.value"
                        v-on:change="municipios"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="estado.id" v-for="estado in comboEstados">@{{ estado.estado }}</option>
                </select>
                <small id="estadoHelp" class="form-text text-muted">Estado de la oficina en donde se desempeña</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estado">Municipio <span v-if="form.empleado.checked" class="campo-obligatorio">*</span></label>
                <select aria-describedby="municipioHelp"
                        class="form-control"
                        id="municipio"
                        v-bind:data-validar="form.municipio.validar"
                        v-bind:disabled="form.municipio.disabled"
                        v-model="form.municipio.value"
                        v-on:change="parroquias"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="municipio.id" v-for="municipio in comboMunicipios">@{{ municipio.municipio }}</option>
                </select>
                <small id="estadoHelp" class="form-text text-muted" v-html="form.municipio.help"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estado">Parroquia <span v-if="form.empleado.checked" class="campo-obligatorio">*</span></label>
                <select aria-describedby="parroquiaHelp"
                        class="form-control"
                        id="parroquia"
                        v-bind:data-validar="form.parroquia.validar"
                        v-bind:disabled="form.parroquia.disabled"
                        v-model="form.parroquia.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="parroquia.id" v-for="parroquia in comboParroquias">@{{ parroquia.parroquia }}</option>
                </select>
                <small id="estadoHelp" class="form-text text-muted" v-html="form.parroquia.help"></small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estado">División <span v-if="form.empleado.checked" class="campo-obligatorio">*</span></label>
                <select aria-describedby="divisionHelp"
                        class="form-control"
                        id="division"
                        v-bind:data-validar="form.division.validar"
                        v-bind:disabled="form.division.disabled"
                        v-model="form.division.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="division.id" v-for="division in comboDivisiones">@{{ division.descripcion }}</option>
                </select>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estado">Cargo <span v-if="form.empleado.checked" class="campo-obligatorio">*</span></label>
                <select aria-describedby="cargoHelp"
                        class="form-control"
                        id="cargo"
                        v-bind:data-validar="form.cargo.validar"
                        v-bind:disabled="form.cargo.disabled"
                        v-model="form.cargo.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="cargo.id" v-for="cargo in comboCargos">@{{ cargo.descripcion }}</option>
                </select>
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
                        v-on:click="refreshView">Agregar un nuevo usuario</button>
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

      <script src="{{ mix('/js/nuevoUsuario.js') }}"></script>

    </body>
</html>
