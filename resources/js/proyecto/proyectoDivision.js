require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;

Vue.use(VueTheMask);
Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(VueNumeric);
Vue.use(BootstrapVue);

new Vue({

  el: '#proyectoDivision',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alert:{
      message: "",
      mostrar: false
    },
    comboEstatus: [],
    form: {
      btn: {
        filtrar: {
          disabled: false,
          html: "",
          htmlInit: "Buscar Proyecto",
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
      estatus: {
        disabled: true,
        value: ""
      },
      horas:{
        asignar: false,
        disabled: true,
        value: 0
      },
      horasC:{
        asignar: false,
        disabled: true,
        value: 0
      },
      analistas: {
        disabled: true,
        validar: false,
        value: []
      },
      mostrar: false
    },
    detalleDproyecto: {
      error: false,
      data: []
    },
    detalleAproyecto: {
      error: false,
      data: []
    },
    detalleAsigproyecto: {
      error: false,
      data: []
    },
    detalleAnalista: {
      error: false,
      data: []
    },
    permisoActualizar: false,
    proyectosD: [],
    proyectos: [],
    proyectoBusqueda: [],
    horasComparar: [],
    horas_cargadas: 0,
    diferencia: 0,
    permisoVer: false,
    permisoCrear: false,
    loading: true
  },
  beforeCreate: function(){

    self = this;

    axios.get('/asignarProyectos')
    .then(function (response) {

      if(response.status === 200){

        self.comboEstatus = response.data.estatus;
        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.estatus.disabled = false;
        self.form.analistas.disabled = false;
        self.form.mostrar = true;
        self.form.btn.filtrar.html = self.form.btn.filtrar.htmlInit;

        self.proyectosD = response.data.proyectos;
        for (var i = 0; i < self.proyectosD.length; i++) {
          if(self.proyectosD[i].permiso > 0 && self.proyectosD[i].id_estatus === 1){
            self.proyectos = self.proyectos.concat(self.proyectosD[i]);
            self.permisoCrear = true;
          }
          if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2 || self.proyectosD[i].permiso === 3) {
            self.permisoActualizar = true; 
          }
          if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2) {
            self.permisoVer = true;
          }
        }
        if (Object.entries(self.proyectos).length === 0) {
          var message = "No posee proyectos asignados";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };
        }
        self.loading = false;

      }else{

        var message = "No posee proyectos asignados";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        throw "error";

      }

    })
    .catch(error => {
      self.alert.mostrar = true;
      self.alertForm = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

      self.loading = false;

    });

  },
  created: function () {},
  mounted: function () {

    $('#modal-detalle-Dproyecto').on('hidden.bs.modal', function () {

      self.detalleDproyecto.data = [];
      self.detalleDproyecto.error = false;
      self.detalleAproyecto.data = [];
      self.detalleAproyecto.error = false;

    });
    $('#modal-asignar-Aproyecto').on('hidden.bs.modal', function () {

      self.detalleAsigproyecto.data = [];
      self.detalleAsigproyecto.error = false;
      self.detalleAnalista.data = [];
      self.detalleAnalista.error = false;


    });
  },
  updated: function () {},
  methods:{

    asignarHoras: function(valor){

      self.form.horas.asignar = (valor.length > 0) ? true : false;

      if(!self.form.horas.asignar){
        self.form.horas.value = 0;
        $("#horas").parent().find(".mensaje").html("").removeClass("invalid-feedback");
        $("#horas").removeClass("error");
      }

    },

    formatoHoraAsignada: function(input){

      let regex = /^\d+$/;

      if(!regex.test(input.key)){
        input.preventDefault();
        self.horasTotales();
      }

      $("#horas").parent().find(".mensaje").html("").removeClass("invalid-feedback");
      $("#horas").removeClass("error");

    },
    horasTotales: function(){

      var total = 0;

      $(".hora-asignada").each(function(index,item){
        let hora = ($(item).val().trim() === "") ? 0 : parseInt($(item).val());
        total = parseInt(total) + hora;
      });

      self.form.horas.value = total;

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
    keyboard: function(e){

      if (e.keyCode === 13){
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/proyectoDivision";
    },

    buscar: function(){

      self.permisoActualizar = false;
      self.permisoCrear = false;
      self.permisoVer = false;
      self.alert.mostrar = false;
      self.form.descripcion.disabled = true;
      self.form.cliente.disabled = true;
      self.form.estatus.disabled = true;
      self.form.btn.filtrar.html = self.form.btn.filtrar.htmlLoading;
      self.form.btn.filtrar.disabled = true;

      let parametros = {
        cliente: self.form.cliente.value,
        proyecto: self.form.descripcion.value,
        estatus: self.form.estatus.value,
      };

      axios.get('/buscardiviProyectos', {params: parametros})
      .then(function (response) {

        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.estatus.disabled = false;
        self.form.btn.filtrar.html = self.form.btn.filtrar.htmlInit;
        self.form.btn.filtrar.disabled = false

        self.proyectoBusqueda = response.data.proyectoBusqueda;
        self.proyectosD = response.data.proyectos;
        self.proyectos = [];

        for (var i = 0; i < self.proyectosD.length; i++) {
          for (var j = 0; j < self.proyectoBusqueda.length; j++) {
            if (self.proyectosD[i].id_proyecto == self.proyectoBusqueda[j].id_proyecto && self.proyectoBusqueda[j].id_estatus === 1) {
              if(self.proyectosD[i].permiso > 0){
                self.proyectos = self.proyectos.concat(self.proyectosD[i]);
                self.permisoCrear = true;
              }
              if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2 || self.proyectosD[i].permiso === 3) {
                self.permisoActualizar = true; 
              }
              if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2) {
                self.permisoVer = true;
              }
            }
            if (self.proyectosD[i].id_proyecto == self.proyectoBusqueda[j].id_proyecto && self.proyectoBusqueda[j].id_estatus === 2) {
              if(self.proyectosD[i].permiso > 0){
                self.proyectos = self.proyectos.concat(self.proyectosD[i]);
                self.permisoCrear = true;
              }
              if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2) {
                self.permisoVer = true;
              }
            }            
          }
        }

      }).catch(error => {

        self.alert.mostrar = true;
        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.estatus.disabled = false;
        self.form.btn.filtrar.html = self.form.btn.filtrar.htmlInit;
        self.form.btn.filtrar.disabled = false;

      });

    },

    mostrarDetalleDivProyecto: function(idDproyecto,e){

      self.detalleDproyecto.error = false;
      self.horas_cargadas = 0;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idDproyecto: idDproyecto
      };

      axios.get('/DetalleDivProyecto', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleDproyecto.data = response.data.infoDproyecto;
          self.detalleAproyecto.data = response.data.infoAproyecto;
          for (var i = 0; i < self.detalleAproyecto.data.length; i++) {
            if (self.detalleAproyecto.data[i].horas_cargadas != null) {
              self.horas_cargadas = parseFloat(self.detalleAproyecto.data[i].horas_cargadas) + self.horas_cargadas;
            }
          }

          $('#modal-detalle-Dproyecto').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleDproyecto.error = true;
        $('#modal-detalle-Dproyecto').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    },

    asignarAnalistaProyecto: function(idDproyecto,e){

      self.detalleAsigproyecto.error = false;
      $(e.target).removeClass("far fa-edit").addClass("fa-cog fa-spin");

      let parametros = {
        idDproyecto: idDproyecto
      };

      axios.get('/detalleAnalistaProyecto', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleAnalista.data = response.data.analistas;
          self.detalleAsigproyecto.data = response.data.proyecto;
          self.form.horas.value = 0;
          for (var i = 0; i < self.detalleAnalista.data.length; i++) {
            self.horasComparar[i] = self.detalleAnalista.data[i].horas_asignadas;
            if (self.detalleAnalista.data[i].horas_asignadas === null) {
              self.horasComparar[i] = 0;
            }
          }
          for (var i = 0; i < self.horasComparar.length; i++) {
            self.form.horas.value = self.horasComparar[i] + self.form.horas.value;
          }

          $('#modal-asignar-Aproyecto').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("far fa-edit");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleAsigproyecto.error = true;
        $('#modal-asignar-Aproyecto').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("far fa-edit");

      });

    },

    estados: function(analista,idAnaProy,idDproyecto,id_proyecto_division,e){

      if(idAnaProy == null){

        let parametros = {
          estado: 1,
          idDproyecto: idDproyecto,
          idUsuario: analista,
          id_proyecto_division: id_proyecto_division,
        };

        axios.get('/agregarAnalistaProy', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.detalleAnalista.data = response.data.analistas;
            self.detalleAsigproyecto.data = response.data.proyecto;

            for (var i = 0; i < self.detalleAnalista.data.length; i++) {
            self.horasComparar[i] = self.detalleAnalista.data[i].horas_asignadas;
            if (self.detalleAnalista.data[i].horas_asignadas === null) {
              self.horasComparar[i] = 0;
            }

          }
            self.actualizar();
          }
        })

      }else{
        let parametros = {
          idAnaProy: idAnaProy,
          idDproyecto: idDproyecto,
        };

        axios.get('/modAnalistaProy', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.detalleAnalista.data = response.data.analistas;
            self.detalleAsigproyecto.data = response.data.proyecto;

            for (var i = 0; i < self.detalleAnalista.data.length; i++) {
            self.horasComparar[i] = self.detalleAnalista.data[i].horas_asignadas;
            if (self.detalleAnalista.data[i].horas_asignadas === null) {
              self.horasComparar[i] = 0;
            }

          }
            self.actualizar();
      }else{
        throw response.data;
      }
    })
      }

    },

    asigna: function(analista,idAnaProy,idDproyecto,horas_contratadas,e){

      var total2 = [];
      self.alertForm = {
          class : "",
          message : "",
          show: false
        };

      if (self.form.horas.value > horas_contratadas) {
        var message = "Se a sobrepasado el maximo de horas que puede asignar.";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };
      }else{
        $(".hora-asignada").each(function(index,item){
        let hora = ($(item).val().trim() === "") ? 0 : parseInt($(item).val());
        total2.push({hora});
      });

      for (var i = 0; i < total2.length; i++) {
          total2[i] = total2[i]["hora"];

      }

      let parametros = {
        idAnaProy: idAnaProy,
        idDproyecto: idDproyecto,
        horas_asignadas: total2,
        horasComparar: self.horasComparar,

      };

      axios.get('/asigHorasAnalistaProy', {params: parametros})
      .then(function (response) {
        if(response.status === 200 && response.data.response === true){
          self.detalleAnalista.data = response.data.analistas;
          self.detalleAsigproyecto.data = response.data.proyecto;

        self.actualizar();
        }else{
          throw response.data;
        }
      })
      }
    },

    actualizar: function(){

      let parametros = {
        cliente: self.form.cliente.value,
        proyecto: self.form.descripcion.value,
        estatus: self.form.estatus.value,
      };

      axios.get('/buscardiviProyectos', {params: parametros})
      .then(function (response) {

        self.proyectoBusqueda = response.data.proyectoBusqueda;
        self.proyectosD = response.data.proyectos;
        self.proyectos = [];

        for (var i = 0; i < self.proyectosD.length; i++) {
          for (var j = 0; j < self.proyectoBusqueda.length; j++) {
            if (self.proyectosD[i].id_proyecto == self.proyectoBusqueda[j].id_proyecto && self.proyectoBusqueda[j].id_estatus === 1) {
              if(self.proyectosD[i].permiso > 0){
                self.proyectos = self.proyectos.concat(self.proyectosD[i]);
                self.permisoCrear = true;
              }
            }
            if (self.proyectosD[i].id_proyecto == self.proyectoBusqueda[j].id_proyecto && self.proyectoBusqueda[j].id_estatus === 2) {
              if(self.proyectosD[i].permiso > 0){
                self.proyectos = self.proyectos.concat(self.proyectosD[i]);
              }
              if (self.proyectosD[i].permiso === 1 || self.proyectosD[i].permiso === 2) {
              }
            }            
          }
        }

      }).catch(error => {

        self.alert.mostrar = true;
        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.estatus.disabled = false;
        self.form.btn.filtrar.html = self.form.btn.filtrar.htmlInit;
        self.form.btn.filtrar.disabled = false;

      });

    },

    formCargarHoras: function(idProyecto,idUsuario,e){


      let parametros = {
        idProyecto: idProyecto,
        idUsuario: idUsuario
      };

      axios.get('/formCargarHoras', {params: parametros})

    }

  }// Fin methods

});
