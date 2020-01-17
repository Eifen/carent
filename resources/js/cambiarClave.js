require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

Vue.component('menu-principal', require('./components/menuPrincipal.vue').default);

var app = new Vue({

  el: '#cambiarClave',
  data: {
    alert: {
      class: "",
      message: "",
      show: false
    },
    form: {
      claveActual: {
        disabled: false,
        value: ""
      },
      nuevaClave: {
        disabled: false,
        value: ""
      },
      repetirNuevaClave: {
        disabled: false,
        value: ""
      }
    },
    iv: null,
    key: null,
    submit: {
      content: "Cambiar Contraseña",
      disabled: false,
      show:true
    }
  },
  beforeCreate: function(){

    self = this;

    axios.get('/encryptConfig')
    .then(function (response) {

      if(response.status === 200 && response.data.key && response.data.iv){

        self.key = response.data.key;
        self.iv = response.data.iv;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      Object.keys(self.form).forEach(function(indiceObjecto, indice) {
        self.form[indiceObjecto].disabled = true;
      });
      self.submit.disabled = true;
      self.alert = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

    });

  },
  methods:{

    encriptar: function(valor){

      let key = CryptoJS.enc.Hex.parse(self.key);
      let iv = CryptoJS.enc.Hex.parse(self.iv);

      var encrypted = CryptoJS.AES.encrypt(valor, key, {
          iv,
          padding: CryptoJS.pad.ZeroPadding,
      });

      return encrypted.toString();

    },
    valuesForm: function(e){

      self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    cambiarContrasena: function(){

      var formValido = true;

      $("form .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("form .form-group .form-control").removeClass("error");

      $("form .form-group").each(function(index, elemento) {

        var input = $(elemento).find(".form-control")[0];
        var valido = self.validarValor(input);

        if(!valido.respuesta){
          $(elemento).find(".mensaje").html(valido.mensaje).addClass("invalid-feedback");
          $(elemento).find(".form-control").addClass("error");
          formValido = valido.respuesta;
          return false;
        }

      });

      if(formValido){

        self.alert = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          claveActual: self.encriptar(self.form.claveActual.value),
          nuevaClave: self.encriptar(self.form.nuevaClave.value)
        }

        self.submit.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submit.disabled = true;
        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });

        axios.post('/guardarNuevaClave', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.submit.show = false;

            self.alert = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };

            setTimeout(function(){

              window.location.href = "/cambiarClave";

            }, 5000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          Object.keys(self.form).forEach(function(indiceObjecto, indice) {
            self.form[indiceObjecto].disabled = false;
          });
          self.submit.content = 'Cambiar Contraseña';
          self.submit.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert = {
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

          if(input.type === 'password'){

            if(input.getAttribute("data-min")){
              let minChar = input.getAttribute("data-min");
              let numChar = input.value.length

              if(numChar < minChar){
                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);
              }

            }

            if(input.getAttribute("data-equal")){

              let id = input.getAttribute("data-equal");
              let valor = document.getElementById(id).value;

              if(valor !== input.value){
                respuesta = false;
                mensaje   = 'La Contraseña no coincide con el campo "Nueva Contraseña"!';
                zenscroll.toY($(input).offset().top - 100);
              }

            }

          }

        }

      }

      return {respuesta: respuesta, mensaje: mensaje};

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        self.login();
      }

    }

  }// Fin methods

});
