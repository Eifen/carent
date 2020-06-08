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
//Declaramos las variables
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
    horas_asignadas: "",
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
    //Buscamos los parametros iniciales y se los asignamos a las variables
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

        self.horas_asignadas = self.infoProyAnalista.horas_asignadas;
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

    crear: function(horas_cargadas,horas_asignadas,e){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        if(parseInt(horas_asignadas) < parseInt(horas_cargadas) + parseInt(self.form.horas_trabajadas.value)){
          var message = "Sobrepasaste el limite de horas asignadas";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };
        }else if (self.form.horas_trabajadas.value > 23) {
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
        //Se utiliza el metodo post para cargar horas y se envian con los parametros
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
            self.actualizar(); //Invocamos el metodo actualizar
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
      //Se buscan los parametros actualizados y se los asignamos a las variables
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
      // Obtenemos lo valores
      let parametros = {
        idHcargadas: idHcargadas
      };
      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/detalleModHorasCargadas', {params: parametros})
      .then(function (response) {

        if(response.status === 200 ){
          //Le asignamos los valores a las variables
          self.modHorasCargadas.data = response.data.infoModHorasCargadas;
          self.form.fechaM.value = self.modHorasCargadas.data.fecha;
          self.form.descripcionM.value = self.modHorasCargadas.data.descripcion;
          self.form.horas_trabajadasM.value = self.modHorasCargadas.data.horas_trabajadas;
          self.form.horas_trabajadasA.value = self.modHorasCargadas.data.horas_trabajadas
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

    modificar: function(horas_cargadas,horas_asignadas,horas_trabajadasA,e){

      var diferencia = parseInt(horas_cargadas) - parseInt(horas_trabajadasA);
        self.alertForm = {
          class : "",
          message : "",
          show: false
        };
        if (parseInt(horas_asignadas) < diferencia + parseInt(self.form.horas_trabajadasM.value)) {
          var message = "Sobrepasaste el limite de horas asignadas";
          self.alertForm = {
            class : "alert alert-warning",
            message : message,
            show: true
          };
          self.form.fechaM.value = "";
          self.form.descripcionM.value = "";
          self.form.horas_trabajadasM.value = "";
          setTimeout(function(){
              self.alertForm = {
              class: "",
              message: "",
              show: false
              };
            }, 2000);
        }else{
        //Obtenemos valores
        let parametros = {
          fecha:  self.form.fechaM.value,
          descripcion: self.form.descripcionM.value,
          horas_trabajadas: self.form.horas_trabajadasM.value,
          id: self.modHorasCargadas.data.id,
        }
        //Se utiliza el metodo post para modificar y se envian con los parametros
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
            self.actualizar(); // Invocamos el metodo actualizar
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
      }

    },

    detalleHorasEliminar: function(idHcargadas,e){

      self.eliHorasCargadas.error = false;
      $(e.target).removeClass("fas fa-trash").addClass("fa-cog fa-spin");
      // Obtenemos lo valores
      let parametros = {
        idHcargadas: idHcargadas
      };
      //Se utiliza el metodo get para eliminar y se envian con los parametros
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
