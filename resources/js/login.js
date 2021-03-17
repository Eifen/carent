require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import AutoNumeric from 'autonumeric';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Vuelidate from 'vuelidate';
import { required, minLength, minValue } from 'vuelidate/lib/validators';
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;


Vue.component('loading',require('./components/loading.vue').default);
Vue.component('alert',require('./components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons)
Vue.use(Vuelidate);

//se declaran todas las varibles
new Vue({

  el: '#app',
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
    formLogin: {
      alert: {
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      codigoUsuario: {
        autonumeric: null,
        disabled: true,
        invalidFeedback: "",
        state: null,
        value: null
      },
      clave: {
        disabled: true,
        iconShowPass: {
          icon: "",
          hide: "eye-slash-fill",
          show: "eye-fill"
        },
        invalidFeedback: "",
        type: "password",
        state: null,
        value: ""
      },
      submit:{
        disabled: true,
        html: "Entrar",
        htmlInit: "Entrar",
        htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
        show:true
      }
    },
    formRecovery: {
      codigoRecuperacion: {
        disabled: false,
        value: ""
      }
    },
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

    let codigoUsuario = self.$refs["codigoUsuario"].$el
    self.formLogin.codigoUsuario.autonumeric = new AutoNumeric(codigoUsuario, {
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

    self.formLogin.codigoUsuario.disabled = false;
    self.formLogin.clave.disabled = false;
    self.formLogin.clave.iconShowPass.icon = self.formLogin.clave.iconShowPass.show;
    self.formLogin.submit.html = self.formLogin.submit.htmlInit;
    self.formLogin.submit.disabled = false;

    /*$('#modal-recuperar-clave').on('hidden.bs.modal', function () {

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

    });*/

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
    limpiarMensajeError: function(objeto){

      objeto.state = null;
      objeto.invalidFeedback = "";

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

      self.mostrarAlert(self.formLogin.alert);

      Object.keys(self.formLogin).forEach((indice, i) => {

        if(self.formLogin[indice].hasOwnProperty("state")){
          self.formLogin[indice].state = (self.formLogin[indice].state === true) ? true : null;
        }

        if(self.formLogin[indice].hasOwnProperty("invalidFeedback")){
          self.formLogin[indice].invalidFeedback = "";
        }

      });

      return;

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

      self.formLogin.clave.type = (self.formLogin.clave.type === "text") ? "password" : "text";
      self.formLogin.clave.iconShowPass.icon = (self.formLogin.clave.type === "text") ? self.formLogin.clave.iconShowPass.hide : self.formLogin.clave.iconShowPass.show;

    },
    mostrarAlert: function(alert, mostrar = false, variante = "", mensaje = "", iconCerrar = false, contador = false, ocultarSeg = 0){

      return new Promise(resolve => {

        alert.contador = contador;
        alert.iconCerrar = iconCerrar;
        alert.mensaje = mensaje;
        alert.mostrar = mostrar;
        alert.ocultarSeg = ocultarSeg;
        alert.variante = variante;

        resolve(true);

      });

    }

  }// Fin methods

});
