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
          htmlInit: "buscar proyecto",
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
    permisoActualizar: false,
    proyectos: []
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
        self.form.mostrar = true;
        self.form.btn.filtrar.html = self.form.btn.filtrar.htmlInit;

        self.proyectos = response.data.proyectos;
        self.permisoActualizar = response.data.permisoActualizar;

      }else{

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
  },
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
      window.location.href = "/proyectoDivision";
    },
    limpiarFiltro: function(){

      self.form.descripcion.value = "";
      self.form.cliente.value = "";
      self.form.estatus.value = "";
      self.buscar();

    },
    buscar: function(){

      permisoActualizar: false,
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
        self.form.btn.filtrar.disabled = false;
        self.permisoActualizar = response.data.permisoActualizar;

        self.proyectos = response.data.proyectos;

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
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idDproyecto: idDproyecto
      };

      axios.get('/DetalleDivProyecto', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleDproyecto.data = response.data.infoDproyecto;
          self.detalleAproyecto.data = response.data.infoAproyecto;

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

    }

  }// Fin methods

});
