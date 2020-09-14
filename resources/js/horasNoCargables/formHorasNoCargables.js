require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
const moment = require('moment');
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import { Datetime } from 'vue-datetime';
import 'vue-datetime/dist/vue-datetime.css';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;

Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component('datetime', Datetime);
Vue.use(VueNumeric);
Vue.use(BootstrapVue);

new Vue({

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
      fechaDesde:{
        disabled:false,
        maxValue: "",
        value: ""
      },
      fechaHasta:{
        disabled:true,
        maxValue: "",
        minValue: "",
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
        maxValue: "",
        value: ""
      },
      fechaHasta:{
        disabled:true,
        maxValue: "",
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
        maxValue: "",
        value: ""
      },
      fechaHasta:{
        disabled:false,
        maxValue: "",
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
    loading: true,
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
    confirmarModificarHora: {
      content: "Eliminar",
      disabled: false,
      show:true
    },
    cancelarEliminarModificarHora: {
      content: "No quiero eliminarlas",
      disabled: false,
      show:false
    },
    eliminarModificarHora: {
      content: "Si, estoy de acuerdo en eliminarlas",
      disabled: false,
      show: false
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
        self.formFiltro.fechaDesde.disabled = false;
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

        self.loading = false;

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

      self.loading = false;

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
          maxValue: "",
          minValue: "",
          value: ""
        },
        fechaHasta:{
          disabled:true,
          maxValue: "",
          minValue: "",
          value: ""
        },
        observacion: {
          disabled:false,
          maxlength: 250,
          value: ""
        }
      }

      self.fechaDesde("formCargarHoras", "");

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

      self.confirmarModificarHora = {
        content: "Eliminar",
        disabled: false,
        show:true
      }

      self.cancelarEliminarModificarHora = {
        content: "No quiero eliminarlas",
        disabled: false,
        show:false
      }

      self.eliminarModificarHora = {
        content: "Si, estoy de acuerdo en eliminarlas",
        disabled: false,
        show:false
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
          maxValue: "",
          minValue: "",
          value: ""
        },
        fechaHasta:{
          disabled:false,
          maxValue: "",
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

      self.fechaDesde("formModificarHoras", "");

      $("#modificarConcepto").removeClass("error");
      $("#modificarConcepto").parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");

    });

  },
  updated: function () {},
  methods:{

    cargar: function(){
      $("#modal-cargar").modal("show");
    },
    fechaDesdeFiltro: function(fecha_seleccionada){

      self.formFiltro.fechaHasta.value = "";

      var fecha, fecha_desde_max;

      if(fecha_seleccionada !== ""){

        fecha = moment();
        fecha_desde_max = moment(fecha_seleccionada);

        var fecha_hasta_max = fecha;

        if(fecha_hasta_max.minute() < 30){
          self.formFiltro.fechaHasta.maxValue = fecha_hasta_max.startOf("hour").toISOString();
        }else{
          self.formFiltro.fechaHasta.maxValue = fecha_hasta_max.startOf("hour").add(30, "minutes").toISOString();
        }

        var fecha_hasta_min = fecha_desde_max;
        self.formFiltro.fechaHasta.minValue = fecha_hasta_min.add(30, "minutes").toISOString();
        self.formFiltro.fechaHasta.disabled = false;

      }else{

        fecha = fecha_desde_max = moment();

        if(fecha_desde_max.minute() < 30){
          fecha_desde_max = fecha_desde_max.startOf("hour").subtract(30, "minute");
          self.formFiltro.fechaDesde.maxValue = fecha_desde_max.toISOString();
        }else{
          fecha_desde_max =  fecha_desde_max.startOf("hour");
          self.formFiltro.fechaDesde.maxValue = fecha_desde_max.toISOString();
        }

      }

    },
    fechaDesde: function(form,e){

      self.limpiarMensajeError($("#"+form+" .fechaDesde"));

      self[form].fechaHasta.value = "";

      var fecha, fecha_desde_max;

      if(e !== ""){

        fecha = moment();
        fecha_desde_max = moment(e);

        var fecha_hasta_max = fecha;

        if(fecha_hasta_max.minute() < 30){
          self[form].fechaHasta.maxValue = fecha_hasta_max.startOf("hour").toISOString();
        }else{
          self[form].fechaHasta.maxValue = fecha_hasta_max.startOf("hour").add(30, "minutes").toISOString();
        }

        var fecha_hasta_min = fecha_desde_max;
        self[form].fechaHasta.minValue = fecha_hasta_min.add(30, "minutes").toISOString();
        self[form].fechaHasta.disabled = false;

      }else{

        fecha = fecha_desde_max = moment();

        if(fecha_desde_max.minute() < 30){
          fecha_desde_max = fecha_desde_max.startOf("hour").subtract(30, "minute");
          self[form].fechaDesde.maxValue = fecha_desde_max.toISOString();
        }else{
          fecha_desde_max =  fecha_desde_max.startOf("hour");
          self[form].fechaDesde.maxValue = fecha_desde_max.toISOString();
        }

      }

    },
    fechaHasta: function(form, fecha_desde, fecha_hasta){

      self.limpiarMensajeError($("#"+form+" .fechaHasta"));

      if(fecha !== ""){

        var fecha = moment();
        var fecha_desde_max = moment(fecha_desde);

        var fecha_hasta_max = fecha;

        if(fecha_hasta_max.minute() < 30){
          self[form].fechaHasta.maxValue = fecha_hasta_max.startOf("hour").toISOString();
        }else{
          self[form].fechaHasta.maxValue = fecha_hasta_max.startOf("hour").add(30, "minutes").toISOString();
        }

        var fecha_hasta_min = fecha_desde_max;
        self[form].fechaHasta.minValue = fecha_hasta_min.add(30, "minutes").toISOString();

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

          if(response.status === 200 && response.data.response === true){

            self.submitModalCargarHora.content = 'Cargar';
            self.limpiarFiltro();

            self.alertCargarHora = {
              class : "alert alert-success",
              message : response.data.message,
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
    limpiarFecha: function(form,fecha){
      self[form][fecha].value = "";

      if(fecha === "fechaDesde"){
        self[form].fechaHasta.value = "";
        self[form].fechaHasta.disabled = true;
      }

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
      self.formFiltro.fechaDesde.value = "";
      self.formFiltro.fechaHasta.value = "";
      self.buscar();

    },
    buscar: function(){

      self.formFiltro.conceptos.disabled = true;
      self.formFiltro.divisiones.disabled = true;
      self.formFiltro.empleados.disabled = true;
      self.formFiltro.estatus.disabled = true;
      self.formFiltro.fechaDesde.disabled = true;
      self.formFiltro.fechaHasta.disabled = true;

      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.disabled = true;
      self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlLoading;
      self.formFiltro.btn.cargar.disabled = true;

      //Evaluamos como filtraremos la division
      if(self.formFiltro.divisiones.value.length === 0 && self.comboDivisiones.length > 1){
        var param_divisiones = null;
      }else if(self.formFiltro.divisiones.value.length > 0){
        var param_divisiones = self.formFiltro.divisiones.value;
      }else if(self.formFiltro.divisiones.value.length === 0 && self.comboDivisiones.length === 1){
        var param_divisiones = self.comboDivisiones[0].id;
      }else{
        var param_divisiones = null;
      }

      //Evaluamos como filtraremos al empleado
      if(self.formFiltro.empleados.value.length === 0 && self.comboEmpleados.length > 1){
        var param_empleados = null;
      }else if(self.formFiltro.empleados.value.length > 0){
        var param_empleados = self.formFiltro.empleados.value;
      }else if(self.formFiltro.empleados.value.length === 0 && self.comboEmpleados.length === 1){
        var param_empleados = self.comboEmpleados[0].id;
      }else{
        var param_empleados = null;
      }

      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        desde: desde,
        concepto: ((self.formFiltro.conceptos.value.length === 0) ? null : self.formFiltro.conceptos.value[0].id),
        division: param_divisiones,
        empleado: param_empleados,
        estatus: self.formFiltro.estatus.value,
        paginar: self.paginador.paginar,
        supervisa: ((self.supervisor === true && self.formFiltro.empleados.value.length === 0) ? true : false),
        supervisarTodo: ((self.supervisarTodo === true && self.formFiltro.divisiones.value.length === 0) ? true : false),
        fecha_desde: self.formFiltro.fechaDesde.value,
        fecha_hasta: self.formFiltro.fechaHasta.value
      };

      axios.get('/buscarHorasNoCargableCargadas', {params: parametros})
      .then(function (response) {

        self.formFiltro.conceptos.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.empleados.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.fechaDesde.disabled = false;
        self.formFiltro.fechaHasta.disabled = (self.formFiltro.fechaHasta.value !== "") ? false : true;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        self.formFiltro.btn.cargar.html = self.formFiltro.btn.cargar.htmlInit;
        self.formFiltro.btn.cargar.disabled = false;

        self.registros = response.data.registros;
        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);

      }).catch(error => {

        self.formFiltro.conceptos.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.empleados.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.fechaDesde.disabled = false;
        self.formFiltro.fechaHasta.disabled = (self.formFiltro.fechaHasta.value !== "") ? false : true;
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

            const fecha_desde = moment(self[formulario].fechaDesde.value);
            const fecha_hasta = moment(self[formulario].fechaHasta.value);

            if(fecha_desde >= fecha_hasta){

              $('#'+formulario+" #fechaHasta").find(".mensaje").html("La Decha Hasta no puede ser menor o igual a la Fecha Desde!").addClass("invalid-feedback");
              $('#'+formulario+" #fechaHasta").find(".form-control").addClass("error");

            }else{

              return true;

            }

          }

        }

      }// Fin del if

    },
    modificarConcepto: function(registro){

      self.formModificarHoras.id = registro.id;
      self.formModificarHoras.concepto.value = {
        id: registro.id_concepto,
        id_usuario: registro.id_usuario,
        descripcion: registro.concepto
      };

      self.formModificarHoras.fechaDesde.value = registro.fecha_desde_utc;
      self.formModificarHoras.fechaHasta.value = registro.fecha_hasta_utc;
      self.formModificarHoras.observacion.value = (registro.observacion === null) ? "" : registro.observacion;
      self.formModificarHoras.estatus.value = registro.id_estatus;

      self.formModificarHoras.concepto.disabled = ((registro.autor === 1 && registro.editar === 1) || (registro.autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.fechaDesde.disabled = ((registro.autor === 1 && registro.editar === 1) || (registro.autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.fechaHasta.disabled = ((registro.autor === 1 && registro.editar === 1) || (registro.autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.observacion.disabled = ((registro.autor === 1 && registro.editar === 1) || (registro.autor === 1 && self.supervisor === true)) ? false : true;
      self.formModificarHoras.estatus.disabled = (self.supervisor === true) ? false : true;
      self.submitModalModificarHora.show = ((registro.autor === 1 && registro.editar === 1) || self.supervisor === true) ? true : false;
      self.confirmarModificarHora.show = ((registro.autor === 1 && registro.editar === 1) || self.supervisor === true) ? true : false;
      self.formModificarHoras.fechaAprobacion = (registro.fecha_aprobacion === null) ? "" : registro.fecha_aprobacion;
      self.formModificarHoras.aprobadoPor = (registro.aprobado_por === null) ? "" : registro.aprobado_por;

      self.fechaHasta("formModificarHoras", registro.fecha_desde_utc, registro.fecha_hasta_utc);

      $("#modal-modificar").modal("show");

    },
    confirmarEliminar: function(){
      //alert(self.formModificarHoras.id)

      self.submitModalModificarHora.show = false;
      self.confirmarModificarHora.show = false;
      self.eliminarModificarHora.show = true;
      self.cancelarEliminarModificarHora.show = true;

      self.alertModificarHora = {
        class : "alert alert-danger text-center",
        message : "¿Estás de acuerdo de eliminar estas horas?",
        show: true
      };

    },
    cancelarEliminarHora: function(){

      self.submitModalModificarHora.show = true;
      self.confirmarModificarHora.show = true;
      self.eliminarModificarHora.show = false;
      self.cancelarEliminarModificarHora.show = false;

      self.alertModificarHora = {
        class : "",
        message : "",
        show: false
      };

    },
    eliminarHora: function(){

      self.eliminarModificarHora.content = '<i class="fas fa-cog fa-spin"></i>';
      self.eliminarModificarHora.disabled = true;
      self.cancelarEliminarModificarHora.disabled = true;

      //Obtenemos valores
      let parametros = {
        id: self.formModificarHoras.id
      }

      axios.post('/eliminarHorasNoCargables', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.respuesta === true){

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

        self.eliminarModificarHora.content = 'Si, estoy de acuerdo en eliminarlas';
        self.eliminarModificarHora.disabled = false;
        self.cancelarEliminarModificarHora.disabled = false;

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

        setTimeout(function(){

          self.alertModificarHora = {
            class : "alert alert-danger text-center",
            message : "¿Estás de acuerdo de eliminar estas horas?",
            show: true
          };

        }, 3000);

      });

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
          id_usuario: self.formModificarHoras.concepto.value.id_usuario,
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
        self.confirmarModificarHora.disabled = true;

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
          self.formModificarHoras.fechaDesde.disabled = false;
          self.formModificarHoras.fechaHasta.disabled = false;
          self.formModificarHoras.observacion.disabled = false;
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
