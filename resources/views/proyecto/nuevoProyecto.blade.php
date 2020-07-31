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
        <link href="{{ mix('/css/nuevoProyecto.css') }}" rel="stylesheet" type="text/css">

    </head>
    <body>

      <b-container fluid id="app" v-on:keypress="keyboard">

        <loading :loading="loading" v-show="loading"></loading>
        <menu-principal v-cloak></menu-principal>

        <b-row align-h="center" align-v="center" class="wrapper-forms" v-cloak v-if="form.mostrar">
          <b-col cols="12" sm="11" md="9" lg="8">
            <h4>Estas creando un nuevo Proyecto</h4>
            <b-form class="row">
              <b-form-group
                :invalid-feedback="form.camposAtributos.descripcion.invalidFeedback"
                class="col-12 col-sm-6"
                description="Ejemplo: Proyecto 1"
                label="Descripcion"
                label-for="descripcion"
                id="group-descripcion">
                <b-form-input
                  :disabled="form.camposAtributos.descripcion.disabled"
                  :state="form.camposAtributos.descripcion.state"
                  autocomplete="off"
                  id="descripcion"
                  ref="descripcion"
                  size="sm"
                  type="text"
                  v-on:keyup="limpiarMensajeError"
                  v-model="$v.form.campos.descripcion.$model"></b-form-input>
              </b-form-group>
              <b-form-group
                :invalid-feedback="form.camposAtributos.cliente.invalidFeedback"
                class="col-12 col-sm-6"
                label="Cliente"
                label-for="cliente"
                id="group-cliente">
                <b-form-input
                  @blur="valorBlur('cliente')"
                  @input="buscarCliente"
                  :disabled="form.camposAtributos.cliente.disabled"
                  :state="form.camposAtributos.cliente.state"
                  autocomplete="off"
                  id="cliente"
                  ref="cliente"
                  size="sm"
                  type="text"
                  v-on:focus="valorFocus('cliente')"
                  v-model.trim="form.camposAtributos.cliente.valor"></b-form-input>
                <b-dropdown id="lista-cliente" variant="link" no-caret block ref="ref-lista-cliente">
                  <b-dropdown-item-button
                    :key="key"
                    v-for="(cliente, key) in form.camposAtributos.cliente.listaDropdown.listado"
                    v-if="form.camposAtributos.cliente.listaDropdown.listado.length > 0"
                    v-on:click="elegirFuncionario(cliente)"> @{{ cliente.nombre }} </b-dropdown-item-button>
                  <b-dropdown-item-button
                    v-if="form.camposAtributos.cliente.listaDropdown.noResultado"
                    v-on:click="listadoNoValido('cliente')">No se encontrarón clientes, intente con otro nombre!</b-dropdown-item-button>
                </b-dropdown>
                <b-form-text id="cliente-help" v-html="form.camposAtributos.cliente.help"></b-form-text>
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
                <label for="horas">Monto en <span class="campo-obligatorio">*</span></label>
                <select aria-describedby="montoEnHelp"
                        class="form-control"
                        id="montoEn"
                        data-validar="true"
                        v-bind:disabled="form.montoEn.disabled"
                        v-model="form.montoEn.value"
                        v-on:change="monedaSeleccionada">
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
                <multiselect @input="asignarHoras"
                             @Open="limpiarMensajeErrorMultiselect"
                             :clear-on-select="false"
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
                           :disabled="form.divisiones.disabled"
                           :ref="'asignar-'+division.id"
                           class="form-control hora-asignada"
                           type="text">
                  </div>
                </div>
              </div>
            </b-form>

             <!--Al hacer clic se invoca el metodo crear de nuevoProyecto.js y envia los valores de las variables para su modificacion-->
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

          </b-col>

          <div class="col-12 col-sm-11 col-md-9 col-lg-8" v-cloak>
            <div class="row wrapper-alert">
              <div class="col-12">
                <div v-bind:class="alertForm.class" role="alert" v-if="alertForm.show" v-html="alertForm.message"></div>
              </div>
            </div>
          </div>

        </b-row>

      </b-container>

      <script src="{{ mix('/js/nuevoProyecto.js') }}"></script>

    </body>
</html>
