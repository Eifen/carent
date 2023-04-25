require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
window.zenscroll = require('zenscroll');
window.$ = require('jquery');
var self;

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

new Vue({

  el: '#buscarCliente',
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
        value: ""
      },
      select: {
        disabled:false,
        value: ""
      }
    },
    loading: true,
    clientes: {
      mostrar: false,
      registros: []
    },
    detalleCliente: {
      error: false,
      data: []
    },
    permisoActualizar: false
  },
  beforeCreate: function(){

    self = this;

  },
  created: function () {

  },
  mounted: function () {

    $('#modal-detalle-cliente').on('hidden.bs.modal', function () {

      self.detalleCliente.data = [];
      self.detalleCliente.error = false;

    });

    self.loading = false;

  },
  updated: function () {},
  methods:{
    buscar: function(e){

      self.alert.mostrar = false;

      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;
        //Obtenemos los valores
        let parametros = {
          buscarPor: self.formSearch.select.value,
          dato: self.formSearch.inputSearch.value
        };
        //Se utiliza el metodo get y se envia al clienteController y se envian los parametros
        axios.get('/buscarClientes', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.clientes.mostrar = true;
            self.clientes.registros = response.data.clientes;
            self.permisoActualizar = response.data.permisoActualizar;

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.clientes.registros = [];
          self.clientes.mostrar = false;

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
      let valoresPermitidos = [1,2,3,4];

      self.clientes.mostrar = false;
      self.clientes.registros = [];

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
        self.clientes.registros = [];
        self.clientes.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    mostrarDetalleCliente: function(idCliente,e){

      self.detalleCliente.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");
      // Obtenemos los valores
      let parametros = {
        idCliente: idCliente
      };
      //Se utiliza el metodo get y se envia al clienteController y se envian los parametros
      axios.get('/detalleCliente', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleCliente.data = response.data.info;

          $('#modal-detalle-cliente').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleCliente.error = true;
        $('#modal-detalle-cliente').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    }
  }// Fin methods

});
