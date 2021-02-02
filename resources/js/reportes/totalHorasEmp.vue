<template>
  <b-row>

    <b-col cols=12>
      <h5 v-if="formFiltro.mostrar">Filtros de Búsqueda</h5>
      <b-form class="row" v-if="formFiltro.mostrar">
        <b-form-group
          :invalid-feedback="formFiltro.campos.divisiones.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="División"
          label-for="division"
          id="group-division">
          <b-form-select
                  @change="empleadosDivision"
                  :disabled="formFiltro.campos.divisiones.disabled"
                  :state="formFiltro.campos.divisiones.state"
                  class="form-control form-control-sm"
                  id="division"
                  data-validar="true"
                  ref="divisiones"
                  size="sm"
                  v-model="$v.formFiltro.campos.divisiones.value.$model">
            <option :value="null" selected disabled>Seleccione...</option>
            <option v-bind:value="division.id" v-for="division in formFiltro.campos.divisiones.listado">{{ division.descripcion }}</option>
          </b-form-select>
        </b-form-group>
        <b-form-group
          :invalid-feedback="formFiltro.campos.empleados.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="Empleado"
          label-for="empleado"
          id="group-empleado">
          <b-form-select
                  @change="limpiarMensajeError(formFiltro.campos.empleados)"
                  :disabled="formFiltro.campos.empleados.disabled"
                  :state="formFiltro.campos.empleados.state"
                  aria-describedby="empleadoHelp"
                  class="form-control form-control-sm"
                  id="empleado"
                  data-validar="true"
                  ref="empleados"
                  size="sm"
                  v-model="$v.formFiltro.campos.empleados.value.$model">
            <option :value="null" selected disabled>{{ formFiltro.campos.empleados.seleccione }}</option>
            <option v-bind:value="empleado.id" v-for="empleado in formFiltro.campos.empleados.listado">{{ empleado.nombre }}</option>
          </b-form-select>
        </b-form-group>
        <b-form-group
          :invalid-feedback="formFiltro.campos.fechaDesde.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Desde"
          label-for="fechaDesde"
          id="group-fechaDesde">
          <b-form-datepicker
            @input="fechaDesdeFiltro"
            :disabled="formFiltro.campos.fechaDesde.disabled"
            :locale="'es-VE'"
            :max="formFiltro.campos.fechaDesde.maxValue"
            :state="formFiltro.campos.fechaDesde.state"
            locale="es"
            size="sm"
            ref="fechaDesde"
            v-bind="formFiltro.traduccionCalendario.labels['es-VE'] || {}"
            v-model="$v.formFiltro.campos.fechaDesde.value.$model"></b-form-datepicker>
        </b-form-group>
        <b-form-group
          :invalid-feedback="formFiltro.campos.fechaHasta.invalidFeedback"
          class="form-group col-12 col-sm-6 col-md-4"
          label="Fecha Hasta"
          label-for="fechaHasta"
          id="group-fechaHasta">
          <b-form-datepicker
            @input="limpiarMensajeError(formFiltro.campos.fechaHasta)"
            :disabled="formFiltro.campos.fechaHasta.disabled"
            :locale="'es-VE'"
            :max="formFiltro.campos.fechaHasta.maxValue"
            :min="formFiltro.campos.fechaHasta.minValue"
            :state="formFiltro.campos.fechaHasta.state"
            locale="es"
            size="sm"
            ref="fechaHasta"
            v-bind="formFiltro.traduccionCalendario.labels['es-VE'] || {}"
            v-model="$v.formFiltro.campos.fechaHasta.value.$model"></b-form-datepicker>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-2">
          <label>&nbsp;</label>
          <b-button
            :disabled="formFiltro.btn.filtrar.disabled"
            block
            size="sm"
            v-html="formFiltro.btn.filtrar.html"
            v-on:click="buscar"
            variant="primary"></b-button>
        </b-form-group>
        <b-form-group class="col-12 col-sm-6 col-md-2">
          <label>&nbsp;</label>
          <b-button
            :disabled="formFiltro.btn.limpiarFiltro.disabled"
            block
            size="sm"
            v-html="formFiltro.btn.limpiarFiltro.html"
            v-on:click="limpiarFiltro"
            variant="outline-primary"></b-button>
        </b-form-group>
        <b-form-group class="col-12">
          <alert :contador="formFiltro.alert.contador"
                 :icono-cerrar="formFiltro.alert.iconCerrar"
                 :mensaje="formFiltro.alert.mensaje"
                 :mostrar="formFiltro.alert.mostrar"
                 :ocultar-seg="formFiltro.alert.ocultarSeg"
                 :variante="formFiltro.alert.variante">
          </alert>
        </b-form-group>
      </b-form>
    </b-col>

    <b-col cols=12>

    </b-col>

  </b-row>
</template>

<style lang="less">
  @import '../../less/reportes/totalHorasEmp.less';
