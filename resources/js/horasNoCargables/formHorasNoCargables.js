require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import { Datetime } from 'vue-datetime';
const luxon  = require("luxon");
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;

Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('datetime', Datetime);
Vue.use(VueNumeric);


var app = new Vue({

  el: '#app',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alertCargarHora: {
      class: "",
      message: "",
      show: false
    },
    alertModificarConcepto: {
      class: "",
      message: "",
      show: false
    },
    comboConceptos: [],
    comboEmpleados: [],
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
        value: []
      },
      divisiones:{
        disabled: true,
        value: []
      },
      empleados:{
        disabled: true,
        value: []
      },
      estatus: {
        disabled: true,
        value: ""
      },
      mostrar: false
    },
    formCargarHoras: {
      concepto: {
        disabled: false,
        value: ""
      },
      fechaDesde:{
        disabled:false,
        value: ""
      },
      fechaHasta:{
        disabled:true,
        minValue: "",
        value: ""
      },
      observacion: {
        disabled:false,
        maxlength: 250,
        value: ""
      }
    },
    formModificarConcepto: {
      concepto: {
        disabled: false,
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
    submitModalCargarHora: {
      content: "Cargar",
      disabled: false,
      show:true
    },
    submitModalModificarConcepto: {
      content: "Modificar",
      disabled: false,
      show:true
    },
    supervisor: false,
    supervisarTodo: false
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

        self.registros = response.data.registros;
        self.supervisor = response.data.supervisor;
        self.supervisarTodo = response.data.supervisar_todo;

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

    $('#modal-cargar').on('hidden.bs.modal', function () {

      self.alertCargarHora = {
        class : "",
        message : "",
        show: false
      };

      self.submitModalCargarHora = {
        content: "Crear",
        disabled: false,
        show:true
      }

      self.formCargarHoras = {
        concepto: {
          disabled: false,
          value: ""
        },
        fechaDesde:{
          disabled:false,
          value: ""
        },
        fechaHasta:{
          disabled:true,
          minValue: "",
          value: ""
        },
        observacion: {
          disabled:false,
          maxlength: 250,
          value: ""
        }
      }

      $(".multiselect").parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
      $(".multiselect .multiselect__tags").removeClass("error");

      $("#formCargarHoras .fechaDesde, #formCargarHoras .fechaHasta").removeClass("error");
      $("#formCargarHoras .fechaDesde, #formCargarHoras .fechaHasta").parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

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

    cargar: function(){
      $("#modal-cargar").modal("show");
    },
    fechaMinima: function(form,e){

      if(e !== ""){

        var fecha_hasta = new Date(e).getTime() + (30 * 60000);
            fecha_hasta = new Date(fecha_hasta).toISOString();

        self[form].fechaHasta.minValue = fecha_hasta;
        self[form].fechaHasta.disabled = false;
        self.limpiarMensajeError($("#"+form+" .fechaDesde"));
      }

    },
    cargarHoras: function(){

      var formValido = self.validarForm("formCargarHoras");

      if(formValido){

        self.alertCargarHora = {
          class : "",
          message : "",
          show: false
        };

        self.formCargarHoras.concepto.disabled = true;
        self.formCargarHoras.fechaDesde.disabled = true;
        self.formCargarHoras.fechaHasta.disabled = true;
        self.formCargarHoras.observacion.disabled = true;

        //Obtenemos valores
        let parametros = {
          concepto: self.formCargarHoras.concepto.value.id,
          fechaDesde: self.formCargarHoras.fechaDesde.value,
          fechaHasta: self.formCargarHoras.fechaHasta.value,
          observacion: self.formCargarHoras.observacion.value
        }

        self.submitModalCargarHora.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitModalCargarHora.disabled = true;
        self.formCargarHoras.concepto.disabled = true;

        axios.post('/registrarHorasNoCargables', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.respuesta === true){

            self.submitModalCargarHora.content = 'Cargar';

            //self.limpiarFiltro();

            self.alertCargarHora = {
              class : "alert alert-success",
              message : response.data.mensaje,
              show: true
            };

            setTimeout(function(){ $("#modal-cargar").modal("hide"); }, 3000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formCargarHoras.concepto.disabled = false;
          self.formCargarHoras.fechaDesde.disabled = false;
          self.formCargarHoras.fechaHasta.disabled = false;
          self.formCargarHoras.observacion.disabled = false;
          self.submitModalCargarHora.content = 'Cargar';
          self.submitModalCargarHora.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertCargarHora = {
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
      $(".multiselect").parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
      $(".multiselect .multiselect__tags").removeClass("error");
    },
    limpiarMensajeError: function(e){

      if(typeof e.target === "undefined"){
        var el = $(e);
      }else{
        var el = $(e.target);
      }

      el.removeClass("error");
      el.parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

    },
    campoOpcionalARequerido: function(e){

      self.valuesForm(e);
      self.form[e.target.id].validar = (self.form[e.target.id].value.length > 0 && self.form[e.target.id].validar === false) ? true : false;

    },
    limpiarFiltro: function(){

      self.formFiltro.conceptos.value = [];
      self.formFiltro.divisiones.value = [];
      self.formFiltro.empleados.value = [];
      self.formFiltro.estatus.value = "";
      self.buscar();

    },
    buscar: function(){

      self.formFiltro.conceptos.disabled = true;
      self.formFiltro.divisiones.disabled = true;
      self.formFiltro.empleados.disabled = true;
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
        concepto: ((self.formFiltro.conceptos.value.length === 0) ? null : self.formFiltro.conceptos.value[0].id),
        division: ((self.formFiltro.divisiones.value.length === 0) ? null : self.formFiltro.divisiones.value[0].id),
        empleado: ((self.formFiltro.empleados.value.length === 0) ? null : self.formFiltro.empleados.value[0].id),
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar,
        supervisa: ((self.supervisor === true && self.formFiltro.empleados.value.length === 0) ? true : false),
        supervisarTodo: ((self.supervisarTodo === true && self.formFiltro.divisiones.value.length === 0) ? true : false)
      };

      axios.get('/buscarHorasNoCargableCargadas', {params: parametros})
      .then(function (response) {

        self.formFiltro.conceptos.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.empleados.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlInit;
        self.formFiltro.btn.cargar.disabled = false;

        self.registros = response.data.registros;
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

      }).catch(error => {

        self.formFiltro.conceptos.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.empleados.disabled = false;
        self.formFiltro.estatus.disabled = false;
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
    validarForm: function(formulario) {

      if(self[formulario].concepto.value === "" || self[formulario].concepto.value === null){

        $('#'+formulario+" #conceptoNew").find(".mensaje").html("Este campo es requerido!").addClass("invalid-feedback");
        $('#'+formulario+" #conceptoNew").find(".multiselect__tags").addClass("error");

        return false;

      }else{

        if(self[formulario].fechaDesde.value === "" || self[formulario].fechaDesde.value === null){

          $('#'+formulario+" #fechaDesde").find(".mensaje").html("Este campo es requerido!").addClass("invalid-feedback");
          $('#'+formulario+" #fechaDesde").find(".form-control").addClass("error");

          return false;

        }else{

          if(self[formulario].fechaHasta.value === "" || self[formulario].fechaHasta.value === null){

            $('#'+formulario+" #fechaHasta").find(".mensaje").html("Este campo es requerido!").addClass("invalid-feedback");
            $('#'+formulario+" #fechaHasta").find(".form-control").addClass("error");

            return false;

          }else{

            return true;

          }

        }

      }// Fin del if

    },
    modificarConcepto: function(id,concepto,id_estatus){

      $("#modal-modificar-concepto").modal("show");
      self.formModificarConcepto.concepto.value = concepto;
      self.formModificarConcepto.concepto.id = id;
      self.formModificarConcepto.estatus.value = id_estatus;

    },
    guardarModificarConcepto: function(){

      var formValido = validarForm('formCargarHoras');
console.log(formValido);
      return

      $("#formModificarConcepto .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("#formModificarConcepto .form-group .form-control").removeClass("error");

      $("#formModificarConcepto .form-group").each(function(index, elemento) {

        var input = $(elemento).find(".form-control")[0];
        var valido = self.validarForm(input);

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
