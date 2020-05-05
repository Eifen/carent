require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
import Datepicker from 'vuejs-datepicker';
import {es} from 'vuejs-datepicker/dist/locale';
export default {
    components: {
        Datepicker
    }
};
var self;
Vue.use(VueTheMask);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
var app = new Vue({

  el: '#cargarHoras',
  components: {
        Datepicker
    },
  data: {

    es:es,
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alert:{
      message: "",
      mostrar: false
    },
    form: {
      fecha:{
        disabled: false,
        value: ""
      },
      descripcion:{
        disabled: false,
        value: ""
      },
      horas_trabajadas:{
        disabled: false,
        value: ""
      },
      fechaM:{
        disabled: false,
        value: ""
      },
      descripcionM:{
        disabled: false,
        value: ""
      },
      horas_trabajadasM:{
        disabled: false,
        value: ""
      },
      horas_trabajadasA:{
        disabled: false,
        value: ""
      },
      btn: {
        Crear: {
          disabled: false,
          html: "",
          htmlInit: "Cargar Horas",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
        Modificar: {
          disabled: false,
          html: "",
          htmlInit: "Modificar Horas Cargadas",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
        Eliminar: {
          disabled: false,
          html: "",
          htmlInit: "Eliminar Horas Cargadas",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        }
      },
      mostrar: false
      },
    modHorasCargadas: {
      error: false,
      data: []
    },
    eliHorasCargadas: {
      error: false,
    },
    infoProyAnalista: [],
    infoHorasCargadas: [],
    infoEliHorasCargadas: [],
    permisoActualizar: false,
    horas_cargadas: 0,
  },
  beforeCreate: function(){

    self = this;

    axios.get('/datosHorasProyecto')
    .then(function (response) {

      if(response.status === 200){

        self.infoProyAnalista = response.data.infoProyAnalista;
        self.infoHorasCargadas = response.data.infoHorasCargadas;
        self.form.mostrar = true;
        self.form.btn.Crear.html = self.form.btn.Crear.htmlInit;
        self.permisoActualizar = response.data.permisoActualizar;
        self.permisoEliminar = response.data.permisoEliminar;
        self.permisoCrear = response.data.permisoCrear;

        for (var i = 0; i < self.infoHorasCargadas.length; i++) {
              self.horas_cargadas = self.infoHorasCargadas[i].horas_trabajadas + self.horas_cargadas;          
        }
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
  created: function () {
  },
  mounted: function () {

    new AutoNumeric('#horas_trabajadasM', {
      decimalPlaces: 0,
      maximumValue: 23,
      minimumValue: 1,
    });  

     

    $('#modal-detalle-Hcargadas').on('hidden.bs.modal', function () {

      self.modHorasCargadas.data = [];
      self.modHorasCargadas.error = false;

    });

    $('#modal-eliminar-Hcargadas').on('hidden.bs.modal', function () {

      self.eliHorasCargadas.data = [];
      self.eliHorasCargadas.error = false;

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

    crear: function(){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };
        if (self.form.horas_trabajadas.value > 23) {
          var message = "Maximo de 23 horas trabajadas al dia.";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };
        }else{
        //Obtenemos valores
        let parametros = {
          fecha:  self.form.fecha.value,
          descripcion: self.form.descripcion.value,
          horas_trabajadas: self.form.horas_trabajadas.value,
        }
        axios.post('/cargarHoras', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.alertForm = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };
            self.form.fecha.value = "";
            self.form.descripcion.value = "";
            self.form.horas_trabajadas.value = "";
            self.actualizar();
          }else{

            throw response.data;

          }

        })
        .catch(error => {

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });
      };

    },

    actualizar: function(){
      axios.get('/datosHorasProyecto')
      .then(function (response) {

      if(response.status === 200){

        self.infoProyAnalista = response.data.infoProyAnalista;
        self.infoHorasCargadas = response.data.infoHorasCargadas;
        self.form.mostrar = true;
        self.form.btn.Crear.html = self.form.btn.Crear.htmlInit;
        self.horas_cargadas = 0;
        for (var i = 0; i < self.infoHorasCargadas.length; i++) {
              self.horas_cargadas = self.infoHorasCargadas[i].horas_trabajadas + self.horas_cargadas;          
        }
        setTimeout(function(){
              self.alertForm = {
              class: "",
              message: "",
              show: false
              };
            }, 2000);
      }else{

        throw "error";

      }

    })
    .catch(error => {
      self.submitCrear.disabled = true;
      self.alert.mostrar = true;
      self.alertForm = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

    });
  },

  detalleModHorasCargadas: function(idHcargadas,e){

      self.modHorasCargadas.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idHcargadas: idHcargadas
      };

      axios.get('/detalleModHorasCargadas', {params: parametros})
      .then(function (response) {

        if(response.status === 200 ){

          self.modHorasCargadas.data = response.data.infoModHorasCargadas;
          self.form.fechaM.value = self.modHorasCargadas.data.fecha;
          self.form.descripcionM.value = self.modHorasCargadas.data.descripcion;
          self.form.horas_trabajadasM.value = self.modHorasCargadas.data.horas_trabajadas;
          self.form.btn.Modificar.html = self.form.btn.Modificar.htmlInit;


          $('#modal-detalle-Hcargadas').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("far fa-edit");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.modHorasCargadas.error = true;
        $('#modal-detalle-Hcargadas').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("far fa-edit");

      });

    },

    modificar: function(){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          fecha:  self.form.fechaM.value,
          descripcion: self.form.descripcionM.value,
          horas_trabajadas: self.form.horas_trabajadasM.value,
          id: self.modHorasCargadas.data.id,
        }
        axios.post('/ModificarHorasCargadas', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.alertForm = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };
            self.form.fechaM.value = "";
            self.form.descripcionM.value = "";
            self.form.horas_trabajadasM.value = "";
            self.actualizar();
          }else{

            throw response.data;

          }

        })
        .catch(error => {

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

    },

    detalleHorasEliminar: function(idHcargadas,e){

      self.eliHorasCargadas.error = false;
      $(e.target).removeClass("fas fa-trash").addClass("fa-cog fa-spin");

      let parametros = {
        idHcargadas: idHcargadas
      };

      axios.get('/detalleHorasEliminar', {params: parametros})
      .then(function (response) {

        if(response.status === 200 ){

          self.infoEliHorasCargadas = response.data.infoeliHorasCargadas;
          self.form.btn.Eliminar.html = self.form.btn.Eliminar.htmlInit;


          $('#modal-eliminar-Hcargadas').modal("show");
          $(e.target).removeClass("fa-cog fa-spin").addClass("fas fa-trash");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.eliHorasCargadas.error = true;
        $('#modal-eliminar-Hcargadas').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fas fa-trash");

      });

    },

    eliminar: function(){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          id: self.infoEliHorasCargadas.id,
        }
        axios.post('/EliminarHorasCargadas', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.alertForm = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };
            self.form.fechaM.value = "";
            self.form.descripcionM.value = "";
            self.form.horas_trabajadasM.value = "";
            self.actualizar();
          }else{

            throw response.data;

          }

        })
        .catch(error => {

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

    },
    
   }// Fin methods

});
