require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

Vue.component('loading',require('./components/loading.vue').default);

//se declaran todas las varibles
var app = new Vue({

  el: '#login',
  data: {
    alertLogin: {
      class: "",
      message: "",
      show: false
    },
    alertRecoveryPass: {
      class: "",
      message: "",
      show: false
    },
    copyRight: `Sofguar © ${new Date().getFullYear()}`,
    formLogin: {
      codigoUsuario: {
        disabled: false,
        value: ""
      },
      clave: {
        disabled: false,
        type: "password",
        value: ""
      }
    },
    formRecovery: {
      codigoRecuperacion: {
        disabled: false,
        value: ""
      }
    },
    claseVerClaveIcon: "fas fa-eye",
    iv: null,
    key: null,
    linkRecoveryPass: true,
    loading: true,
    showSubmitModal: true,
    submitLogin: {
      content: "Entrar",
      disabled: false,
      show:true
    },
    submitModalRecoveryPass: {
      content: "Recuperar",
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
        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.formLogin.codigoUsuario.disabled = true;
      self.formLogin.clave.disabled = true;
      self.submitLogin.disabled = true;
      self.submitModalRecoveryPass.show = false;
      self.alertLogin = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };
      self.alertRecoveryPass = {
        class : "alert alert-warning",
        message : "Existe un error!, consulte con el administrador del sistema.",
        show: true
      };

      self.loading = false;

    });

  },
  created: function () {},
  mounted: function () {

    new AutoNumeric('.codigoUsuario', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep'
    });

    new AutoNumeric('.codigoRecuperacion', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep'
    });

    $('#modal-recuperar-clave').on('hidden.bs.modal', function () {

      self.alertRecoveryPass = {
        class : "",
        message : "",
        show: false
      };

      self.submitModalRecoveryPass = {
        content: "Recuperar",
        disabled: false,
        show:true
      }

      self.formRecovery = {
        codigoRecuperacion: {
          disabled: false,
          value: ""
        }
      }

      AutoNumeric.getAutoNumericElement(".codigoRecuperacion").set("");

    });

  },
  updated: function () {},
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
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    modalRecuperarClave: function(){

      $("#modal-recuperar-clave").modal("show");

    },
    recuperarClave: function(){

      var formValido = true;

      $("#formRecoveryPass .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("#formRecoveryPass .form-group .form-control").removeClass("error");

      $("#formRecoveryPass .form-group").each(function(index, elemento) {

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

        self.alertRecoveryPass = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          codigoUsuario: self.encriptar(self.formRecovery.codigoRecuperacion.value)
        }

        self.submitModalRecoveryPass.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitModalRecoveryPass.disabled = true;
        self.formRecovery.codigoRecuperacion.disabled = true;

        axios.post('/recoverylogin', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.recovery === true){

            self.submitModalRecoveryPass.show = false;

            self.alertRecoveryPass = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formRecovery.codigoRecuperacion.disabled = false;
          self.submitModalRecoveryPass.content = 'Recuperar';
          self.submitModalRecoveryPass.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertRecoveryPass = {
            class : "alert alert-warning",
            message : message,
            show: true
          };

        });

      }// Fin if(formValido)

    },
    login: function(){

      var formValido = true;

      $("#formLogin .form-group .mensaje").html("").removeClass("invalid-feedback");
      $("#formLogin .form-group .form-control").removeClass("error");

      $("#formLogin .form-group").each(function(index, elemento) {

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

        self.alertLogin = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          codigoUsuario: self.encriptar(self.formLogin.codigoUsuario.value),
          clave: self.encriptar(self.formLogin.clave.value)
        }

        self.submitLogin.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitLogin.disabled = true;
        self.formLogin.codigoUsuario.disabled = true;
        self.formLogin.clave.disabled = true;

        axios.post('/login', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.login === true){

            self.submitLogin.show = false;
            self.linkRecoveryPass = false;

            self.alertLogin = {
              class : "alert alert-success",
              message : response.data.message,
              show: true
            };

            setTimeout(function(){

              window.location.href = "/inicio";

            }, 2000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formLogin.codigoUsuario.disabled = false;
          self.formLogin.clave.disabled = false;
          self.submitLogin.content = 'Entrar';
          self.submitLogin.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alertLogin = {
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

          if(input.type === 'email'){

            let regexEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            respuesta      = regexEmail.test(input.value);
            (respuesta === false) ? zenscroll.toY($(input).offset().top - 100) : "" ;
            mensaje        = "Correo inválido";

          }else if(input.type === 'text' || input.type === 'textarea'){

            if(input.getAttribute("data-min")){
              let minChar = input.getAttribute("data-min");
              let numChar = input.value.length

              if(numChar < minChar){
                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);
              }

            }

            if(input.getAttribute("data-only-number")){

              let regexNumber = /^\d+$/;
              respuesta = regexNumber.test(input.value);
              mensaje = "Solo números";
              zenscroll.toY($(input).offset().top - 100);

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

    },
    verClave: function(e){

      if(self.$refs["clave"].type === "password"){
        self.formLogin.clave.type = "text";
        self.claseVerClaveIcon = "fas fa-eye-slash";
      }else{
        self.formLogin.clave.type = "password";
        self.claseVerClaveIcon = "fas fa-eye";
      }

    }

  }// Fin methods

});
