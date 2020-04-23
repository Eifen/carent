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

  el: '#asignarAnalista',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    comboEstatus: [],
    form: {
      estado: {
        disabled: false,
        value: ""
      },
      mostrar: false
    },
    analistas: [],
    proyectos: []
  },
  beforeCreate: function(){

    self = this;

    axios.get('/detalleAnalistaProyecto')
    .then(function (response) {

      if(response.status === 200){

        self.analistas = response.data.analistas;
        self.proyectos = response.data.proyecto;
        self.form.mostrar = true;
        
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
      window.location.href = "/asignacionProyecto";
    },
    limpiarFiltro: function(){

      self.form.descripcion.value = "";
      self.form.cliente.value = "";
      self.buscar();

    },
    estados: function(analista,idAnaProy,proyecto,e){

      if(idAnaProy == null){
      
        let parametros = {
          estado: 1,
          proyecto: proyecto,
          idUsuario: analista
        };

        axios.get('/agregarAnalistaProy', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
          }else{
            throw response.data;
          }
        })
        axios.get('/detalleAnalistaProyecto')
        .then(function (response) {

          if(response.status === 200){

            self.analistas = response.data.analistas;
            self.proyecto = response.data.proyecto;
            self.form.mostrar = true;
        
          }
        })

      }else{
        let parametros = {
          idAnaProy: idAnaProy,
        };

        axios.get('/modAnalistaProy', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){

      }else{
        throw response.data;
      }
    })
      }
      
    }


  }// Fin methods

});