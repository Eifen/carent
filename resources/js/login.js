import 'bootstrap';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import '@fortawesome/fontawesome-free/js/all.js';
import zenscroll from 'zenscroll';
import axios from 'axios';
import AutoNumeric from 'autonumeric';

import Vuelidate from 'vuelidate';
import { helpers, required } from '@vuelidate/validators'
import { useVuelidate } from '@vuelidate/core'

import CryptoJS from 'crypto-js'
var self;

Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons)
Vue.use(Vuelidate);

import alert from './components/alert.vue'
import loading from './components/loading.vue'
Vue.component('alert', alert)
Vue.component('loading', loading)

new Vue({

  el: '#app',
  data: {
    encryption: {
      iv: null,
      key: null
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
      botones: {
        recoveryPass: {
          disabled: true,
          html: "",
          htmlInit: "Olvidé mi contraseña",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>",
          show: true
        },
        submit:{
          disabled: true,
          html: "",
          htmlInit: "Entrar",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>",
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
          htmlLoading: 'Validando',
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
    loading: true
  },
  setup: () => ({ 
    v$: useVuelidate() 
  }),
  validations() {
    return {
      formLogin: {
        campos: {
          codigoUsuario: {
            value: {
              required: helpers.withMessage(
                'Este campo es requerido',
                required
              )
            }
          },
          clave: {
            value: {
              required: helpers.withMessage(
                'Este campo es requerido',
                required
              )
            }
          }
        }
      },
      formRecovery: {
        campos: {
          codigoUsuario: {
            value: {
              required: helpers.withMessage(
                'Este campo es requerido',
                required
              )
            }
          }
        }
      }
    }
  },
  beforeCreate: function(){
    self = this;
  },
  mounted: function () {

    try {

      self.formLogin.campos.clave.iconShowPass.icon = self.formLogin.campos.clave.iconShowPass.show;

      let codigoUsuario = self.$refs["codigoUsuario"].$el
      self.formLogin.campos.codigoUsuario.autonumeric = new AutoNumeric(codigoUsuario, {
        decimalPlaces: 0,
        decimalCharacter: ',',
        digitGroupSeparator: '',
        leadingZero: 'keep'
      });

      self.$refs["modal-recuperar-clave"].$on('shown', () => {

        let codigoUsuarioR = self.$refs["codigoUsuarioR"].$el
        self.formRecovery.campos.codigoUsuario.autonumeric = new AutoNumeric(codigoUsuarioR, {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          leadingZero: 'keep'
        });

      });

      self.$refs["modal-recuperar-clave"].$on('hidden', () => {

        self.formRecovery.campos.codigoUsuario.value = null;
        self.formRecovery.campos.codigoUsuario.autonumeric.set(0);

        Object.keys(self.formRecovery.campos).forEach((indice, i) => {

          if(self.formRecovery.campos[indice].hasOwnProperty("state")){
            self.formRecovery.campos[indice].state = null;
          }

          if(self.formRecovery.campos[indice].hasOwnProperty("invalidFeedback")){
            self.formRecovery.campos[indice].invalidFeedback = "";
          }

        });

        self.formRecovery.botones.submit.show = true;

        self.mostrarAlert(self.formRecovery.alert);

      });

      self.formLogin.campos.codigoUsuario.disabled = false;
      self.formLogin.campos.clave.disabled = false;
      self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlInit;
      self.formLogin.botones.submit.disabled = false;
      self.formLogin.botones.recoveryPass.html = self.formLogin.botones.recoveryPass.htmlInit;
      self.formLogin.botones.recoveryPass.disabled = false;

      self.formRecovery.campos.codigoUsuario.disabled = false;
      self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlInit;
      self.formRecovery.botones.submit.disabled = false;

      self.loading = false;

    }catch(err) {

      self.formLogin.botones.submit.html = self.formLogin.botones.submit.htmlInit;

      self.formLogin.campos.codigoUsuario.disabled = true;
      self.formLogin.campos.clave.disabled = true;
      self.formLogin.botones.submit.disabled = true;
      self.formLogin.botones.recoveryPass.disabled = true;

      self.formRecovery.campos.codigoUsuario.disabled = true;
      self.formRecovery.botones.submit.show = false;

      let message = "Existe un error!, consulte con el administrador del sistema.";
      self.mostrarAlert(self.formRecovery.alert, true, "warning", message, false, false, 0);
      self.mostrarAlert(self.formLogin.alert, true, "warning", message, false, false, 0);

      self.loading = false;

    }

  },
  methods:{

    encriptar: function(valor, encryptionKey, encryptionIv){

      let key = CryptoJS.enc.Hex.parse(encryptionKey);
      let iv = CryptoJS.enc.Hex.parse(encryptionIv);

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
    recuperarClave: function(encryptionKey, encryptionIv){

      var formValido = true;

      self.mostrarAlert(self.formRecovery.alert);

      Object.keys(self.formRecovery.campos).forEach((indice, i) => {

        if(self.formRecovery.campos[indice].hasOwnProperty("state")){
          self.formRecovery.campos[indice].state = null;
        }

        if(self.formRecovery.campos[indice].hasOwnProperty("invalidFeedback")){
          self.formRecovery.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.formRecovery.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.v$.formRecovery.campos[indice].value;
        campo.$touch();

        if(campo.$invalid){

          self.formRecovery.campos[indice].state = false;

          const arrayParams = Object.keys(campo.$errors);
          for(var j = 0; j <= (arrayParams.length - 1); j++){
           
            self.formRecovery.campos[indice].invalidFeedback = campo.$errors[j].$message;
            
            if(!campo.$errors[j].$response){
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
          codigoUsuario: self.encriptar(self.formRecovery.campos.codigoUsuario.value, encryptionKey, encryptionIv)
        }

        Object.keys(self.formRecovery.campos).forEach((indice, i) => {

          if(self.formRecovery.campos[indice].hasOwnProperty("disabled")){
            self.formRecovery.campos[indice].disabled = true;
          }

        });

        self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlLoading;
        self.formRecovery.botones.submit.disabled = true;

        axios.post('/recoverylogin', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.recovery === true){

            self.formRecovery.botones.submit.html = self.formRecovery.botones.submit.htmlInit;
            self.formRecovery.botones.submit.disabled = false;
            self.formRecovery.botones.submit.show = false;
            self.mostrarAlert(self.formRecovery.alert, true, "success", response.data.message, true, true, 3);

            setTimeout(function(){

              self.formRecovery.botones.submit.show = true;

              Object.keys(self.formRecovery.campos).forEach((indice, i) => {

                if(self.formRecovery.campos[indice].hasOwnProperty("disabled")){
                  self.formRecovery.campos[indice].disabled = false;
                }

              });

            }, 3200);

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
          self.formRecovery.botones.submit.show = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.mostrarAlert(self.formRecovery.alert, true, "warning", message, true, true, 3);

          setTimeout(function(){
            self.formRecovery.botones.submit.show = true;
          }, 3200);

        });

      }// Fin if(formValido)

    },
    login: function(encryptionKey, encryptionIv){

      var formValido = true;

      self.mostrarAlert(self.formLogin.alert);
      
      Object.keys(self.formLogin.campos).forEach((indice, i) => {

        if(self.formLogin.campos[indice].hasOwnProperty("state")){
          self.formLogin.campos[indice].state = null;
        }

        if(self.formLogin.campos[indice].hasOwnProperty("invalidFeedback")){
          self.formLogin.campos[indice].invalidFeedback = "";
        }

      });
      
      const arrayCampos = Object.keys(self.formLogin.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){
        
        let indice = arrayCampos[i];
        const campo = self.v$.formLogin.campos[indice].value;
        campo.$touch();
        if(campo.$invalid){

          self.formLogin.campos[indice].state = false;
         
          const arrayParams = Object.keys(campo.$errors);
          for(var j = 0; j <= (arrayParams.length - 1); j++){
           
            self.formLogin.campos[indice].invalidFeedback = campo.$errors[j].$message;
            
            if(!campo.$errors[j].$response){
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
          codigoUsuario: self.encriptar(self.formLogin.campos.codigoUsuario.value, encryptionKey, encryptionIv),
          clave: self.encriptar(self.formLogin.campos.clave.value, encryptionKey, encryptionIv)
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
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

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
