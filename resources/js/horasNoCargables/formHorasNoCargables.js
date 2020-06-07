require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import { Datetime } from 'vue-datetime';
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
    alertModificarHora: {
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
      estatus: {
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
    formModificarHoras: {
      aprobadoPor: "",
      concepto: {
        disabled: false,
        value: ""
      },
      estatus: {
        disabled: false,
        value: ""
      },
      fechaAprobacion: "",
      fechaDesde:{
        disabled:false,
        value: ""
      },
      fechaHasta:{
        disabled:false,
        minValue: "",
        value: ""
      },
      id: null,
      observacion: {
        disabled:false,
        maxlength: 250,
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
    submitModalModificarHora: {
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
        estatus: {
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

    $('#modal-modificar').on('hidden.bs.modal', function () {

      self.alertModificarHora = {
        class : "",
        message : "",
        show: false
      };

      self.submitModalModificarHora = {
        content: "Modificar",
        disabled: false,
        show:true
      }

      self.formModificarHoras = {
        aprobadoPor: "",
        concepto: {
          disabled: false,
          value: ""
        },
        estatus: {
          disabled: false,
          value: ""
        },
        fechaAprobacion: "",
        fechaDesde:{
          disabled:false,
          value: ""
        },
        fechaHasta:{
          disabled:false,
          minValue: "",
          value: ""
        },
        id: null,
        observacion: {
          disabled:false,
          maxlength: 250,
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
    utc_date: function(fecha){

      let date = new Date(fecha);
      let minutes = date.getMinutes();
          minutes = minutes < 10 ? '0'+minutes : minutes;
      let hours = date.getHours();
      let ampm = hours >= 12 ? 'pm' : 'am';
          hours = hours % 12;
          hours = hours ? hours : 12;
      let dd = date.getDate();
          dd = dd < 10 ? '0'+dd : dd;
      let mm = date.getMonth()+1;
          mm = mm < 10 ? '0'+mm : mm;
      let yyyy = date.getFullYear();
      let date_formated = dd+"/"+mm+"/"+yyyy+" "+hours+":"+minutes+" "+ampm;

      return date_formated

    },
    fechaMinima: function(form,e){

      if(e !== ""){

        var fecha_hasta = new Date(e).getTime() + (30 * 60000);
            fecha_hasta = new Date(fecha_hasta).toISOString();

        self[form].fechaHasta.minValue = fecha_hasta;
        self[form].fechaHasta.value = "";
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
        self.formCargarHoras.estatus.disabled = true;
        self.formCargarHoras.fechaDesde.disabled = true;
        self.formCargarHoras.fechaHasta.disabled = true;
        self.formCargarHoras.observacion.disabled = true;

        //Obtenemos valores
        let parametros = {
          concepto: self.formCargarHoras.concepto.value.id,
          estatus: self.formCargarHoras.estatus.value,
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
            self.limpiarFiltro();

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
          estatus: self.formCargarHoras.estatus.disabled = false;
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

        $('#'+formulario+" #concepto").find(".mensaje").html("Este campo es requerido!").addClass("invalid-feedback");
        $('#'+formulario+" #concepto").find(".multiselect__tags").addClass("error");

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
    modificarConcepto: function(id,autor,id_concepto,concepto,fecha_desde,fecha_hasta,observacion,id_estatus,editar, fecha_aprobacion, aprobado_por){

      self.formModificarHoras.id = id;
      self.formModificarHoras.concepto.value = {
        id:id_concepto,
        descripcion: concepto
      };
      self.formModificarHoras.fechaDesde.value = fecha_desde;
      self.fechaMinima("formModificarHoras",fecha_desde);
      self.formModificarHoras.fechaHasta.value = fecha_hasta;
      self.formModificarHoras.observacion.value = (observacion === null) ? "" : observacion;
      self.formModificarHoras.estatus.value = id_estatus;

      self.formModificarHoras.concepto.disabled = ((autor === 1 && editar === 1) || (autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.fechaDesde.disabled = ((autor === 1 && editar === 1) || (autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.fechaHasta.disabled = ((autor === 1 && editar === 1) || (autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.observacion.disabled = ((autor === 1 && editar === 1) || (autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.estatus.disabled = (self.supervisor === true) ? false : true;
      self.submitModalModificarHora.show = ((autor === 1 && editar === 1) || self.supervisor === true) ? true : false;
      self.formModificarHoras.fechaAprobacion = (fecha_aprobacion === null) ? "" : fecha_aprobacion;
      self.formModificarHoras.aprobadoPor = (aprobado_por === null) ? "" : aprobado_por;

      $("#modal-modificar").modal("show");

    },
    guardarModificar: function(){

      var formValido = self.validarForm('formModificarHoras');

      if(formValido){

        self.alertModificarHora = {
          class : "",
          message : "",
          show: false
        };

        self.formModificarHoras.concepto.disabled = true;
        self.formModificarHoras.fechaDesde.disabled = true;
        self.formModificarHoras.fechaHasta.disabled = true;
        self.formModificarHoras.observacion.disabled = true;
        self.formModificarHoras.estatus.disabled = true;

        //Obtenemos valores
        let parametros = {
          id: self.formModificarHoras.id,
          concepto: self.formModificarHoras.concepto.value.id,
          fechaDesde: self.formModificarHoras.fechaDesde.value,
          fechaHasta: self.formModificarHoras.fechaHasta.value,
          observacion: self.formModificarHoras.observacion.value,
          estatus: self.formModificarHoras.estatus.value
        }

        self.submitModalModificarHora.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitModalModificarHora.disabled = true;
        self.formModificarHoras.concepto.disabled = true;
        self.formModificarHoras.estatus.disabled = true;

        axios.post('/modificarHorasNoCargables', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.respuesta === true){

            self.submitModalModificarHora.content = 'Modificar';
            self.limpiarFiltro();

            self.alertModificarHora = {
              class : "alert alert-success",
              message : response.data.mensaje,
              show: true
            };

            setTimeout(function(){ $("#modal-modificar").modal("hide"); }, 3000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formModificarHoras.concepto.disabled = false;
          self.formModificarHoras.estatus.disabled = false;
          self.submitModalModificarHora.content = 'Modificar';
          self.submitModalModificarHora.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertModificarHora = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

      }// Fin if(formValido)

    }

  }// Fin methods

});
