require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
import Multiselect from 'vue-multiselect';
import VueNumeric from 'vue-numeric';
import Vuelidate from 'vuelidate';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import { required, minLength, minValue } from 'vuelidate/lib/validators';
var self;

Vue.use(VueTheMask);
Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(VueNumeric);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(Vuelidate);

new Vue({

  el: '#proyectoDivision',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alertFormA: {
      class: "",
      message: "",
      show: false
    },
    alert:{
      message: "",
      mostrar: false
    },
    comboEstatus: [],
    comboEmpleados: [],
    form: {
      btn: {
        filtrar: {
          disabled: false,
          html: "",
          htmlInit: "Buscar Proyecto",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
        asignar: {
          disabled: false,
          html: "",
          htmlInit: "Realizar Asignación",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
        cerrar: {
          disabled: false,
          html: "",
          htmlInit: "Terminar Asignación",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        }
      },
      camposAtributos: {
        empleados: {
          disabled: true,
          empleados: [],
          invalidFeedback: "",
          state: null,
          value:""
        },
        horas:{
          disabled: true,
          state: null
        }
      },
      campos: {
        empleados: null
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
    permisoAsignar: false,
    permisoCerrar: false,
    proyectosD: [],
    proyectos: [],
    proyectoBusqueda: [],
    horasComparar: [],
    asignados: [],
    horas_cargadas: 0,
    horas_contratadas: 0,
    diferencia: 0,
    permisoVer: false,
    permisoCrear: false,
    agregar: 1,
    loading: true
  },
  validations: {
    form:{
      campos:{
        empleados: {
          required
        },
        horas: {
          required,
          minValue: minValue(1)
        }
      }
    }
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
          self.form.mostrar = false;
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

      self.form.camposAtributos.empleados.empleados = [];
      self.permisoAsignar = true;

      valor.forEach(function(item, index){

          const empleado = {

            id: item.id,
            idAnaProy: item.idAnaProy,
            permisoCarga: item.permisoCarga,
            nombre: item.nombre,
            cargo: item.cargo,
            horas: {
              invalidFeedback: "",
              state: null,
              value: item.hasOwnProperty('horas_asignadas') ? item.horas_asignadas : 0
            },
            horas_cargadas: item.horas_cargadas,
            horas_asignadas: item.horas_asignadas
          }
          self.$set(self.form.camposAtributos.empleados.empleados, index, empleado);


      });

      if(self.form.camposAtributos.empleados.empleados.length === 0){
        self.form.horas.value = 0;
        self.permisoAsignar = false;
        self.permisoCerrar = false;
        self.form.camposAtributos.horas.invalidFeedback = "";
        self.form.camposAtributos.horas.state = null;
      }

      self.horasTotales();

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
      self.horas_contratadas = 0;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idDproyecto: idDproyecto
      };

      axios.get('/DetalleDivProyecto', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleDproyecto.data = response.data.infoDproyecto;
          self.detalleAproyecto.data = response.data.infoAproyecto;
          if (self.detalleDproyecto.data[0].horas_adicional != null) {
          self.horas_contratadas = parseFloat(self.detalleDproyecto.data[0].horas_contratadas) + parseFloat(self.detalleDproyecto.data[0].horas_adicional);
          }else{
            self.horas_contratadas = parseFloat(self.detalleDproyecto.data[0].horas_contratadas);
          }
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

    asignarEmpleado: function(valor){

      var val = 0;
      var empleadosAsignados = [];
      self.permisoAsignar = true;
      empleadosAsignados = self.form.camposAtributos.empleados.empleados;
      self.form.camposAtributos.empleados.empleados = [];

        valor.forEach(function(item, index){

          val = 0;
          for (var i = 0; i < empleadosAsignados.length; i++) {
            if(empleadosAsignados[i].id === item.id){
              const empleado = {
                id: empleadosAsignados[i].id,
                idAnaProy: empleadosAsignados[i].idAnaProy,
                permisoCarga: empleadosAsignados[i].permisoCarga,
                nombre: empleadosAsignados[i].nombre,
                cargo: empleadosAsignados[i].cargo,
                horas: empleadosAsignados[i].horas,
                horas_cargadas: empleadosAsignados[i].horas_cargadas,
                horas_asignadas: empleadosAsignados[i].horas_asignadas
              }
              self.$set(self.form.camposAtributos.empleados.empleados, index, empleado);
              val = 1;
            }
          }
          if (val == 0 ) {
            const empleado = {
                id: item.id,
                idAnaProy: item.idAnaProy,
                permisoCarga: item.permisoCarga,
                nombre: item.nombre,
                cargo: item.cargo,
                horas: {
                  invalidFeedback: "",
                  state: null,
                  value: 0,
                },
                horas_cargadas: item.horas_cargadas,
                horas_asignadas: item.horas_asignadas
              }
            self.$set(self.form.camposAtributos.empleados.empleados, index, empleado);
          }

        });

      if(self.form.camposAtributos.empleados.empleados.length === 0){
        self.permisoAsignar = false;
        self.permisoCerrar = false;
        self.form.horas.value = 0;
        self.form.camposAtributos.horas.invalidFeedback = "";
        self.form.camposAtributos.horas.state = null;
      }
      self.horasTotales();

    },

    horaEmpleado: function(index){

      self.limpiarMensajeError2(self.form.camposAtributos.empleados.empleados[index].horas);
      self.horasTotales();

    },

    horasTotales: function(){
      var total = 0;

      for(var i = 0; i < self.form.camposAtributos.empleados.empleados.length; i++){
        total = total + parseInt(self.form.camposAtributos.empleados.empleados[i].horas.value);
      }

      total = (isNaN(total)) ? 0 : total;

      self.form.horas.value = total;
      self.limpiarMensajeError2(self.form.camposAtributos.horas);

    },

    limpiarMensajeError2: function(objeto){

      objeto.state = null;
      objeto.invalidFeedback = "";

    },

    asignarAnalistaProyecto: function(idDproyecto,id_proyecto_division,e){

      self.form.camposAtributos.empleados.empleados = [];
      self.form.campos.empleados = null;
      self.form.horas.value = 0;
      self.asignados = [];
      self.permisoCerrar = false;
      self.detalleAsigproyecto.error = false;
      $(e.target).removeClass("far fa-edit").addClass("fa-cog fa-spin");

      let parametros = {
        idDproyecto: idDproyecto,
        id_proyecto_division: id_proyecto_division
      };

      axios.get('/detalleAnalistaProyecto', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleAnalista.data = response.data.analistas;
          self.detalleAsigproyecto.data = response.data.proyecto;
          self.comboEmpleados = response.data.empleados;
          self.form.camposAtributos.empleados.disabled = false;
          self.form.btn.asignar.disabled = false;
          self.form.btn.asignar.html = self.form.btn.asignar.htmlInit;

          if (self.detalleAsigproyecto.data[0].horas_adicionales != null) {
            self.detalleAsigproyecto.data[0].horas_contratadas = parseFloat(self.detalleAsigproyecto.data[0].horas_contratadas) + parseFloat(self.detalleAsigproyecto.data[0].horas_adicionales);
          }
          var a = 0;
          for (var i = 0; i < self.comboEmpleados.length; i++) {
            if (self.comboEmpleados[i].id_estatus > 0) {
            self.asignados[a] = self.comboEmpleados[i];
            a = a + 1;
            }
          }
          if (self.asignados.length > 0) {
            self.form.btn.cerrar.disabled = false;
            self.form.btn.cerrar.html = self.form.btn.cerrar.htmlInit;
            self.permisoCerrar = true;
            self.form.campos.empleados = self.asignados;
            self.asignarHoras(self.asignados);
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

    asignarAnalista: function(horas_contratadas,e){

      var formValido = true;
      var total2 = [];
      self.form.btn.asignar.disabled = true;
      self.form.btn.asignar.html = self.form.btn.asignar.htmlLoading;
      self.form.btn.cerrar.disabled = false;
      self.form.btn.cerrar.html = self.form.btn.cerrar.htmlLoading;
      self.alertForm = {
        class : "",
        message : "",
        show: false
      };
      self.alertFormA = {
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
        setTimeout(function(){
              self.alertForm = {
              class: "",
              message: "",
              show: false
              };
            }, 1500);
        self.form.btn.asignar.disabled = false;
        self.form.btn.asignar.html = self.form.btn.asignar.htmlInit;
        self.form.btn.cerrar.disabled = false;
        self.form.btn.cerrar.html = self.form.btn.cerrar.htmlInit;
      }else{

      for(var i = 0; i < self.form.camposAtributos.empleados.empleados.length; i++){
        const hora = self.form.camposAtributos.empleados.empleados[i].horas.value;
        if(hora < 1){
          self.form.camposAtributos.empleados.empleados[i].horas.invalidFeedback = "Debe ser mayor a 0";
          self.form.camposAtributos.empleados.empleados[i].horas.state = false;
          formValido = false;
          zenscroll.toY(self.$refs['hora-'+i].$el);
          self.form.btn.asignar.disabled = false;
          self.form.btn.asignar.html = self.form.btn.asignar.htmlInit;
          self.form.btn.cerrar.disabled = false;
          self.form.btn.cerrar.html = self.form.btn.cerrar.htmlInit;
          break
        }
      }

      if (formValido) {

        const empleados = [];
          self.form.camposAtributos.empleados.empleados.forEach((item, i) => {
          let hora = parseInt(item.horas.value);
          let horas_cargadas = parseInt(item.horas_cargadas);
          empleados.push({id: item.id, idAnaProy: item.idAnaProy, horas: hora, horas_cargadas: horas_cargadas});
        });

        let parametros = {
          id_proyecto: self.detalleAsigproyecto.data[0].id,
          id_proyecto_division: self.detalleAsigproyecto.data[0].id_proyecto_division,
          empleados: empleados
        };

        axios.get('/asigHorasAnalistaProy', {params: parametros})
          .then(function (response) {
            if(response.status === 200 && response.data.response.response === true){

              self.form.btn.asignar.disabled = false;
              self.form.btn.asignar.html = self.form.btn.asignar.htmlInit;
              self.alertFormA = {
                class : "alert alert-success",
                message : response.data.response.message,
                show: true
              };
              setTimeout(function(){
                self.alertFormA = {
                class: "",
                message: "",
                show: false
                };
              }, 1500);
              self.permisoCerrar = true;
              self.form.btn.cerrar.disabled = false;
              self.form.btn.cerrar.html = self.form.btn.cerrar.htmlInit;
            self.actualizar();
            }else{

              self.alertFormA = {
                class : "alert alert-danger",
                message : response.data.response.message,
                show: true
              };
              setTimeout(function(){
                self.alertFormA = {
                class: "",
                message: "",
                show: false
                };
              }, 3000);
              self.comboEmpleados = response.data.empleados;
              self.form.camposAtributos.empleados.disabled = false;
              self.form.btn.asignar.disabled = false;
              self.form.btn.asignar.html = self.form.btn.asignar.htmlInit;

              var a = 0;
              for (var i = 0; i < self.comboEmpleados.length; i++) {
                if (self.comboEmpleados[i].id_estatus > 0) {
                self.asignados[a] = self.comboEmpleados[i];
                a = a + 1;
                }
              }
              if (self.asignados.length > 0) {
                self.form.btn.cerrar.disabled = false;
                self.form.btn.cerrar.html = self.form.btn.cerrar.htmlInit;
                self.permisoCerrar = true;
                self.form.campos.empleados = self.asignados;
                self.asignarHoras(self.asignados);
              }

              $('#modal-asignar-Aproyecto').modal("show");
              $(e.target).removeClass("fa-cog fa-spin").addClass("far fa-edit");
            }
          })
        }
      }
    },

    actualizar: function(){

      self.agregar = 1;
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

    cantidadHora(value){

      let regex = /^(?:[1-9][0-9]*)$/;

      if(regex.test(value)){
        return value;
      }else{
        return "";
      }

    },

    formCargarHoras: function(idProyecto,idUsuario,e){


      let parametros = {
        idProyecto: idProyecto,
        idUsuario: idUsuario
      };

      axios.get('/formCargarHoras', {params: parametros})

    },

    cerrarModal: function(){
      $("#modal-asignar-Aproyecto").modal("hide");

    }

  }// Fin methods

});