</style>

<script>

  import Vue from 'vue';
  import axios from 'axios';
  import alert from '../components/alert.vue';
  import JsonExcel from "vue-json-excel";
  import Vuelidate from 'vuelidate';
  import { required, minLength, minValue } from 'vuelidate/lib/validators';
  import zenscroll from 'zenscroll';
  var self;

  Vue.use(Vuelidate);

  export default {
      data() {
        return {
          formFiltro: {
            alert: {
              contador: false,
              iconCerrar: false,
              mensaje: "",
              mostrar: false,
              ocultarSeg: 0,
              variante: ""
            },
            btn: {
              filtrar: {
                disabled: false,
                html: "",
                htmlInit: "Aplicar Filtro",
                htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
              },
              limpiarFiltro: {
                disabled: false,
                html: "",
                htmlInit: "Limpiar Filtro",
                htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
              }
            },
            campos: {
              divisiones: {
                disabled: true,
                invalidFeedback: '',
                listado: [],
                state: null,
                value: null
              },
              empleados: {
                disabled: true,
                invalidFeedback: '',
                listado: [],
                seleccione: "seleccione...",
                state: null,
                value: null
              },
              fechaDesde: {
                disabled: true,
                invalidFeedback: '',
                maxValue: "",
                state: null,
                value: null
              },
              fechaHasta:{
                disabled: true,
                invalidFeedback: '',
                maxValue: "",
                minValue: "",
                state: null,
                value: null
              }
            },
            mostrar : false,
            traduccionCalendario: {
              labels: {
                'es-VE': {
                  weekdayHeaderFormat: 'narrow',
                  labelPrevDecade: 'Década Anterior',
                  labelPrevYear: 'Año Anterior',
                  labelPrevMonth: 'Mes Anterior',
                  labelCurrentMonth: 'Mes Actualي',
                  labelNextMonth: 'Siguiente Mes',
                  labelNextYear: 'Siguiente Año',
                  labelNextDecade: 'Siguiente Década',
                  labelToday: 'Hoy',
                  labelSelected: 'Seleccionado',
                  labelNoDateSelected: 'Ninguna fecha seleccionada',
                  labelCalendar: 'Calendario',
                  labelNav: 'Navegación de calendario',
                  labelHelp: 'Use las teclas del cursor para navegar por las fechas del calendario'
                }
              },
              maxValue: "",
              minValue: "",
              value: null
            }
          }
        };
      },
      components: {
        alert,
        "downloadExcel": JsonExcel
      },
      validations: {
        formFiltro:{
          campos:{
            divisiones: {
              value: {
                required
              }
            },
            empleados: {
              value: {
                required
              }
            },
            fechaDesde: {
              value: {
                required
              }
            },
            fechaHasta: {
              value: {
                required
              }
            }
          }
        }
      },
      beforeCreate: function(){

        self = this;

        axios.get('/dataRepTotalHorasEmp')
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.formFiltro.campos.divisiones.listado = response.data.divisiones;
            self.formFiltro.campos.divisiones.disabled = false;
            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
            self.formFiltro.mostrar = true;

            self.$parent.reporteCargado();

          }else{

            throw "error";

          }

        })
        .catch(error => {


        });

      },
      created: function(){

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        self.formFiltro.campos.fechaDesde.maxValue = now;
        self.formFiltro.campos.fechaHasta.maxValue = now;

      },
      beforeUpdate:function(){},
      updated: function(){},
      methods: {

        mostrarAlert: function(alert, mostrar = false, variante = "", mensaje = "", iconCerrar = false, contador = false, ocultarSeg = 0){

          return new Promise(resolve => {

            alert.contador = contador;
            alert.iconCerrar = iconCerrar;
            alert.mensaje = mensaje;
            alert.mostrar = mostrar;
            alert.ocultarSeg = ocultarSeg;
            alert.variante = variante;

            resolve(true);

          });

        },
        empleadosDivision: function(){

          self.limpiarMensajeError(self.formFiltro.campos.divisiones);
          self.formFiltro.campos.empleados.disabled = true;
          self.formFiltro.campos.empleados.value = null;
          self.formFiltro.campos.empleados.seleccione = "buscando!";

          let parametros = {
            id_division :  self.formFiltro.campos.divisiones.value
          };

          axios.get('/repTotalHorasEmpEmpleadosDivision', {params: parametros})
          .then(function (response) {

            if(response.status === 200){

              self.formFiltro.campos.empleados.listado = response.data.empleados;
              self.formFiltro.campos.empleados.disabled = false;
              self.formFiltro.campos.fechaDesde.disabled = false;
              self.formFiltro.campos.empleados.seleccione = "seleccione...";

            }else{

              throw "error";

            }

          })
          .catch(error => {


          });

        },
        fechaDesdeFiltro: function(fecha_seleccionada){

          self.limpiarMensajeError(self.formFiltro.campos.fechaDesde)

          self.formFiltro.campos.fechaHasta.value = null;
          self.formFiltro.campos.fechaHasta.minValue = fecha_seleccionada;
          self.formFiltro.campos.fechaHasta.disabled = false;

        },
        buscar: function(){

          var formValido = true;

          //self.mostrarAlert(self.form.alert);

          Object.keys(self.formFiltro.campos).forEach((indice, i) => {

            if(self.formFiltro.campos[indice].hasOwnProperty("state")){
              self.formFiltro.campos[indice].state = (self.formFiltro.campos[indice].state === true) ? true : null;
            }

            if(self.formFiltro.campos[indice].hasOwnProperty("invalidFeedback")){
              self.formFiltro.campos[indice].invalidFeedback = "";
            }

          });

          const arrayCampos = Object.keys(self.formFiltro.campos);
          for(var i = 0; i <= (arrayCampos.length - 1); i++){

            let indice = arrayCampos[i];
            const campo = self.$v.formFiltro.campos[indice].value;
            campo.$touch();

            if(campo.$invalid){

              self.formFiltro.campos[indice].state = false;
              const valorCampo = self.$v.formFiltro.campos[indice].value.$model;

              const arrayParams = Object.keys(campo.$params);
              for(var j = 0; j <= (arrayParams.length - 1); j++){

                let mensajeError = self.validadorMensajes(arrayParams[j], campo);
                self.formFiltro.campos[indice].invalidFeedback = mensajeError.mensaje;

                if(!mensajeError.respuesta){
                  break
                }

              }

              zenscroll.toY(self.$refs[indice].$el);
              formValido = false;
              break;

            }

          }

          if(formValido){

            Object.keys(self.formFiltro.campos).forEach((indice, i) => {

              if(self.formFiltro.campos[indice].hasOwnProperty("disabled")){
                self.formFiltro.campos[indice].disabled = true;
              }

            });

            self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
            self.formFiltro.btn.filtrar.disabled = true;
            self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
            self.formFiltro.btn.limpiarFiltro.disabled = true;

            //Obtenemos los valores
            let parametros = {
              division: self.formFiltro.campos.divisiones.value,
              empleado: self.formFiltro.campos.empleados.value,
              fecha_desde: self.formFiltro.campos.fechaDesde.value,
              fecha_hasta: self.formFiltro.campos.fechaHasta.value
            };

            //Se utiliza el metodo get para su busqueda y se envian con los parametros
            axios.get('/repTotalHorasInfoEmp', {params: parametros})
            .then(function (response) {

              Object.keys(self.formFiltro.campos).forEach((indice, i) => {

                if(self.formFiltro.campos[indice].hasOwnProperty("disabled")){
                  self.formFiltro.campos[indice].disabled = false;
                }

              });

              self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
              self.formFiltro.btn.filtrar.disabled = false;
              self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
              self.formFiltro.btn.limpiarFiltro.disabled = false;

            }).catch(error => {

              Object.keys(self.formFiltro.campos).forEach((indice, i) => {

                if(self.formFiltro.campos[indice].hasOwnProperty("disabled")){
                  self.formFiltro.campos[indice].disabled = false;
                }

              });

              self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
              self.formFiltro.btn.filtrar.disabled = false;
              self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
              self.formFiltro.btn.limpiarFiltro.disabled = false;

            });

          }

        },
        limpiarFiltro: function(){

          self.formFiltro.campos.empleados.disabled = true;
          self.formFiltro.campos.fechaDesde.disabled = true;
          self.formFiltro.campos.fechaHasta.disabled = true;

          self.formFiltro.campos.divisiones.value = null;
          self.formFiltro.campos.empleados.value = null;
          self.formFiltro.campos.fechaDesde.value = null;
          self.formFiltro.campos.fechaHasta.value = null;

        },
        validadorMensajes: function(indice,campo){

          var mensaje,
              respuesta = true;

          if(!campo[indice] && indice === "required"){
            mensaje = "Este campo es requerido!";
            respuesta = false;
          }else if(!campo[indice] && indice === "minLength"){
            let minChar = campo.$params[indice].min;
            mensaje = "Debe contener al menos "+minChar+" Caracteres!";
            respuesta = false;
          }else if(!campo[indice] && indice === "email"){
            mensaje = "Correo inválido!";
            respuesta = false;
          }else if(!campo[indice] && indice === "minValue"){
            let minChar = campo.$params[indice].min;
            mensaje = "El valor mínimo es "+minChar+"!";
            respuesta = false;
          }else{
            mensaje = "";
          }

          return {mensaje:mensaje, respuesta:respuesta};

        },
        limpiarMensajeError: function(objeto){

          objeto.state = null;
          objeto.invalidFeedback = "";

        }
      }
  }

</script>
