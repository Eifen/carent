require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
window.zenscroll = require('zenscroll');
window.$ = require('jquery');
var self;

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
    crUsuario: {
      checked: false,
      menu: 2,
      c: 0
    },
    coUsuario: {
      checked: false,
      menu: 3,
      r: 0,
      u: 0
    },
    modUsuario: {
      checked: false,
      menu: 3,
      r: 0,
      u: 0
    },
    crCliente: {
      checked: false,
      menu: 5,
      c: 0
    },
    coCliente: {
      checked: false,
      menu: 6,
      r: 0,
      u: 0
    },
    modCliente: {
      checked: false,
      menu: 6,
      r: 0,
      u: 0
    },
    crProyecto: {
      checked: false,
      menu: 9,
      c: 0
    },
    coProyecto: {
      checked: false,
      menu: 10,
      r: 0,
      u: 0,
    },
    modProyecto: {
      checked: false,
      menu: 10,
      r: 0,
      u: 0
    },
    crFact: {
      checked: false,
      menu: 7,
      c: 0,
      r: 0,
      u: 0,
    },
    verFact: {
      checked: false,
      menu: 7,
      c: 0,
      r: 0,
      u: 0,
    },
    modFact: {
      checked: false,
      menu: 7,
      c: 0,
      r: 0,
      u: 0,
    },
    verAsigna: {
      checked: false,
      menu: 11,
      c: 0,
      r: 0,
      u: 0,
      d: 0
    },
    modAsigna: {
      checked: false,
      menu: 11,
      c: 0,
      r: 0,
      u: 0,
      d: 0
    },
    caHora: {
      checked: false,
      menu: 11,
      c: 0,
      r: 0,
      u: 0,
      d: 0
    },
    eliHora: {
      checked: false,
      menu: 11,
      c: 0,
      r: 0,
      u: 0,
      d: 0
    },
    conHoraNoC: {
      checked: false,
      menu: 13,
      u: 0,
    },
    carHoraNoC: {
      checked: false,
      menu: 14,
      u: 0,
    },
    usuario: {
      data: ""
    },
    division: {
      data: ""
    },
    cargo: {
      data: ""
    },
    permisoActualizar: false,
    permisoRRHH: false,
    permisoContraloria: false,
    permisoSocio: false,
    permisoEncargado: false,
    permisoSergio: false,
    infoUsuario: [],
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
      self.permisoRRHH = false;
      self.permisoContraloria = false;
      self.permisoSocio = false;
      self.permisoSergio = false;
      self.permisoEncargado = true;
      self.detalleMenu.error = false;
      self.crUsuario.checked = false;
      self.crUsuario.c = 0;
      self.coUsuario.checked = false;
      self.coUsuario.r = 2;
      self.coUsuario.u = 2;
      self.modUsuario.checked = false;
      self.modUsuario.r = 2;
      self.modUsuario.u = 2;
      self.crCliente.checked = false;
      self.crCliente.c = 0;
      self.coCliente.checked = false;
      self.coCliente.r = 2;
      self.coCliente.u = 2;
      self.modCliente.checked = false;
      self.modCliente.r = 2;
      self.modCliente.u = 2;
      self.crProyecto.checked = false;
      self.crProyecto.c = 0;
      self.coProyecto.checked = false;
      self.coProyecto.r = 2;
      self.coProyecto.u = 2;
      self.modProyecto.checked = false;
      self.modProyecto.r = 2;
      self.modProyecto.u = 2;
      self.crFact.checked = false;
      self.crFact.c = 2;
      self.crFact.r = 2;
      self.crFact.u = 2;
      self.verFact.checked = false;
      self.verFact.c = 2;
      self.verFact.r = 2;
      self.verFact.u = 2;
      self.modFact.checked = false;
      self.modFact.c = 2;
      self.modFact.r = 2;
      self.modFact.u = 2;
      self.verAsigna.checked = false;
      self.verAsigna.c = 2;
      self.verAsigna.r = 2;
      self.verAsigna.u = 2;
      self.verAsigna.d = 2;
      self.modAsigna.checked = false;
      self.modAsigna.c = 2;
      self.modAsigna.r = 2;
      self.modAsigna.u = 2;
      self.modAsigna.d = 2;
      self.caHora.checked = false;
      self.caHora.c = 2;
      self.caHora.r = 2;
      self.caHora.u = 2;
      self.caHora.d = 2;
      self.eliHora.checked = false;
      self.eliHora.c = 2;
      self.eliHora.r = 2;
      self.eliHora.u = 2;
      self.eliHora.d = 2;
      self.conHoraNoC.checked = false;
      self.conHoraNoC.u = 0;
      self.carHoraNoC.checked = false;
      self.carHoraNoC.u = 0;

      $(e.target).removeClass("fa-user-edit").addClass("fa-cog fa-spin");

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleMenu', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.usuario.data = response.data.id_usuario;
          self.infoUsuario = response.data.datosUsuario;
          self.division.data = response.data.datosUsuario.id_division;
          if (self.division.data === 7) {
            self.permisoRRHH = true;
          }else if (self.division.data === 10 || self.infoUsuario.codigo === "10863") {
            self.permisoContraloria = true;
          }
          if (self.infoUsuario.codigo === "10863") {
            self.permisoSergio = true
          }
          self.cargo.data = response.data.datosUsuario.id_cargo;
          if (self.cargo.data === 16 || self.cargo.data === 17) {
            self.permisoSocio = true;
            self.permisoEncargado = false;
          }

          self.detalleMenu.data = response.data.info;
          for (var i = 0; i < self.detalleMenu.data.length; i++) {
            if (self.detalleMenu.data[i].id_menu === 2) {
              self.crUsuario.checked = true;
              self.crUsuario.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 3) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coUsuario.checked = true;
                self.coUsuario.r = self.detalleMenu.data[i].R;
                self.coUsuario.u = self.detalleMenu.data[i].U;
                self.modUsuario.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modUsuario.checked = true;
                self.modUsuario.r = self.detalleMenu.data[i].R;
                self.modUsuario.u = self.detalleMenu.data[i].U;
                self.coUsuario.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 5) {
             self.crCliente.checked = true;
             self.crCliente.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 6) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coCliente.checked = true;
                self.coCliente.r = self.detalleMenu.data[i].R;
                self.coCliente.u = self.detalleMenu.data[i].U;
                self.modCliente.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modCliente.checked = true;
                self.modCliente.r = self.detalleMenu.data[i].R;
                self.modCliente.u = self.detalleMenu.data[i].U;
                self.coCliente.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 7) {
              if (self.detalleMenu.data[i].C === 1) {
                self.crFact.checked = true;
                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].R === 1) {
                self.verFact.checked = true;
                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modFact.checked = true;
                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 9) {
             self.crProyecto.checked = true;
             self.crProyecto.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 10) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coProyecto.checked = true;
                self.coProyecto.r = self.detalleMenu.data[i].R;
                self.coProyecto.u = self.detalleMenu.data[i].U;
                self.modProyecto.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modProyecto.checked = true;
                self.modProyecto.r = self.detalleMenu.data[i].R;
                self.modProyecto.u = self.detalleMenu.data[i].U;
                self.coProyecto.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 11) {
              if (self.detalleMenu.data[i].R === 1) {
                self.verAsigna.checked = true;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modAsigna.checked = true;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].C === 1) {
                self.caHora.checked = true;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].D === 1) {
                self.eliHora.checked = true;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 13) {
              if (self.detalleMenu.data[i].U === 1) {
                self.conHoraNoC.checked = true;
                self.conHoraNoC.u = self.detalleMenu.data[i].U;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 14) {
              if (self.detalleMenu.data[i].U === 1) {
                self.carHoraNoC.checked = true;
                self.carHoraNoC.u = self.detalleMenu.data[i].U;
              }
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

    Crear: function(crear, menu, e){

      if(crear === 0){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: 0,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data )
          }else{
            throw response.data;
          }
        })
      }else{

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })

      }

    },

    Consultar: function(ver,modificar1,modificar2,menu,e){

      if((ver === 2) && (modificar1 === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 1,
          U: 0,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(ver === 0){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 1,
          U: modificar2,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((ver === 1) && (modificar2 === 1)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: modificar2,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((ver === 1) && (modificar1 === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    Modificar: function(ver1,modificar,ver2,menu,e){

      if((ver1 === 2) && (modificar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: 1,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(modificar === 0){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: ver2,
          U: 1,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((ver2 === 1) && (modificar === 1)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: ver2,
          U: 0,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((modificar === 1) && (ver1 === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    crFactura: function(crear,leer,modificar,menu,e){

      if (self.crFact.checked === true) {
        self.crFact.checked = false;
      }
      if (self.crFact.checked === false) {
        self.crFact.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2)){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: 0,
          U: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 1) && (leer === 0) && (modificar === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(crear === 1){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: leer,
          U: modificar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(crear === 0){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: leer,
          U: modificar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    verFactura: function(crear,leer,modificar,menu,e){

      if (self.verFact.checked === true) {
        self.verFact.checked = false;
      }
      if (self.verFact.checked === false) {
        self.verFact.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 1,
          U: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 0) && (leer === 1) && (modificar === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(leer === 1){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: 0,
          U: modificar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(leer === 0){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: 1,
          U: modificar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    modFactura: function(crear,leer,modificar,menu,e){

      if (self.modFact.checked === true) {
        self.modFact.checked = false;
      }
      if (self.modFact.checked === false) {
        self.modFact.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: 1,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 0) && (leer === 0) && (modificar === 1)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(modificar === 1){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(modificar === 0){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: 1,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    verAsignar: function(crear,leer,modificar,eliminar,menu,e){

      if (self.verAsigna.checked === true) {
        self.verAsigna.checked = false;
      }
      if (self.verAsigna.checked === false) {
        self.verAsigna.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2) && (eliminar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 1,
          U: 0,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 0) && (leer === 1) && (modificar === 0) && (eliminar === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(leer === 1){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: 0,
          U: modificar,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(leer === 0){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: 1,
          U: modificar,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    modAsignar: function(crear,leer,modificar,eliminar,menu,e){

      if (self.modAsigna.checked === true) {
        self.modAsigna.checked = false;
      }
      if (self.modAsigna.checked === false) {
        self.modAsigna.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2) && (eliminar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: 1,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 0) && (leer === 0) && (modificar === 1) && (eliminar === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(modificar === 1){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: 0,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(modificar === 0){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: 1,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    caHoras: function(crear,leer,modificar,eliminar,menu,e){

      if (self.caHora.checked === true) {
        self.caHora.checked = false;
      }
      if (self.caHora.checked === false) {
        self.caHora.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2) && (eliminar === 2)){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: 0,
          U: 0,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 1) && (leer === 0) && (modificar === 0) && (eliminar === 0)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(crear === 1){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: leer,
          U: modificar,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(crear === 0){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: leer,
          U: modificar,
          D: eliminar,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    eliHoras: function(crear,leer,modificar,eliminar,menu,e){

      if (self.eliHora.checked === true) {
        self.eliHora.checked = false;
      }
      if (self.eliHora.checked === false) {
        self.eliHora.checked = true;
      }

      if((crear === 2) && (leer === 2) && (modificar === 2) && (eliminar === 2)){

        let parametros = {
          menuCr: menu,
          C: 0,
          R: 0,
          U: 0,
          D: 1,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if((crear === 0) && (leer === 0) && (modificar === 0) && (eliminar === 1)){

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(eliminar === 1){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: modificar,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else if(eliminar === 0){

        let parametros = {
          menuCr: menu,
          C: crear,
          R: leer,
          U: modificar,
          D: 1,
          idUsuario: self.usuario.data
        };
        axios.get('/modificarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    conHorasNoC: function(modificar, menu, e){

      if (self.conHoraNoC.checked === true) {
        self.conHoraNoC.checked = false;
      }
      if (self.conHoraNoC.checked === false) {
        self.conHoraNoC.checked = true;
      }

      if(modificar === 0){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: 1,
          U: 1,
          D: 1,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else{

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    carHorasNoC: function(modificar, menu, e){

      if (self.carHoraNoC.checked === true) {
        self.carHoraNoC.checked = false;
      }
      if (self.carHoraNoC.checked === false) {
        self.carHoraNoC.checked = true;
      }

      if(modificar === 0){

        let parametros = {
          menuCr: menu,
          C: 1,
          R: 1,
          U: 1,
          D: 0,
          idUsuario: self.usuario.data
        };
        axios.get('/agregarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }else{

        let parametros = {
          menuCr: menu,
          idUsuario: self.usuario.data
        };
        axios.get('/quitarMenUsu', {params: parametros})
        .then(function (response) {
          if(response.status === 200 && response.data.response === true){
            self.actualizarDetalleMenu(self.usuario.data)
          }else{
            throw response.data;
          }
        })
      }
    },

    actualizarDetalleMenu: function(idUsuario){

      self.crUsuario.checked = false;
      self.crUsuario.c = 0;
      self.coUsuario.checked = false;
      self.coUsuario.r = 2;
      self.coUsuario.u = 2;
      self.modUsuario.checked = false;
      self.modUsuario.r = 2;
      self.modUsuario.u = 2;
      self.crCliente.checked = false;
      self.crCliente.c = 0;
      self.coCliente.checked = false;
      self.coCliente.r = 2;
      self.coCliente.u = 2;
      self.modCliente.checked = false;
      self.modCliente.r = 2;
      self.modCliente.u = 2;
      self.crProyecto.checked = false;
      self.crProyecto.c = 0;
      self.coProyecto.checked = false;
      self.coProyecto.r = 2;
      self.coProyecto.u = 2;
      self.modProyecto.checked = false;
      self.modProyecto.r = 2;
      self.modProyecto.u = 2;

      self.crFact.c = 2;
      self.crFact.r = 2;
      self.crFact.u = 2;

      self.verFact.c = 2;
      self.verFact.r = 2;
      self.verFact.u = 2;

      self.modFact.c = 2;
      self.modFact.r = 2;
      self.modFact.u = 2;

      self.verAsigna.c = 2;
      self.verAsigna.r = 2;
      self.verAsigna.u = 2;
      self.verAsigna.d = 2;

      self.modAsigna.c = 2;
      self.modAsigna.r = 2;
      self.modAsigna.u = 2;
      self.modAsigna.d = 2;

      self.caHora.c = 2;
      self.caHora.r = 2;
      self.caHora.u = 2;
      self.caHora.d = 2;

      self.eliHora.c = 2;
      self.eliHora.r = 2;
      self.eliHora.u = 2;
      self.eliHora.d = 2;

      self.conHoraNoC.u = 0;

      self.carHoraNoC.u = 0;

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleMenu', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.usuario.data = response.data.id_usuario;
          self.infoUsuario = response.data.datosUsuario;

          self.detalleMenu.data = response.data.info;
          for (var i = 0; i < self.detalleMenu.data.length; i++) {
            if (self.detalleMenu.data[i].id_menu === 2) {
              self.crUsuario.checked = true;
              self.crUsuario.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 3) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coUsuario.checked = true;
                self.coUsuario.r = self.detalleMenu.data[i].R;
                self.coUsuario.u = self.detalleMenu.data[i].U;
                self.modUsuario.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modUsuario.checked = true;
                self.modUsuario.r = self.detalleMenu.data[i].R;
                self.modUsuario.u = self.detalleMenu.data[i].U;
                self.coUsuario.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 5) {
             self.crCliente.checked = true;
             self.crCliente.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 6) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coCliente.checked = true;
                self.coCliente.r = self.detalleMenu.data[i].R;
                self.coCliente.u = self.detalleMenu.data[i].U;
                self.modCliente.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modCliente.checked = true;
                self.modCliente.r = self.detalleMenu.data[i].R;
                self.modCliente.u = self.detalleMenu.data[i].U;
                self.coCliente.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 7) {
              if (self.detalleMenu.data[i].C === 1) {

                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].R === 1) {

                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {

                self.modFact.c = self.detalleMenu.data[i].C;
                self.modFact.r = self.detalleMenu.data[i].R;
                self.modFact.u = self.detalleMenu.data[i].U;
                self.verFact.c = self.detalleMenu.data[i].C;
                self.verFact.r = self.detalleMenu.data[i].R;
                self.verFact.u = self.detalleMenu.data[i].U;
                self.crFact.c = self.detalleMenu.data[i].C;
                self.crFact.r = self.detalleMenu.data[i].R;
                self.crFact.u = self.detalleMenu.data[i].U;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 9) {
             self.crProyecto.checked = true;
             self.crProyecto.c = 1;
            }
            if (self.detalleMenu.data[i].id_menu === 10) {
              if (self.detalleMenu.data[i].R === 1) {
                self.coProyecto.checked = true;
                self.coProyecto.r = self.detalleMenu.data[i].R;
                self.coProyecto.u = self.detalleMenu.data[i].U;
                self.modProyecto.u = self.detalleMenu.data[i].U;
              }
              if (self.detalleMenu.data[i].U === 1) {
                self.modProyecto.checked = true;
                self.modProyecto.r = self.detalleMenu.data[i].R;
                self.modProyecto.u = self.detalleMenu.data[i].U;
                self.coProyecto.r = self.detalleMenu.data[i].R;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 11) {
              if (self.detalleMenu.data[i].R === 1) {

                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].U === 1) {

                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].C === 1) {

                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
              }
              if (self.detalleMenu.data[i].D === 1) {

                self.eliHora.c = self.detalleMenu.data[i].C;
                self.eliHora.r = self.detalleMenu.data[i].R;
                self.eliHora.u = self.detalleMenu.data[i].U;
                self.eliHora.d = self.detalleMenu.data[i].D;
                self.caHora.c = self.detalleMenu.data[i].C;
                self.caHora.r = self.detalleMenu.data[i].R;
                self.caHora.u = self.detalleMenu.data[i].U;
                self.caHora.d = self.detalleMenu.data[i].D;
                self.modAsigna.c = self.detalleMenu.data[i].C;
                self.modAsigna.r = self.detalleMenu.data[i].R;
                self.modAsigna.u = self.detalleMenu.data[i].U;
                self.modAsigna.d = self.detalleMenu.data[i].D;
                self.verAsigna.c = self.detalleMenu.data[i].C;
                self.verAsigna.r = self.detalleMenu.data[i].R;
                self.verAsigna.u = self.detalleMenu.data[i].U;
                self.verAsigna.d = self.detalleMenu.data[i].D;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 13) {
              if (self.detalleMenu.data[i].U === 1) {
                self.conHoraNoC.u = self.detalleMenu.data[i].U;
              }
            }
            if (self.detalleMenu.data[i].id_menu === 14) {
              if (self.detalleMenu.data[i].U === 1) {

                self.carHoraNoC.u = self.detalleMenu.data[i].U;
              }
            }
          }

        }else{

          throw response.data;

        }

      })
    },

  }// Fin methods

});
