require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import VueNumeric from 'vue-numeric';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import axios from 'axios';

Vue.component('loading', require('../components/loading.vue').default);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.component('reporte-1',require('./horasCargables.vue').default);
Vue.component('reporte-2',require('./clientesProyecto.vue').default);
Vue.component('reporte-3',require('./empleados.vue').default);
Vue.component('reporte-4',require('./clientes.vue').default);
Vue.component('reporte-5',require('./horasProyectos.vue').default);
Vue.component('reporte-6',require('./facturadoCliProy.vue').default);
Vue.component('reporte-7',require('./totalHorasEmp.vue').default);
Vue.component('reporte-8',require('./totalHorasCarg.vue').default);

Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.component('multiselect', Multiselect);
Vue.use(VueNumeric);

new Vue({

  el: '#app',
  data: {
    alert:{
      message: "",
      mostrar: false
    },
    formReportes: {
      btn: {
        generar: {
          disabled: true,
          html: "",
          htmlInit: "Generar Reporte",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        }
      },
      reportes:{
        disabled: true,
        listado: [],
        recargar: false,
        verReporte: {
          id: null,
          key: 0
        },
        value: null
      },
      mostrar: false
    },
    loading: true,
    permisos: null
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialFormReportes')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        //Le asignamos los valores a las variables
        self.formReportes.reportes.disabled = false;
        self.formReportes.mostrar = true;
        self.formReportes.btn.generar.html = self.formReportes.btn.generar.htmlInit;

        response.data.reportes.forEach((item, i) => {
          self.formReportes.reportes.listado.push({text : item.descripcion, value : item.id});
        });

        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.alert.mostrar = true;
      self.alert.message = (error.data.message) ? error.data.message : "Ocurrió un error!, por favor intente recargando la página.";
      self.loading = false;

    });

  },
  created: function () {},
  mounted: function () {},
  updated: function () {},
  methods:{
    reporteSeleccionado: function(valor){

      if(valor !== null){
        self.formReportes.btn.generar.disabled = false;
      }

    },
    generarReporte: function(){

      if(self.formReportes.reportes.verReporte.id === self.formReportes.reportes.value) {
        self.formReportes.reportes.verReporte.key += 1;
      }

      self.formReportes.reportes.disabled = true;
      self.formReportes.btn.generar.html = self.formReportes.btn.generar.htmlLoading;
      self.formReportes.btn.generar.disabled = true;

      self.formReportes.reportes.verReporte.id = self.formReportes.reportes.value;

    },
    reporteCargado: function(){

      self.formReportes.reportes.disabled = false;
      self.formReportes.btn.generar.html = self.formReportes.btn.generar.htmlInit;
      self.formReportes.btn.generar.disabled = false;
      self.formReportes.reportes.recargar = false;

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        e.preventDefault();
      }

    },
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

    }
  }

});
