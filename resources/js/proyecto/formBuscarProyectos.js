require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;


Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.use(VueNumeric);

var app = new Vue({

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

    axios.get('/dataInicialListadoProyectos')
    .then(function (response) {

      if(response.status === 200){

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
    keyboard: function(e){

      if (e.keyCode === 13){
        self.crear();
      }

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

      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        cliente: self.formFiltro.cliente.value,
        divisiones: self.formFiltro.divisiones.value,
        proyecto: self.formFiltro.descripcion.value,
        desde: desde,
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar
      };
      console.log(parametros);
      return;

      axios.get('/buscarConceptos', {params: parametros})
      .then(function (response) {

        self.formFiltro.submit.disabled = false;
        self.formFiltro.submit.html = self.formFiltro.submit.htmlInit;
        self.formFiltro.reset.disabled = false;
        self.formFiltro.reset.html = self.formFiltro.reset.htmlInit;
        self.formFiltro.concepto.disabled = false;
        self.formFiltro.estatus.disabled = false;

        self.tabla.registros = self.registroTabla(response.data.conceptos);
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

      }).catch(error => {

        console.log("error filtro");
        self.formFiltro.submit.disabled = false;
        self.formFiltro.submit.html = self.formFiltro.submit.htmlInit;
        self.formFiltro.reset.disabled = false;
        self.formFiltro.reset.html = self.formFiltro.reset.htmlInit;
        self.formFiltro.concepto.disabled = false;
        self.formFiltro.estatus.disabled = false;

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
