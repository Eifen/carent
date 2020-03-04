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
        <link href="{{ mix('/css/nuevoProyecto.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="app" class="container-fluid" v-on:keypress="keyboard">
        <menu-principal></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms">
          <div class="col-12 col-sm-11 col-md-9 col-lg-8" v-if="form.mostrar">
            <h3>Estas creando un nuevo Proyecto</h3>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="descripcion">Descripción <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="descripcionHelp"
                       class="form-control"
                       data-min="3"
                       data-validar="true"
                       id="descripcion"
                       maxlength="250"
                       v-bind:disabled="form.descripcion.disabled"
                       v-model.trim="form.descripcion.value"
                       type="text">
                <small id="descripcionHelp" class="form-text text-muted">Ejemplo: Auditoria Externa</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="cliente">Cliente</label>
                <select aria-describedby="clienteHelp"
                        class="form-control"
                        id="cliente"
                        data-validar="true"
                        v-bind:disabled="form.cliente.disabled"
                        v-model="form.cliente.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="cliente.id" v-for="cliente in comboClientes">@{{ cliente.razon_social }}</option>
                </select>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="apellido1">Horas Contratadas <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="horasHelp"
                       class="form-control"
                       data-validar="true"
                       id="horas"
                       v-bind:disabled="form.horas.disabled"
                       v-model="form.horas.value"
                       type="text">
                <small id="horasHelp" class="form-text text-muted">Ejemplo: 90</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="fechaContratacion">Fecha de Contratación</label>
                <input aria-describedby="fechaContratacionHelp"
                       class="form-control text-lowercase"
                       data-date="true"
                       data-validar="true"
                       id="fechaContratacion"
                       v-bind:disabled="form.fechaContratacion.disabled"
                       v-mask="'##/##/####'"
                       v-model="form.fechaContratacion.value"
                       type="text">
                <small id="fechaContratacionHelp" class="form-text text-muted">Formato: 00/00/0000</small>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="divisiones">Divisiones <span class="campo-obligatorio">*</span></label>
                <multiselect @Open="limpiarMensajeErrorMultiselect"
                             :clear-on-select="false"
                             :close-on-select="false"
                             :disabled="form.divisiones.disabled"
                             :multiple="true"
                             :options="comboDivisiones"
                             :show-labels="false"
                             clase="form-control"
                             data-validar="true"
                             id="divisiones"
                             label="descripcion"
                             placeholder="Seleccione..."
                             track-by="descripcion"
                             v-model="form.divisiones.value"></multiselect>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="estatus">Estatus <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="estatusHelp"
                        class="form-control"
                        id="estatus"
                        data-validar="true"
                        v-bind:disabled="form.estatus.disabled"
                        v-model="form.estatus.value"
                        v-on:click="limpiarMensajeError">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="estatus.id" v-for="estatus in comboEstatus">@{{ estatus.descripcion }}</option>
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
                        v-on:click="refreshView">Crear un nuevo proyecto</button>
              </div>
            </div>

          </div>

          <div class="col-12 col-sm-11 col-md-9 col-lg-8">
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>

        </div>

      </div>

      <script src="{{ mix('/js/nuevoProyecto.js') }}"></script>

    </body>
</html>
