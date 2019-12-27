require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

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
    disabledSubmitLogin: false,
    disabledSubmitModal: false,
    formLogin: {
      codigoUsuario : "",
      clave: ""
    },
    key: null,
    iv: null,
    showSubmitModal: true
  },
  beforeCreate: function(){

    self = this;

    const config = axios.get('/encryptConfig', { params : {clave: "123456"}})
    .then(function (response) {

      //console.log(response);

      if(response.status === 200 && response.data.key && response.data.iv){

        self.key = response.data.key;
        self.iv = response.data.iv;
        self.alertLogin = {class : "", message: "", show: false};
        self.alertRecoveryPass = {class : "", message: "", show: false};
        self.showSubmitModal = true;

      }else{

        self.disabledSubmitLogin = true;
        self.disabledSubmitModal = true;
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
        self.showSubmitModal = false;

      }

    })
    .catch(error => {

      self.submitLogin = true;

    });

  },
  created: function () {
    alert ("ANA BLANDINSS");

  },
  mounted: function () {

    new AutoNumeric('#codigoUsuario', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: ''
    });

    new AutoNumeric('#codigoRecuperacion', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: ''
    });

  },
  updated: function () {

    $('.aliado').tooltip();

  },
  methods:{

    desencriptar: function(valor){
      return "hola";
    },
    valuesFormLogin: function(e){
      self.formLogin[$(e.target).attr("id")] = $(e.target).val();

    },
    modalRecuperarClave: function(){

      $("#modal-recuperar-clave").modal("show");

    },
    recuperarClave: function(){
      alert("recuperar");
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

        //Obtenemos valores
        let parametros = {
          codigoUsuario: self.formLogin.codigoUsuario,
          clave: self.formLogin.clave
        }

        axios.post('/login', parametros)
        .then(function (response) {

          const data = response.data;

        })
        .catch(error => {

          console.log("ERROR LOGIN");
          console.log(error);

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

    }

  }// Fin methods

});
