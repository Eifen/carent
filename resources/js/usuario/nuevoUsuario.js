require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
import { Datetime } from 'vue-datetime';
import 'vue-datetime/dist/vue-datetime.css';
var self;

Vue.use(VueTheMask);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component('datetime', Datetime);
Vue.use(BootstrapVue);

new Vue({

  el: '#nuevoUsuario',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    comboEstados: [],
    comboMunicipios: [],
    comboParroquias: [],
    comboDivisiones: [],
    comboCargos: [],
    comboTipoDocumento: [],
    refreshForm: false,
    form: {
      nombre1:{
        disabled: false,
        value: ""
      },
      nombre2:{
        disabled: false,
        value: ""
      },
      apellido1:{
        disabled: false,
        value: ""
      },
      apellido2:{
        disabled: false,
        value: ""
      },
      fechaNacimiento: {
        disabled: false,
        value: ""
      },
      codigoUsuario: {
        disabled: false,
        value: ""
      },
      cedula: {
        disabled: false,
        value: ""
      },
      estado: {
        disabled: true,
        validar: false,
        value: ""
      },
      municipio: {
        disabled: true,
        help: "Municipio de la oficina en donde se desempeña",
        validar: false,
        value: ""
      },
      parroquia: {
        disabled: true,
        help: "Parroquia de la oficina en donde se desempeña",
        validar: false,
        value: ""
      },
      division: {
        disabled: true,
        validar: false,
        value: ""
      },
      cargo: {
        disabled: true,
        validar: false,
        value: ""
      },
      correoPrincipal: {
        disabled: false,
        value: ""
      },
      correoSecundario: {
        disabled: false,
        validar: false,
        value: ""
      },
      telefono1: {
        disabled: false,
        value: ""
      },
      telefono2: {
        disabled: false,
        value: ""
      },
      empleado: {
        checked:false
      },
      fechaIngreso:{
        disabled: true,
        validar: false,
        value: ""
      },
      tipoDocumento: {
        disabled: false,
        value: ""
      }
    },
    loading: true,
    submitCrear: {
      content: "Crear Nuevo Usuario",
      disabled: false,
      show:true
    },
    key: null,
    iv: null
  },
  beforeCreate: function(){

    self = this;

    axios.get('/dataInicialNuevoUsuario')
    .then(function (response) {

      if(response.status === 200 && response.data.encryptConfig.key && response.data.encryptConfig.iv){

        self.key = response.data.encryptConfig.key;
        self.iv = response.data.encryptConfig.iv;
        self.comboCargos = response.data.cargos;
        self.comboDivisiones = response.data.divisiones;
        self.form.municipio.help = 'Municipio de la oficina en donde se desempeña';
        self.comboEstados = response.data.estados;
        self.comboTipoDocumento = response.data.tipoDocumentos;
        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      Object.keys(self.form).forEach(function(indiceObjecto, indice) {

        self.form[indiceObjecto].disabled = true;

      });

      self.submitCrear.disabled = true;

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

    new AutoNumeric('#codigoUsuario', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep',
      minimumValue: 0,
      modifyValueOnWheel: false
    });

    new AutoNumeric('#cedula', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '.',
      minimumValue: 0,
      modifyValueOnWheel: false
    });

    $('[data-toggle="tooltip"]').tooltip();

  },
  updated: function () {},
  methods:{

    municipios: function(){

      self.form.municipio.value = ""
      self.form.municipio.disabled = true;
      self.form.parroquia.value = ""
      self.form.parroquia.disabled = true;
      self.form.parroquia.help = '<i class="fas fa-cog fa-spin"></i> buscando';

      axios.get('/municipios', { params: {
         id_estado: self.form.estado.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.form.parroquia.help = 'Parroquia de la oficina en donde se desempeña';
          self.comboMunicipios = response.data;
          self.form.municipio.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        self.form.parroquia.help = 'Parroquia de la oficina en donde se desempeña';

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },
    parroquias: function(){

      self.form.parroquia.disabled = true;

      axios.get('/parroquias', {params: {
         id_municipio: self.form.municipio.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.comboParroquias = response.data;
          self.form.parroquia.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },
    esEmpleado: function(e){

      if(self.form.empleado.checked){

        self.form.estado.disabled = false;
        self.form.division.disabled = false;
        self.form.cargo.disabled = false;
        self.form.fechaIngreso.disabled = false;

        self.form.estado.validar = true;
        self.form.municipio.validar = true;
        self.form.parroquia.validar = true;
        self.form.division.validar = true;
        self.form.cargo.validar = true;
        self.form.fechaIngreso.validar = true;

        self.form.estado.value = "";

      }else{

        $(e.target).parents("form").find(".form-group .mensaje").html("").removeClass("invalid-feedback");
        $(e.target).parents("form").find(".form-group .form-control").removeClass("error");

        self.form.estado.disabled = true;
        self.form.municipio.disabled = true;
        self.form.parroquia.disabled = true;
        self.form.division.disabled = true;
        self.form.cargo.disabled = true;
        self.form.fechaIngreso.disabled = true;

        self.form.estado.validar = false;
        self.form.municipio.validar = false;
        self.form.parroquia.validar = false;
        self.form.division.validar = false;
        self.form.cargo.validar = false;
        self.form.fechaIngreso.validar = false;

        self.form.estado.value = "";
        self.form.municipio.value = "";
        self.form.parroquia.value = "";
        self.form.division.value = "";
        self.form.cargo.value = "";
        self.form.fechaIngreso.value = "";

      }

    },
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

      if(e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'email'){
        self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    limpiarMensajeError2: function(){

      if(self.$refs["fechaIngreso"]){
        $(self.$refs["fechaIngreso"].$el).children("input").removeClass("error");
        $(self.$refs["fechaIngreso"].$el).parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
      }

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

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          nombre1:  self.form.nombre1.value,
          nombre2: self.form.nombre2.value,
          apellido1: self.form.apellido1.value,
          apellido2: self.form.apellido2.value,
          fechaNacimiento: self.form.fechaNacimiento.value,
          codigoUsuario: self.encriptar(self.form.codigoUsuario.value),
          cedula: AutoNumeric.getAutoNumericElement("#cedula").getNumber(),
          parroquia: self.form.parroquia.value,
          division: self.form.division.value,
          cargo: self.form.cargo.value,
          correoPrincipal: self.form.correoPrincipal.value,
          correoSecundario: self.form.correoSecundario.value,
          telefono1: self.form.telefono1.value,
          telefono2: self.form.telefono2.value,
          empleado: self.form.empleado.checked,
          fechaIngreso: self.form.fechaIngreso.value,
          tipoDocumento: self.form.tipoDocumento.value
        }

        self.submitCrear.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitCrear.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });

        axios.post('/crearUsuario', parametros)
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
            indices.push("estado","municipio","parroquia","division","cargo","fechaIngreso");
          }

          indices.forEach(function(indiceObjecto, indice) {
            self.form[indiceObjecto].disabled = false;
          });
          self.submitCrear.content = 'Crear Nuevo Usuario';
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

          if(input.type === 'email'){

            let regexEmail = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            respuesta      = regexEmail.test(input.value);

            if(!respuesta){
              zenscroll.toY($(input).offset().top - 100);
              mensaje        = "Correo inválido";
            }

          }else if(input.type === 'text' || input.type === 'textarea' || input.type === 'date'){

            if(input.getAttribute("data-min") && !input.getAttribute("data-name-lastname")){

              let minChar = (Number(input.getAttribute("data-min")) === 0) ? 1 : input.getAttribute("data-min");
              let numChar = input.value.length
              let regexName = /^[a-zA-Z ']+$/;

              if(numChar < minChar){

                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);

              }

            }else if(input.getAttribute("data-min") && input.getAttribute("data-name-lastname")){

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

            }else if(input.getAttribute("data-name-lastname")){

              if(input.value.length > 0){

                let regexName = /^[A-Za-zÀ-ÖØ-öø-ÿ ]+$/;
                respuesta = regexName.test(input.value);

                if(!respuesta){
                  mensaje = "Solo se permiten letras y este caracter (')!";
                  zenscroll.toY($(input).offset().top - 100);
                }

              }

            }else if(input.getAttribute("data-only-number")){

              var valor = (input.getAttribute("data-formated-number")) ? AutoNumeric.getAutoNumericElement("#"+input.id).getNumber() : input.value;

              let regexNumber = /^\d+$/;
              respuesta = regexNumber.test(valor);

              if(!respuesta){
                mensaje = "Solo números";
                zenscroll.toY($(input).offset().top - 100);
              }

            }else{

              if(input.value === ""){
                respuesta= false;
                mensaje = "Este campo es requerido!";
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
    },
    limpiarFecha: function(nameRef){
      self.form[nameRef].value = "";
    }

  }// Fin methods

});
