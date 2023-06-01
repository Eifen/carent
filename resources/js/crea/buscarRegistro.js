import 'bootstrap';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import axios from 'axios';
window.axios = axios;
import AutoNumeric from 'autonumeric';
window.AutoNumeric = AutoNumeric;
import zenscroll from 'zenscroll';
window.zenscroll = zenscroll;
import $ from 'jquery';
window.$ = $;
var self;

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

new Vue({

  el: '#buscarRegistro',
  data: {
    alert:{
      message: "",
      mostrar: false
    },
    formSearch: {
      submit: {
        disabled: true,
        html: "Consultar"
      },
      inputSearch: {
        disabled: true,
        value: "l"
      },
      select: {
        disabled:false,
        value: ""
      }
    },
    registros: {
      mostrar: false,
      registros: []
    },
    detalleRegistro: {
      error: false,
      data: []
    }
  },
  beforeCreate: function(){

    self = this;

  },
  created: function () {

  },
  mounted: function () {

    $('#modal-detalle-registro').on('hidden.bs.modal', function () {

      self.detalleRegistro.data = [];
      self.detalleRegistro.error = false;

    });

  },
  updated: function () {},
  methods:{
    Crear: function(e){

      self.alert.mostrar = false;

      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;

        let parametros = {
          buscarPor: self.formSearch.select.value,
        };

        axios.get('/buscarRegistro', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.registros.mostrar = true;
            self.registros.registros = response.data.registros;

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.registros.registros = [];
          self.registros.mostrar = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert.message = message;

        });

      }else{

        $(".inputSearch").parent().find(".mensaje").html("Campo requerido").addClass("invalid-feedback");
        $(".inputSearch").addClass("error");
        zenscroll.toY($(".inputSearch").offset().top - 100);

      }

    },
    tipoFiltro: function(e){

      let opcion = parseInt(e.target.value);
      let valoresPermitidos = [1,2];

      self.registros.mostrar = false;
      self.registros.registros = [];

      if(valoresPermitidos.includes(opcion)){
        self.formSearch.inputSearch.disabled = false;
        self.formSearch.submit.disabled = false;
      }else{
        self.formSearch.inputSearch.disabled = true;
        self.formSearch.submit.disabled = true;
      }

    },
    evaluarCampo: function(id, e){

      if(e.target.type === 'text'){
        self.formSearch[id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      if(id === "inputSearch" && self.formSearch["inputSearch"].value.trim() === ""){
        self.registros.registros = [];
        self.registros.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    mostrardetalleRegistro: function(idRegistro,e){

      self.detalleRegistro.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        buscarPor: self.formSearch.select.value,
        idRegistro: idRegistro
      };

      axios.get('/detalleRegistro', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleRegistro.data = response.data.info;

          $('#modal-detalle-registro').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleRegistro.error = true;
        $('#modal-detalle-registro').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    },
  }// Fin methods
});
