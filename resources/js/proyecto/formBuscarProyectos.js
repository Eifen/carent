require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;


Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(VueNumeric);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

new Vue({

  el: '#app',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
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
      descripcion:{
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
    loading: true,
    paginador: {
      max: 0,
      numPaginas: 0,
      pagina:1,
      paginar: 0
    },
    permisoActualizar: false,
    proyectos: []
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialListadoProyectos')
    .then(function (response) {

      if(response.status === 200){
        //Le asignamos los valores a las variables
        self.comboEstatus = response.data.estatus;
        self.comboDivisiones = response.data.divisiones;
        self.formFiltro.descripcion.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.mostrar = true;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;

        self.proyectos = response.data.proyectos;
        self.permisoActualizar = response.data.permisoActualizar;

        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;

        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.alertForm = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

      self.loading = false;

    });

  },
  created: function () {},
  mounted: function () {},
  updated: function () {},
  methods:{

    valuesForm: function(e){

      if(e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'email'){
        self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeErrorMultiselect: function(){
      $(".multiselect").parent().find(".mensaje").html("").removeClass("invalid-feedback");
      $(".multiselect").removeClass("error");
    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    campoOpcionalARequerido: function(e){

      self.valuesForm(e);
      self.form[e.target.id].validar = (self.form[e.target.id].value.length > 0 && self.form[e.target.id].validar === false) ? true : false;

    },
    refreshView: function(){
      window.location.href = "/formNuevoProyecto";
    },
    limpiarFiltro: function(){

      self.formFiltro.descripcion.value = "";
      self.formFiltro.cliente.value = "";
      self.formFiltro.divisiones.value = "";
      self.formFiltro.estatus.value = "";
      self.buscar();

    },
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

  }// Fin methods

});
