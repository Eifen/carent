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
        <link href="{{ mix('/css/modificarProyecto.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <div id="modificarProyecto" class="container-fluid" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <div class="row align-items-center justify-content-center wrapper-forms" v-cloak>
          <div class="col-12 col-sm-11 col-md-9 col-lg-8 wrapper-back-btn">
            <div class="row justify-content-center">
              <div class="col-12 col-md-6 col-lg-4">
                <a class="btn atras"
                   href="{{ url()->previous() }}"
                   type="button">Regresar</a>
              </div>
            </div>
          </div>

          <div class="col-12 col-sm-11 col-md-9 col-lg-8" v-if="form.mostrar">
            <h3>Estas Modificando un Proyecto</h3>
            <form class="row">
              <div class="col-12 wrapper-required-legend">
                <b>Campos Obligatorios (<span class="campo-obligatorio">*</span>)</b>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="descripcion">Descripción <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="descripcionHelp"
                       class="form-control text-uppercase"
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
              <b-form-group
                :invalid-feedback="form.camposAtributos.fechaContratacion.invalidFeedback"
                class="col-12 col-sm-6"
                label="Fecha de Contratación:"
                label-for="fechaContratacion"
                id="group-fechaContratacion">
                <b-form-datepicker
                  @input="limpiarMensajeError('fechaContratacion')"
                  :date-format-options="{ year: 'numeric', month: '2-digit', day: '2-digit' }"
                  :disabled="form.camposAtributos.fechaContratacion.disabled"
                  :max="form.camposAtributos.fechaContratacion.max"
                  :state="form.camposAtributos.fechaContratacion.state"
                  id="fechaContratacion"
                  label-help="Use las teclas del cursor para navegar por las fechas del calendario"
                  label-no-date-selected="Ninguna fecha seleccionada"
                  locale="es-ES"
                  placeholder="Seleccione una fecha"
                  ref="fechaContratacion"
                  v-model="$v.form.campos.fechaContratacion.$model"></b-form-datepicker>
              </b-form-group>
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
              <div class="form-group col-12 col-sm-6">
                <label for="horas">Monto en <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="montoEnHelp"
                        class="form-control"
                        id="montoEn"
                        data-validar="true"
                        v-bind:disabled="form.montoEn.disabled"
                        v-model="form.montoEn.value">
                  <option value="" disabled selected>Seleccione...</option>
                  <option v-bind:value="moneda.id" v-for="moneda in comboMonedas" :simbolo="moneda.simbolo">@{{ moneda.moneda }}</option>
                </select>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="horas">Monto <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="montoHelp"
                       class="form-control"
                       data-validar="true"
                       id="monto"
                       v-bind:disabled="form.monto.disabled"
                       v-model="form.monto.value"
                       type="text">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <!--Selecionador multiple a ir escogiendo una division se habilita un campo para introducir las horas-->
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
                             v-model="form.divisiones.value">
                   <template slot="selection"
                             slot-scope="{ values, search, isOpen }">
                     <span class="multiselect__single" v-if="values.length &amp;&amp; !isOpen">@{{ values.length }} Seleccionadas</span>
                   </template>
                </multiselect>
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12 col-sm-6">
                <label for="horas">Horas Contratadas <span class="campo-obligatorio">*</span></label>
                <input aria-describedby="horasHelp"
                       class="form-control"
                       data-validar="true"
                       id="horas"
                       v-bind:disabled="form.horas.disabled"
                       v-model="form.horas.value"
                       type="text">
                <div class="mensaje"></div>
              </div>
              <div class="form-group col-12" v-if="form.horas.asignar">
                <h6 class="titulo-indicar-horas">Indica la cantidad de horas por división</h6>
              </div>
              <div class="form-group col-12" v-for="division in form.divisiones.value">
                <div class="row">
                  <div class="col-6">
                    <label>División</label>
                    <input type="text" readonly class="form-control-plaintext division" :value="division.descripcion+`:`">
                  </div>
                  <div class="col-6">
                    <label>Horas</label>
                    <input @keypress="formatoHoraAsignada"
                           @keyup="horasTotales"
                           :ref="'asignar-'+division.id"
                           class="form-control hora-asignada"
                           type="text">
                  </div>
                </div>
              </div>
            </form>

            <div class="row justify-content-center wrapper-subtmit">
              <div class="col-12 col-md-6 col-lg-4">
                <a class="btn atras"
                   href="{{ url()->previous() }}"
                   type="button">Regresar</a>
              </div>
              <div class="col-12 col-md-6 col-lg-4">
                <!--Al hacer clic se invoca el metodo actualizar de modificarProyecto.js y envia los valores de las variables para su modificacion-->
                <button class="btn subtmit"
                        type="button"
                        v-on:click="actualizar"
                        v-bind:disabled="submitActualizar.disabled"
                        v-html="submitActualizar.content"
                        v-if="submitActualizar.show"></button>
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
      <script src="{{ mix('/js/modificarProyecto.js') }}"></script>
    </body>
</html>
