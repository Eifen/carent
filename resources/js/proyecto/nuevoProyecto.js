require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;

Vue.use(VueTheMask);
Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);

var app = new Vue({

  el: '#app',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    comboClientes: [],
    comboEstatus: [],
    comboDivisiones: [],
    refreshForm: false,
    form: {
      descripcion:{
        disabled: true,
        value: ""
      },
      cliente:{
        disabled: true,
        value: ""
      },
      horas:{
        disabled: true,
        value: 1
      },
      fechaContratacion:{
        disabled: true,
        value: ""
      },
      estatus: {
        disabled: true,
        value: ""
      },
      divisiones: {
        disabled: true,
        validar: false,
        value: ""
      },
      mostrar: false
    },
    submitCrear: {
      content: "Crear nuevo Proyecto",
      disabled: false,
      show:true
    }
  },
  beforeCreate: function(){

    self = this;

    axios.get('/dataInicialNuevoProyecto')
    .then(function (response) {

      if(response.status === 200){

        self.comboClientes = response.data.clientes;
        self.comboEstatus = response.data.estatus;
        self.comboDivisiones = response.data.divisiones;
        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.horas.disabled = false;
        self.form.fechaContratacion.disabled = false;
        self.form.estatus.disabled = false;
        self.form.divisiones.disabled = false;
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
  mounted: function () {

    let checkDataInitReady = setInterval(() => {

      if (self.form.mostrar) {

        clearInterval(checkDataInitReady);
        new AutoNumeric('#horas', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          emptyInputBehavior: 1,
          minimumValue: 1,
          modifyValueOnWheel: false
        });

      }

    }, 1000);

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
    crear: function(){

      var formValido = true;

      $("form .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("form .form-group .form-control").removeClass("error");

      $("form .form-group").each(function(index, elemento) {

        if($(elemento).find(".form-control").length > 0){

          var input = $(elemento).find(".form-control")[0];
          var valido = self.validarValor(input);

          if(!valido.respuesta){
            $(elemento).find(".mensaje").html(valido.mensaje).addClass("invalid-feedback");
            $(elemento).find(".form-control").addClass("error");
            formValido = valido.respuesta;
            return false;
          }

        }

      });

      if(formValido){

        if(self.form.divisiones.value.length === 0){
          formValido = false;
          $(".multiselect").parent().find(".mensaje").html("Seleccione una opción").addClass("invalid-feedback");
          $(".multiselect").addClass("error");
          zenscroll.toY($("#divisiones").offset().top - 100);
        }

      }

      if(formValido){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        const divisiones = [];
        self.form.divisiones.value.forEach((item, i) => {
          divisiones.push(item.id);
        });

        //Obtenemos valores
        let parametros = {
          descripcion:  self.form.descripcion.value,
          cliente: self.form.cliente.value,
          horas: self.form.horas.value,
          fechaContratacion: self.form.fechaContratacion.value,
          divisiones: divisiones,
          estatus: self.form.estatus.value
        }

        self.submitCrear.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitCrear.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          if(self.form[indiceObjecto].hasOwnProperty('disabled')){
            self.form[indiceObjecto].disabled = true;
          }
        });

        axios.post('/crearProyecto', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.submitCrear.show = false;
            self.refreshForm = true;

            self.alertForm = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          var indices = ["nombre1","nombre2","apellido1","apellido2","fechaNacimiento","codigoUsuario","cedula","correoPrincipal","correoSecundario","telefono1","telefono2"];

          if(self.form.empleado.checked){
            indices.push("estado","municipio","parroquia","division","cargo");
          }

          indices.forEach(function(indiceObjecto, indice) {
            self.form[indiceObjecto].disabled = false;
          });
          self.submitCrear.content = 'Crear nuevo Usuario';
          self.submitCrear.disabled = false;

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

      }// Fin if

    },
    validarValor: function(input) {

      var respuesta = true;
      var mensaje   = '';

      if(input.hasAttribute("data-validar")){

        if(input.getAttribute("data-validar") === "true"){

          if(input.type === 'text'){

            if(input.getAttribute("data-min")){

              let minChar = input.getAttribute("data-min");
              let numChar = input.value.length
              let regexName = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/;

              if(numChar < minChar){

                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);

              }else if(!regexName.test(input.value)){

                respuesta = false;
                mensaje = "Solo se permiten letras y este caracter (',´)!";
                zenscroll.toY($(input).offset().top - 100);

              }

            }else if(input.getAttribute("data-date")){

              let numChar = input.value.length

              if(numChar < 10){
                respuesta = false;
                mensaje   = "Fecha incorrecta!";
                zenscroll.toY($(input).offset().top - 100);
              }

            }

          }else if(input.type === "select-one"){

            if(input.value === ""){
              respuesta = false;
              mensaje = "Debe seleccionar una opción!";
              zenscroll.toY($(input).offset().top - 100);
            }

          }

        }

      }

      return {respuesta: respuesta, mensaje: mensaje};

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/formNuevoUsuario";
    }

  }// Fin methods

});
