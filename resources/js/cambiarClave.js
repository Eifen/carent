require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import Vuelidate from 'vuelidate';
import { required, minLength, minValue, sameAs } from 'vuelidate/lib/validators';
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

Vue.component('menu-principal', require('./components/menuPrincipal.vue').default);
Vue.component('loading',require('./components/loading.vue').default);
Vue.component('alert',require('./components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(Vuelidate);

var app = new Vue({

  el: '#app',
  data: {
    encryption: {
      iv: null,
      key: null
    },
    formCambiarClave: {
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
          htmlInit: "Cambiar Contraseña",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show:true
        }
      },
      campos: {
        claveActual: {
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
        },
        nuevaClave: {
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
        },
        repetirNuevaClave: {
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
      }
    },
    loading: true
  },
  validations: {
    formCambiarClave: {
      campos: {
        claveActual: {
          value: {
            required
          }
        },
        nuevaClave: {
          value: {
            required
          }
        },
        repetirNuevaClave: {
          value: {
            required,
            igualA: sameAs(function(){ return this.formCambiarClave.campos.nuevaClave.value;})
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

      self.formCambiarClave.campos.claveActual.iconShowPass.icon = self.formCambiarClave.campos.claveActual.iconShowPass.show;
      self.formCambiarClave.campos.nuevaClave.iconShowPass.icon = self.formCambiarClave.campos.nuevaClave.iconShowPass.show;
      self.formCambiarClave.campos.repetirNuevaClave.iconShowPass.icon = self.formCambiarClave.campos.repetirNuevaClave.iconShowPass.show;

      if(mostrarModalCambioClave){
        self.$refs["mostrarModalCambioClave"].show();
      }

      self.formCambiarClave.campos.claveActual.disabled = false;
      self.formCambiarClave.campos.nuevaClave.disabled = false;
      self.formCambiarClave.campos.repetirNuevaClave.disabled = false;
      self.formCambiarClave.botones.submit.html = self.formCambiarClave.botones.submit.htmlInit;
      self.formCambiarClave.botones.submit.disabled = false;

      self.loading = false;

    }catch(err) {

      self.formCambiarClave.botones.submit.html = self.formCambiarClave.botones.submit.htmlInit;

      self.formCambiarClave.campos.claveActual.disabled = true;
      self.formCambiarClave.campos.nuevaClave.disabled = true;
      self.formCambiarClave.campos.repetirNuevaClave.disabled = true;
      self.formCambiarClave.botones.submit.disabled = true;

      let message = "Existe un error!, consulte con el administrador del sistema.";
      self.mostrarAlert(self.formCambiarClave.alert, true, "warning", message, false, false, 0);

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
    cambiarContrasena: function(encryptionKey, encryptionIv){

      var formValido = true;

      self.mostrarAlert(self.formCambiarClave.alert);

      Object.keys(self.formCambiarClave.campos).forEach((indice, i) => {

        if(self.formCambiarClave.campos[indice].hasOwnProperty("state")){
          self.formCambiarClave.campos[indice].state = null;
        }

        if(self.formCambiarClave.campos[indice].hasOwnProperty("invalidFeedback")){
          self.formCambiarClave.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.formCambiarClave.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.formCambiarClave.campos[indice].value;
        campo.$touch();

        if(campo.$invalid){

          self.formCambiarClave.campos[indice].state = false;
          const valorCampo = self.$v.formCambiarClave.campos[indice].value.$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.formCambiarClave.campos[indice].invalidFeedback = mensajeError.mensaje;

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
          claveActual: self.encriptar(self.formCambiarClave.campos.claveActual.value,encryptionKey,encryptionIv),
          nuevaClave: self.encriptar(self.formCambiarClave.campos.nuevaClave.value,encryptionKey,encryptionIv)
        }

        Object.keys(self.formCambiarClave.campos).forEach((indice, i) => {

          if(self.formCambiarClave.campos[indice].hasOwnProperty("disabled")){
            self.formCambiarClave.campos[indice].disabled = true;
          }

        });

        self.formCambiarClave.botones.submit.html = self.formCambiarClave.botones.submit.htmlLoading;
        self.formCambiarClave.botones.submit.disabled = true;

        axios.post('/guardarNuevaClave', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.formCambiarClave.botones.submit.html = self.formCambiarClave.botones.submit.htmlInit;
            self.mostrarAlert(self.formCambiarClave.alert, true, "success", response.data.message, false, true, 3);

            setTimeout(function(){

              window.location.href = "/cambiarClave";

            }, 3000);

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          Object.keys(self.formCambiarClave.campos).forEach((indice, i) => {

            if(self.formCambiarClave.campos[indice].hasOwnProperty("disabled")){
              self.formCambiarClave.campos[indice].disabled = false;
            }

          });

          self.formCambiarClave.botones.submit.html = self.formCambiarClave.botones.submit.htmlInit;
          self.formCambiarClave.botones.submit.disabled = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.mostrarAlert(self.formCambiarClave.alert, true, "warning", message, false, false, 0);

        });

      }// Fin if

    },
    validadorMensajes: function(indice,campo){

      var mensaje,
          respuesta = true;

      if(!campo[indice] && indice === "required"){
        mensaje = "Este campo es requerido!";
        respuesta = false;
      }else if(!campo[indice] && indice === "igualA"){
        mensaje = "El valor debe ser igual al campo anterior";
        respuesta = false;
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

    },
    verClave: function(campo){

      campo.type = (campo.type === "text") ? "password" : "text";
      campo.icon = (campo.type === "text") ? campo.iconShowPass.hide : campo.iconShowPass.show;

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
