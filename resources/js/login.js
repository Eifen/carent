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
    formLogin: {
      alert: {
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      botones: {
        recoveryPass: {
          disabled: true,
          html: "",
          htmlInit: "Olvidé mi contraseña",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: true
        },
        submit:{
          disabled: true,
          html: "",
          htmlInit: "Entrar",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show:true
        }
      },
      campos: {
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
            show: "eye-fill",
            value: null
          },
          invalidFeedback: "",
          type: "password",
          state: null,
          value: ""
        }
      },
    },
    formRecovery: {
      alert: {
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      botones: {
        submit:{
          disabled: true,
          html: "",
          htmlInit: "Recuperar Contraseña",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: true
        }
      },
      campos: {
        codigoUsuario: {
          autonumeric: null,
          disabled: true,
          invalidFeedback: "",
          state: null,
          value: null
        }
      }
    },
    iv: null,
    key: null,
    loading: true
  },
  validations: {
    formLogin: {
      campos: {
        codigoUsuario: {
          value: {
            required
          }
        },
        clave: {
          value: {
            required
          }
        }
      }
    },
    formRecovery: {
      campos: {
        codigoUsuario: {
          value: {
            required
          }
        }
      }
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

      /*self.formLogin.campos.codigoUsuario.disabled = true;
      self.formLogin.campos.clave.disabled = true;
      formLogin.botones.submit.disabled = true;
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

      self.loading = false;*/

    });

  },
  created: function () {},
  mounted: function () {

    let codigoUsuario = self.$refs["codigoUsuario"].$el
    self.formLogin.campos.codigoUsuario.autonumeric = new AutoNumeric(codigoUsuario, {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep'
    });

    /*let codigoUsuarioR = self.$refs["codigoUsuarioR"].$el
    self.formRecovery.campos.codigoUsuario.autonumeric = new AutoNumeric(codigoUsuarioR, {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep'
    });*/

    self.formLogin.campos.codigoUsuario.disabled = false;
    self.formLogin.campos.clave.disabled = false;
    self.formLogin.campos.clave.iconShowPass.icon = self.formLogin.campos.clave.iconShowPass.show;
    self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlInit;
    self.formLogin.botones.submit.disabled = false;
    self.formLogin.botones.recoveryPass.html = self.formLogin.botones.recoveryPass.htmlInit;
    self.formLogin.botones.recoveryPass.disabled = false;

    self.formRecovery.campos.codigoUsuario.disabled = false;
    self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlInit;
    self.formRecovery.botones.submit.disabled = false;

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
    recuperarClave: function(){

      var formValido = true;

      self.mostrarAlert(self.formRecovery.alert);

      Object.keys(self.formRecovery.campos).forEach((indice, i) => {

        if(self.formRecovery.campos[indice].hasOwnProperty("state")){
          self.formRecovery.campos[indice].state = (self.formRecovery.campos[indice].state === true) ? true : null;
        }

        if(self.formRecovery.campos[indice].hasOwnProperty("invalidFeedback")){
          self.formRecovery.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.formRecovery.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.formRecovery.campos[indice];
        campo.$touch();

        if(campo.$invalid){

          self.formRecovery.campos[indice].state = false;
          const valorCampo = self.$v.formRecovery.campos[indice].$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.formRecovery.campos[indice].invalidFeedback = mensajeError.mensaje;

            if(!mensajeError.respuesta){
              break
            }

          }

          zenscroll.toY(self.$refs[indice].$el);
          formValido = false;
          break;

        }

      }

      if(formValido){

        //Obtenemos valores
        let parametros = {
          codigoUsuario: self.encriptar(self.formRecovery.campos.codigoUsuario.value)
        }

        Object.keys(self.formRecovery.campos).forEach((indice, i) => {

          if(self.formRecovery.campos[indice].hasOwnProperty("disabled")){
            self.formRecovery.campos[indice].disabled = true;
          }

        });

        self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlLoading;
        self.formRecovery.botones.submit.disabled = true;

        axios.post('/recoveryloginsss', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.recovery === true){

            self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlInit;
            self.formRecovery.botones.submit.disabled = false;
            self.mostrarAlert(self.formRecovery.alert, true, "success", response.data.message, false, false, 0);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          Object.keys(self.formRecovery.campos).forEach((indice, i) => {

            if(self.formRecovery.campos[indice].hasOwnProperty("disabled")){
              self.formRecovery.campos[indice].disabled = false;
            }

          });

          self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlInit;
          self.formRecovery.botones.submit.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.mostrarAlert(self.formRecovery.alert, true, "warning", message, false, false, 0);

        });

      }// Fin if(formValido)

    },
    login: function(){

      var formValido = true;

      self.mostrarAlert(self.formLogin.alert);

      Object.keys(self.formLogin.campos).forEach((indice, i) => {

        if(self.formLogin.campos[indice].hasOwnProperty("state")){
          self.formLogin.campos[indice].state = (self.formLogin.campos[indice].state === true) ? true : null;
        }

        if(self.formLogin.campos[indice].hasOwnProperty("invalidFeedback")){
          self.formLogin.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.formLogin.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.formLogin.campos[indice];
        campo.$touch();

        if(campo.$invalid){

          self.formLogin.campos[indice].state = false;
          const valorCampo = self.$v.formLogin.campos[indice].$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.formLogin.campos[indice].invalidFeedback = mensajeError.mensaje;

            if(!mensajeError.respuesta){
              break
            }

          }

          zenscroll.toY(self.$refs[indice].$el);
          formValido = false;
          break;

        }

      }

      if(formValido){

        //Obtenemos valores
        let parametros = {
          codigoUsuario: self.encriptar(self.formLogin.campos.codigoUsuario.value),
          clave: self.encriptar(self.formLogin.campos.clave.value)
        }

        Object.keys(self.formLogin.campos).forEach((indice, i) => {

          if(self.formLogin.campos[indice].hasOwnProperty("disabled")){
            self.formLogin.campos[indice].disabled = true;
          }

        });

        self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlLoading;
        self.formLogin.botones.submit.disabled = true;
        self.formLogin.botones.recoveryPass.disabled = true;

        axios.post('/login', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.login === true){

            self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlInit;
            self.mostrarAlert(self.formLogin.alert, true, "success", response.data.message, false, false, 0);

            setTimeout(function(){

              window.location.href = "/inicio";

            }, 2000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          Object.keys(self.formLogin.campos).forEach((indice, i) => {

            if(self.formLogin.campos[indice].hasOwnProperty("disabled")){
              self.formLogin.campos[indice].disabled = false;
            }

          });

          self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlInit;
          self.formLogin.botones.submit.disabled = false;
          self.formLogin.botones.recoveryPass.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.mostrarAlert(self.formLogin.alert, true, "warning", message, false, false, 0);

        });

      }// Fin if

    },
    validadorMensajes: function(indice,campo){

      var mensaje,
          respuesta = true;

      if(!campo[indice] && indice === "required"){
        mensaje = "Este campo es requerido!";
        respuesta = false;
      }else if(!campo[indice] && indice === "minLength"){
        let minChar = campo.$params[indice].min;
        mensaje = "Debe contener al menos "+minChar+" Caracteres!";
        respuesta = false;
      }else if(!campo[indice] && indice === "email"){
        mensaje = "Correo inválido!";
        respuesta = false;
      }else if(!campo[indice] && indice === "minValue"){
        let minChar = campo.$params[indice].min;
        mensaje = "El valor mínimo es "+minChar+"!";
        respuesta = false;
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        self.login();
      }

    },
    verClave: function(e){

      self.formLogin.campos.clave.type = (self.formLogin.campos.clave.type === "text") ? "password" : "text";
      self.formLogin.campos.clave.iconShowPass.icon = (self.formLogin.campos.clave.type === "text") ? self.formLogin.campos.clave.iconShowPass.hide : self.formLogin.campos.clave.iconShowPass.show;

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
