require('bootstrap');
window.Vue = require('vue');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
window.zenscroll = require('zenscroll');
window.$ = require('jquery');
var self;

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);

var app = new Vue({

  el: '#buscarUsuario',
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
    usuarios: {
      mostrar: false,
      registros: []
    },
    detalleUsuario: {
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

    $('#modal-detalle-usuario').on('hidden.bs.modal', function () {

      self.detalleUsuario.data = [];
      self.detalleUsuario.error = false;

    });

  },
  updated: function () {},
  methods:{
    buscar: function(e){

      self.alert.mostrar = false;

      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;

        let parametros = {
          buscarPor: self.formSearch.select.value,
          dato: self.formSearch.inputSearch.value
        };

        axios.get('/buscarUsuarios', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.usuarios.mostrar = true;
            self.usuarios.registros = response.data.usuarios;
            self.permisoActualizar = response.data.permisoActualizar;

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Consultar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.usuarios.registros = [];
          self.usuarios.mostrar = false;

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
      let valoresPermitidos = [1,2,3,4,5];

      self.usuarios.mostrar = false;
      self.usuarios.registros = [];

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
        self.usuarios.registros = [];
        self.usuarios.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    mostrarDetalleUsuario: function(idUsuario,e){

      self.detalleUsuario.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleUsuario', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleUsuario.data = response.data.info;

          $('#modal-detalle-usuario').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleUsuario.error = true;
        $('#modal-detalle-usuario').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    }
  }// Fin methods

});
