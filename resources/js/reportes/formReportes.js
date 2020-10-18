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
          disabled: false,
          html: "",
          htmlInit: "Generar Reporte",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        }
      },
      reportes:{
        disabled: true,
        value: ""
      },
      mostrar: false
    },
    loading: true,
    permisos: null
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialIngresosGastos')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){



        //Le asignamos los valores a las variables
        self.formReportes.reportes.disabled = false;
        self.formReportes.mostrar = true;
        self.formReportes.btn.generar.html = self.formReportes.btn.generar.htmlInit;

        self.permisos = response.data.permisos;

        /*esponse.data.estatus.forEach((item, i) => {
          self.comboEstatus.push({text : item.descripcion, value : item.valor});
        });*/

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
    registroTabla: function(datos){

      const registros = [];
      datos.forEach((item, i) => {

        var variante;

        switch (item.id_estatus) {
          case 1: variante = "success"; break;
          case 2: variante = "danger"; break;
          case 3: variante = "warning"; break;
          case 4: variante = "warning"; break;
          default: variante = "light";
        }

        const proyecto = {
          numero: (i + 1),
          proyecto: item.proyecto,
          cliente: item.cliente,
          fecha_contratacion: item.fecha_contratacion,
          monto_contratado: item.simbolo_moneda+''+item.monto_contratado,
          estatus: item.estatus,
          id: item.id,
          id_estatus: item.id_estatus,
          variante: variante
        };

        registros.push(proyecto);

      });

      return registros;

    },
    buscar: function(){

      self.formFiltro.cliente.disabled = true;
      self.formFiltro.proyecto.disabled = true;
      self.formFiltro.estatus.disabled = true;
      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.disabled = true;

      //Obtenemos los valores
      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        cliente: self.formFiltro.cliente.value,
        proyecto: self.formFiltro.proyecto.value,
        desde: desde,
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar
      };

      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/buscarProyectoFacturacion', {params: parametros})
      .then(function (response) {

        self.formFiltro.cliente.disabled = false;
        self.formFiltro.proyecto.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;

        // Se le asigna los valores a las variables
        self.proyectos = response.data.proyectos;
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

        self.tabla.registros = self.registroTabla(response.data.proyectos);

      }).catch(error => {

        self.formFiltro.proyecto.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;

      });

    },
    limpiarFiltro: function(){

      self.formFiltro.proyecto.value = "";
      self.formFiltro.cliente.value = "";
      self.formFiltro.estatus.value = "";
      self.buscar();

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

    },
    paginaAnterior: function(){
      self.paginador.pagina = ((self.paginador.pagina - 1) === 0) ? 1 : (self.paginador.pagina - 1);
      self.buscar();
    },
    paginaSiguiente: function(){
      self.paginador.pagina = ((self.paginador.pagina + 1) > self.paginador.max) ? self.paginador.pagina : (self.paginador.pagina + 1);
      self.buscar();
    },
    numeroPagina: function(e){
      self.buscar();
    }
  }

});
