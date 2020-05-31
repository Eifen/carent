require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import Datepicker from 'vuejs-datepicker';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;


Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('date-picker', Datepicker);
Vue.use(VueNumeric);

var app = new Vue({

  el: '#app',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alertConceptoNuevo: {
      class: "",
      message: "",
      show: false
    },
    alertModificarConcepto: {
      class: "",
      message: "",
      show: false
    },
    comboEstatus: [],
    comboDivisiones: [],
    conceptos: [],
    formFiltro: {
      btn: {
        cargar: {
          disabled: false,
          html: "",
          htmlInit: "Cargar Hora",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
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
      conceptos: {
        disabled: true,
        value: ""
      },
      divisiones:{
        disabled: true,
        value: ""
      },
      empleados:{
        disabled: true,
        value: ""
      },
      estatus: {
        disabled: true,
        value: ""
      },
      mostrar: false
    },
    formNuevoConcepto: {
      concepto: {
        disabled: false,
        value: ""
      }
    },
    formModificarConcepto: {
      concepto: {
        disabled: false,
        id: null,
        value: ""
      },
      estatus:{
        disabled: false,
        value: ""
      }
    },
    paginador: {
      max: 0,
      numPaginas: 0,
      pagina:1,
      paginar: 0
    },
    submitModalConceptoNuevo: {
      content: "Crear",
      disabled: false,
      show:true
    },
    submitModalModificarConcepto: {
      content: "Modificar",
      disabled: false,
      show:true
    }
  },
  beforeCreate: function(){

    self = this;

    axios.get('/dataInicialHorasNoCargables')
    .then(function (response) {

      if(response.status === 200 && response.data.error === false){

        self.comboConceptos = response.data.conceptos;
        self.comboDivisiones = response.data.divisiones;
        self.comboEmpleados = response.data.empleados;
        self.comboEstatus = response.data.estatus;
        self.formFiltro.conceptos.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.empleados.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.mostrar = true;
        self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlInit;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;

        self.conceptos = response.data.conceptos;

        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;

      }else if(response.data.error === true){
        throw response.data;
      }else{
        throw "error";
      }

    })
    .catch(error => {

      self.alertForm = {
        class : "alert alert-warning",
        message : (error.mensaje) ? error.mensaje : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

    });

  },
  created: function () {},
  mounted: function () {

    $('#modal-crear-concepto').on('hidden.bs.modal', function () {

      self.alertConceptoNuevo = {
        class : "",
        message : "",
        show: false
      };

      self.submitModalConceptoNuevo = {
        content: "Crear",
        disabled: false,
        show:true
      }

      self.formNuevoConcepto = {
        concepto: {
          disabled: false,
          value: ""
        }
      }

      $("#conceptoNuevo").removeClass("error");
      $("#conceptoNuevo").parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

    });

    $('#modal-modificar-concepto').on('hidden.bs.modal', function () {

      self.alertModificarConcepto = {
        class : "",
        message : "",
        show: false
      };

      self.submitModalModificarConcepto = {
        content: "Crear",
        disabled: false,
        show:true
      }

      self.formModificarConcepto = {
        concepto: {
          disabled: false,
          id: null,
          value: ""
        },
        estatus: {
          disabled: false,
          value: ""
        }
      }

      $("#modificarConcepto").removeClass("error");
      $("#modificarConcepto").parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

    });

  },
  updated: function () {},
  methods:{

    crearNuevo: function(){
      $("#modal-crear-concepto").modal("show");
    },
    soloLetras: function(e){

      if(e.target.value.trim() === ''){
        self.formNuevoConcepto.concepto.value = '';
      }

    },
    crearConcepto: function(){

      var formValido = true;

      $("#formNuevoConcepto .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("#formNuevoConcepto .form-group .form-control").removeClass("error");

      $("#formNuevoConcepto .form-group").each(function(index, elemento) {

        var input = $(elemento).find(".form-control")[0];
        var valido = self.validarValor(input);

        if(!valido.respuesta){
          $(elemento).find(".mensaje").html(valido.mensaje).addClass("invalid-feedback");
          $(elemento).find(".form-control").addClass("error");
          formValido = valido.respuesta;
          return false;
        }

      });

      if(formValido){

        self.alertConceptoNuevo = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          concepto: self.formNuevoConcepto.concepto.value
        }

        self.submitModalConceptoNuevo.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitModalConceptoNuevo.disabled = true;
        self.formNuevoConcepto.concepto.disabled = true;

        axios.post('/crearConceptoNoCargable', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.respuesta === true){

            //self.submitModalConceptoNuevo.show = false;
            self.formNuevoConcepto.concepto.value = "";
            self.submitModalConceptoNuevo.disabled = false;
            self.formNuevoConcepto.concepto.disabled = false;
            self.submitModalConceptoNuevo.content = 'Crear';

            self.limpiarFiltro();

            self.alertConceptoNuevo = {
              class : "alert alert-success",
              message : response.data.mensaje,
              show: true
            };

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formNuevoConcepto.concepto.disabled = false;
          self.submitModalConceptoNuevo.content = 'Crear';
          self.submitModalConceptoNuevo.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertConceptoNuevo = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

      }// Fin if(formValido)

    },
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
    limpiarFiltro: function(){


      self.formFiltro.estatus.value = "";
      self.buscar();

    },
    buscar: function(){


      self.formFiltro.estatus.disabled = true;
      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.disabled = true;
      self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlLoading;
      self.formFiltro.btn.cargar.disabled = true;

      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        desde: desde,
        concepto: self.formFiltro.descripcion.value,
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar
      };

      axios.get('/buscarConceptoHorasNoCargables', {params: parametros})
      .then(function (response) {

        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlInit;
        self.formFiltro.btn.cargar.disabled = false;

        self.conceptos = response.data.conceptos;
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

      }).catch(error => {

        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlInit;
        self.formFiltro.btn.cargar.disabled = false;

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
    },
    validarValor: function(input) {

      var respuesta = true;
      var mensaje   = '';

      if(input.hasAttribute("data-validar")){

        if(input.getAttribute("data-validar") === "true"){

          if(input.type === 'text' || input.type === 'textarea'){

            if(input.getAttribute("data-min")){
              let minChar = input.getAttribute("data-min");
              let numChar = input.value.length

              if(numChar < minChar){
                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);
              }

            }

          }

        }

      }

      return {respuesta: respuesta, mensaje: mensaje};

    },
    modificarConcepto: function(id,concepto,id_estatus){

      $("#modal-modificar-concepto").modal("show");
      self.formModificarConcepto.concepto.value = concepto;
      self.formModificarConcepto.concepto.id = id;
      self.formModificarConcepto.estatus.value = id_estatus;

    },
    guardarModificarConcepto: function(){

      var formValido = true;

      $("#formModificarConcepto .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("#formModificarConcepto .form-group .form-control").removeClass("error");

      $("#formModificarConcepto .form-group").each(function(index, elemento) {

        var input = $(elemento).find(".form-control")[0];
        var valido = self.validarValor(input);

        if(!valido.respuesta){
          $(elemento).find(".mensaje").html(valido.mensaje).addClass("invalid-feedback");
          $(elemento).find(".form-control").addClass("error");
          formValido = valido.respuesta;
          return false;
        }

      });

      if(formValido){

        self.alertModificarConcepto = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          concepto: self.formModificarConcepto.concepto.value,
          id: self.formModificarConcepto.concepto.id,
          id_estatus: self.formModificarConcepto.estatus.value
        }

        self.submitModalModificarConcepto.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitModalModificarConcepto.disabled = true;
        self.formModificarConcepto.concepto.disabled = true;
        self.formModificarConcepto.estatus.disabled = true;

        axios.post('/modificarConceptoNoCargable', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.respuesta === true){

            self.submitModalModificarConcepto.disabled = false;
            self.formModificarConcepto.concepto.disabled = false;
            self.formModificarConcepto.estatus.disabled = false;
            self.submitModalModificarConcepto.content = 'Modificar';

            self.limpiarFiltro();

            self.alertModificarConcepto = {
              class : "alert alert-success",
              message : response.data.mensaje,
              show: true
            };

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formModificarConcepto.concepto.disabled = false;
          self.formModificarConcepto.estatus.disabled = false;
          self.submitModalModificarConcepto.content = 'Modificar';
          self.submitModalModificarConcepto.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertModificarConcepto = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

      }// Fin if(formValido)

    }

  }// Fin methods

});
