require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import axios from 'axios';

Vue.component('loading', require('../components/loading.vue').default);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.component('multiselect', Multiselect);

new Vue({

  el: '#app',
  data: {
    alert:{
      message: "",
      mostrar: false
    },
    comboEstatus: [],
    comboDivisiones: [],
    formFiltro: {
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
      cliente:{
        disabled: true,
        value: ""
      },
      proyecto:{
        disabled: true,
        value: ""
      },
      divisiones: {
        disabled: true,
        value: ""
      },
      estatus: {
        disabled: true,
        value: ""
      },
      mostrar: false
    },
    loading: true
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialIngresosGastos')
    .then(function (response) {

      if(response.status === 200){

        //Le asignamos los valores a las variables
        self.comboDivisiones = response.data.divisiones;
        self.formFiltro.proyecto.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.mostrar = true;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;

        response.data.estatus.forEach((item, i) => {
          self.comboEstatus.push({descripcion : item.descripcion, id : item.valor});
        });

        /*self.proyectos = response.data.proyectos;
        self.permisoActualizar = response.data.permisoActualizar;

        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;*/

        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.alert.mostrar = true;
      self.alert.message = (error.data.message) ? error.data.message : "Ocurrió un error!, por favor intente recargando la página.";
      self.loading = false;
      self.tabla.cargando = false;

    });

  },
  created: function () {},
  mounted: function () {},
  updated: function () {},
  methods:{
    buscar: function(){

      self.formFiltro.descripcion.disabled = true;
      self.formFiltro.cliente.disabled = true;
      self.formFiltro.divisiones.disabled = true;
      self.formFiltro.estatus.disabled = true;
      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.disabled = true;

      let idsDivisiones = [];
      if(self.formFiltro.divisiones.value.length > 0){
        self.formFiltro.divisiones.value.forEach((item, i) => {
          idsDivisiones.push(item.id);
        });
      }

      //Obtenemos los valores
      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        cliente: self.formFiltro.cliente.value,
        divisiones: idsDivisiones,
        proyecto: self.formFiltro.descripcion.value,
        desde: desde,
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar
      };
      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/buscarProyectos', {params: parametros})
      .then(function (response) {

        self.formFiltro.descripcion.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        // Se le asigna los valores a las variables
        self.proyectos = response.data.proyectos;
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

      }).catch(error => {

        self.formFiltro.descripcion.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;

      });

    },
    limpiarFiltro: function(){

      self.formFiltro.descripcion.value = "";
      self.formFiltro.cliente.value = "";
      self.formFiltro.divisiones.value = "";
      self.formFiltro.estatus.value = "";
      self.buscar();

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    limpiarMensajeErrorMultiselect: function(){
      $(".multiselect").parent().find(".mensaje").html("").removeClass("invalid-feedback");
      $(".multiselect").removeClass("error");
    },
    keyboard: function(e){

      if (e.keyCode === 13){
        e.preventDefault();
      }

    }
  }

});
