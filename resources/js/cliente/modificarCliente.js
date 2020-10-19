require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

Vue.use(VueTheMask);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component("v-select", vSelect);
Vue.use(BootstrapVue);

const errorInit = () => {

  Object.keys(self.form).forEach(function(indiceObjecto, indice) {

    self.form[indiceObjecto].disabled = true;

  });

  self.submitActualizar.disabled = true;

  self.alertForm = {
    class : "alert alert-warning",
    message : "Existe un error!, consulte con el administrador del sistema.",
    show: true
  };

}

const datosIniciales = () => {

  return new Promise((resolve, reject) => {
    // Se busca los parametros iniciales para la modificacion
    axios.get('/detalleClienteModificar')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        resolve({
                 infoClie: response.data.info,
                 detalleUsuario: response.data.info,
                 estatus: response.data.estatus,
                 paises: response.data.paises,
                 response: true
               });

      }else{

        throw "error";

      }

    }).catch(error => {

      resolve({response:false, message:"Error al obtener la información del usuario"});

    });

  });

}
// Se declaran las variables
new Vue({

  el: '#modificarCliente',
  data: {
    hexTokens: {
      F: {
        pattern: /[cegjpvCEGJPV]/,
        transform: v => v.toLocaleUpperCase()
      },
      N:{
        pattern: /[0-9]/,
        transform: v => v.toLocaleUpperCase()
      }
    },
    idCliente: null,
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    comboEstatus: [],
    comboPaises: [],
    refreshForm: false,
    form: {
      codigoCliente:{
        disabled: false,
        value: ""
      },
      rif:{
        disabled: false,
        value: ""
      },
      nit:{
        disabled: false,
        value: ""
      },
      razon_social:{
        disabled: false,
        value: ""
      },
      pais: {
        disabled: false,
        validar: true,
        value: ""
      },
      direccion:{
        disabled: false,
        maxlength: 500,
        value: ""
      },
      telefono_fiscal: {
        disabled: false,
        value: ""
      },
      pagina_web: {
        disabled: false,
        value: ""
      },
      email_fiscal: {
        disabled: false,
        value: ""
      },
      estatus:{
        disabled: false,
        value: ""
      }
    },
      alert:{
      message: "",
      mostrar: false
    },
      formSearch: {
      submit: {
        disabled: true,
        html: "Buscar"
      },
      inputSearch: {
        disabled: true,
        value: ""
      },
      select: {
        disabled:false,
        value: ""
      }
    },
      usuarios: {
      mostrar: false,
      registros: []
    },
    detalleUsuario: {
      error: false,
      data: []
    },
    submitActualizar: {
      content: "Actualizar Datos",
      disabled: false,
      show:true
    },
    loading: true,
    dataInicial: false,
    id_pais: ""
  },

  beforeCreate: async function(){

    self = this;
    // Se le asigna los valores a las variables con los valores iniciales
    const dataInit = await datosIniciales();

    if(dataInit.response){

        self.idCliente = dataInit.infoClie.id;
        self.detalleUsuario.data.id=dataInit.infoClie.id_usuario_socio;
        self.detalleUsuario.data.codigo=dataInit.infoClie.codigoU;
        self.detalleUsuario.data.nombre=dataInit.infoClie.nombre;
        self.formSearch.inputSearch.value= dataInit.infoClie.codigoU;
        self.form.codigoCliente.value = dataInit.infoClie.codigo;
        self.form.rif.value = dataInit.infoClie.rif;
        self.form.nit.value = dataInit.infoClie.nit;
        self.form.razon_social.value = dataInit.infoClie.razon_social;
        self.form.pais.value = dataInit.infoClie.pais;
        self.id_pais = dataInit.infoClie.id_pais;
        self.form.direccion.value = dataInit.infoClie.direccion;
        
        self.form.telefono_fiscal.value = dataInit.infoClie.telefono_fiscal;
        self.form.pagina_web.value = dataInit.infoClie.pagina_web;
        self.form.email_fiscal.value = dataInit.infoClie.email_fiscal;
        self.form.estatus.value = dataInit.infoClie.id_estatus;
        self.comboEstatus = dataInit.estatus;
        self.comboPaises = dataInit.paises;
        self.loading = false;

      }else{
        errorInit();
      }

  },
  created: async function () {

    let checkDataInitReady = setInterval(() => {
      if (self.form.codigoCliente.value  !== '') {
        clearInterval(checkDataInitReady);

        new AutoNumeric('#codigoCliente', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          leadingZero: 'keep',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#codigoCliente").set(self.form.codigoCliente.value);

        new AutoNumeric('#nit', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#nit").set(self.form.nit.value);


        var indices = ["rif","nit","razon_social","pais","direccion","telefono_fiscal","pagina_web","email_fiscal","estatus"];

        indices.forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = false;
        });

      }
    }, 1000);

  },
  mounted: async function () {
  },
  updated: function () {},
  methods:{

    buscar: function(e){

      self.alert.mostrar = false;
      self.usuarios.mostrar = false;

      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;
        // Obtenemos los valores
        let parametros = {
          buscarPor: self.formSearch.select.value,
          dato: self.formSearch.inputSearch.value
        };
        //Se utiliza el metodo get para su busqueda y se envian con los parametros
        axios.get('/buscarUsuariosS', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.usuarios.mostrar = true;
            self.usuarios.registros = response.data.usuarios;
            $('#modal-detalle-usuario').modal("show");


          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.usuarios.registros = [];
          self.usuarios.mostrar = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert.message = message;

        });

      }else{

        $(".inputSearch").parent().find(".mensaje").html("Campo requerido").addClass("invalid-feedback");
        $(".inputSearch").addClass("error");
        zenscroll.toY($(".inputSearch").offset().top - 100);

      }

    },
    
    tipoFiltro: function(e){

      let opcion = parseInt(e.target.value);
      let valoresPermitidos = [1,2,3,4];

      self.usuarios.mostrar = false;
      self.usuarios.registros = [];

      if(valoresPermitidos.includes(opcion)){
        self.formSearch.inputSearch.disabled = false;
        self.formSearch.submit.disabled = false;
      }else{
        self.formSearch.inputSearch.disabled = true;
        self.formSearch.submit.disabled = true;
      }

    },
    
    evaluarCampo: function(id, e){

      if(e.target.type === 'text'){
        self.formSearch[id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      if(id === "inputSearch" && self.formSearch["inputSearch"].value.trim() === ""){
        self.usuarios.registros = [];
        self.usuarios.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },

    SelecionarUsuario: function(idUsuario,e){

      self.detalleUsuario.error = false;
       $(e.target).removeClass("fa-check-square").addClass("fa-cog fa-spin");
      // Obtenemos los valores
      let parametros = {
        idUsuario: idUsuario
      };
      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/detalleUsuarios', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.usuarios.mostrar = true;
          self.detalleUsuario.data = response.data.info;
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-check-square");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleUsuario.error = true;
        $('#modal-detalle-usuario').modal("show");
        $(e.target).removeClass("fa-check-square").addClass("fa-cog fa-spin");

      });

    },

    pais: function(){

      self.form.telefono_fiscal.value = "";
      self.form.telefono_fiscal.disabled = false;
      self.id_pais = self.form.pais.value.id;      
      self.form.telefono_fiscal.value = self.form.pais.value.codigo_telf;
    },

    valuesForm: function(e){

      if(e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'email'){
        self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeError: function(e){
      $(e.target).removeClass("error");
      $(e.target).parent(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
    },
    campoOpcionalARequerido: function(e){

      self.valuesForm(e);
      self.form[e.target.id].validar = (self.form[e.target.id].value.length > 0 && self.form[e.target.id].validar === false) ? true : false;

    },
    actualizar: function(){

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
          idCliente: self.idCliente,
          idUsuario: self.detalleUsuario.data.id,
          codigoCliente: AutoNumeric.getAutoNumericElement("#codigoCliente").getNumber(),
          rif: self.form.rif.value,
          nit: AutoNumeric.getAutoNumericElement("#nit").getNumber(),
          razon_social:  self.form.razon_social.value,
          pais: self.id_pais,
          direccion: self.form.direccion.value,
          telefono_fiscal: self.form.telefono_fiscal.value,
          pagina_web: self.form.pagina_web.value,
          email_fiscal: self.form.email_fiscal.value,
          estatus: self.form.estatus.value
        }

        self.submitActualizar.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitActualizar.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });
        //Se utiliza el metodo post para la modificacion y se envian con los parametros
        axios.post('/modificarCliente', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            var indices = ["rif","nit","razon_social","pais","direccion","telefono_fiscal","pagina_web","email_fiscal","estatus"];

            indices.forEach(function(indiceObjecto, indice) {
              self.form[indiceObjecto].disabled = false;
            });

            self.submitActualizar.content = 'Actualizar Datos';
            self.submitActualizar.disabled = false;
            self.submitActualizar.show = true;

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

          var indices = ["rif","nit","razon_social","pais","direccion","telefono_fiscal","pagina_web","email_fiscal","estatus"];

          indices.forEach(function(indiceObjecto, indice) {
            self.form[indiceObjecto].disabled = false;
          });
          self.submitActualizar.content = 'Actualizar Datos';
          self.submitActualizar.disabled = false;

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

          }else if(input.type === 'text' || input.type === 'textarea'){

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
        self.actualizar();
      }

    },
    refreshView: function(){
      window.location.href = "/formBuscarCliente";
    }

  }// Fin methods

});
