require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
window.zenscroll = require('zenscroll');
window.$ = require('jquery');
var self;

Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(BootstrapVue);

new Vue({

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
    loading: true,
    selectMenus: {
      value:[]
    },
    usuarios: {
      mostrar: false,
      registros: []
    },
    detalleUsuario: {
      error: false,
      data: []
    },
    detalleMenu: {
      error: false,
      data: []
    },
    usuario: {
      data: ""
    },
    permisoActualizar: false,
    comboMenus: [],
    infoUsuario: [],
    comboMenus: [
      {
        menus: "Usuarios",
        value:[]
      },
      {
        menus: "Clientes",
        value:[]
      },
      {
        menus: "Proyectos",
        value:[]
      },
      {
        menus: "Horas no Cargables",
        value:[]
      },
    ],
    comboMenu: [],
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

    $('#modal-asignar-menu').on('hidden.bs.modal', function () {

      self.detalleMenu.data = [];
      self.detalleMenu.error = false;

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

    },
    mostrarDetalleMenu: function(idUsuario,e){

      self.infoUsuario = [];
      self.detalleUsuario.error = false;
      $(e.target).removeClass("fa-user-edit").addClass("fa-cog fa-spin");

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleMenu', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.usuario.data = response.data.id_usuario;
          self.infoUsuario = response.data.datosUsuario;
          self.detalleMenu.data = response.data.info;
          var u = 0;
          var c = 0;
          var p = 0;
          var h = 0;
            for (var i = 0; i < response.data.infoMenus.length; i++) {
              if (response.data.infoMenus[i].id_menu_padre === 1) {
                self.comboMenus[0].value[u] = response.data.infoMenus[i];
                u++;
              }
              if (response.data.infoMenus[i].id_menu_padre === 4) {
                self.comboMenus[1].value[c] = response.data.infoMenus[i];
                c++;
              }
              if (response.data.infoMenus[i].id_menu_padre === 8) {
                self.comboMenus[2].value[p] = response.data.infoMenus[i];
                p++;
              }
              if (response.data.infoMenus[i].id_menu_padre === 12) {
                self.comboMenus[3].value[h] = response.data.infoMenus[i];
                h++;
              }
            }


          $('#modal-asignar-menu').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-user-edit");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleUsuario.error = true;
        $('#modal-asignar-menu').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-user-edit");

      });

    },

  }// Fin methods

});
