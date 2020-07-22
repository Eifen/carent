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

  self.loading = false;

}

const encryptConfig = () => {

  return new Promise((resolve, reject) => {

    axios.get('/encryptConfig')
    .then(function (response) {

      if(response.status === 200 && response.data.key && response.data.iv){

        resolve({key:response.data.key, iv:response.data.iv});

      }else{

        throw "error";

      }

    }).catch(error => {

      resolve({response:false, message:"Error al obtener las credenciales"});

    });

  });

}

const datosIniciales = () => {

  return new Promise((resolve, reject) => {

    axios.get('/detalleUsuarioModificar')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        resolve({
                 cargos: response.data.cargos,
                 divisiones: response.data.divisiones,
                 estados: response.data.estados,
                 infoUsu: response.data.info,
                 municipios: response.data.municipios,
                 parroquias: response.data.parroquias,
                 estatus: response.data.estatus,
                 tipoDocumentos: response.data.tipoDocumentos,
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

new Vue({

  el: '#modificarUsuario',
  data: {
    idUsuario: null,
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
    comboEstatus: [],
    comboTipoDocumento: [],
    form: {
      nombre1:{
        disabled: true,
        value: ""
      },
      nombre2:{
        disabled: true,
        value: ""
      },
      apellido1:{
        disabled: true,
        value: ""
      },
      apellido2:{
        disabled: true,
        value: ""
      },
      fechaNacimiento: {
        disabled: true,
        value: ""
      },
      codigoUsuario: {
        disabled: true,
        value: ""
      },
      cedula: {
        disabled: true,
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
        disabled: true,
        value: ""
      },
      correoSecundario: {
        disabled: true,
        validar: false,
        value: ""
      },
      telefono1: {
        disabled: true,
        value: ""
      },
      telefono2: {
        disabled: true,
        value: ""
      },
      empleado: {
        checked:false
      },
      estatus:{
        disabled: true,
        value: ""
      },
      fechaIngreso:{
        disabled: true,
        value: ""
      },
      fechaEgreso:{
        disabled: true,
        minValue: "",
        value: ""
      },
      tipoDocumento: {
        disabled: false,
        value: ""
      },
      idUsuarioDocumentoIdentidad: {
        value: null
      }
    },
    loading: true,
    submitActualizar: {
      content: "Actualizar Datos",
      disabled: false,
      show:true
    },
    key: null,
    iv: null,
    dataInicial: false
  },
  beforeCreate: async function(){

    self = this;

    const credenciales = await encryptConfig();

    if(credenciales.key && credenciales.iv){

      self.key = credenciales.key;
      self.iv = credenciales.iv;

      const dataInit = await datosIniciales();

      if(dataInit.response){

        self.idUsuario = dataInit.infoUsu.id;
        self.form.nombre1.value = dataInit.infoUsu.nombre_1;
        self.form.nombre2.value = dataInit.infoUsu.nombre_2;
        self.form.apellido1.value = dataInit.infoUsu.apellido_1;
        self.form.apellido2.value = dataInit.infoUsu.apellido_2;
        self.form.cedula.value = dataInit.infoUsu.cedula;
        self.form.fechaNacimiento.value = dataInit.infoUsu.fecha_nacimiento_utc;
        self.form.codigoUsuario.value = dataInit.infoUsu.codigo;
        self.form.correoPrincipal.value = dataInit.infoUsu.correo_principal;
        self.form.correoSecundario.value = dataInit.infoUsu.correo_secundario;
        self.form.telefono1.value = dataInit.infoUsu.telefono_principal;
        self.form.telefono2.value = dataInit.infoUsu.telefono_secundario;
        self.form.estatus.value = dataInit.infoUsu.id_estatus;
        self.form.tipoDocumento.value = dataInit.infoUsu.id_tipo_documento;
        self.comboEstados = dataInit.estados;
        self.comboCargos = dataInit.cargos;
        self.comboDivisiones = dataInit.divisiones;
        self.comboEstatus = dataInit.estatus;
        self.comboTipoDocumento = dataInit.tipoDocumentos;
        self.form.idUsuarioDocumentoIdentidad.value = dataInit.infoUsu.id_usuario_documento_identidad;

        if(dataInit.infoUsu.id_cargo !== null && dataInit.infoUsu.id_division !== null){

          self.form.empleado.checked = true;

          self.comboMunicipios = dataInit.municipios;
          self.comboParroquias = dataInit.parroquias;

          self.form.estado.disabled = false;
          self.form.municipio.disabled = false;
          self.form.parroquia.disabled = false;
          self.form.division.disabled = false;
          self.form.cargo.disabled = false;
          self.form.fechaIngreso.disabled = false;
          self.form.fechaEgreso.disabled = false;

          self.form.estado.validar = true;
          self.form.municipio.validar = true;
          self.form.parroquia.validar = true;
          self.form.division.validar = true;
          self.form.cargo.validar = true;
          self.form.fechaIngreso.validar = true;

          self.form.estado.value = dataInit.infoUsu.id_estado;
          self.form.municipio.value = dataInit.infoUsu.id_municipio;
          self.form.parroquia.value = dataInit.infoUsu.id_parroquia;
          self.form.division.value = dataInit.infoUsu.id_division;
          self.form.cargo.value = dataInit.infoUsu.id_cargo;
          self.form.fechaIngreso.value = dataInit.infoUsu.fecha_ingreso_utc;
          self.form.fechaEgreso.value = dataInit.infoUsu.fecha_egreso_utc;

          self.fechaMinima(dataInit.infoUsu.fecha_ingreso_utc, dataInit.infoUsu.fecha_egreso_utc);

          self.loading = false;

        }

      }else{
        errorInit();
      }

    }else{
      errorInit();
    }

  },
  created: async function () {

    let checkDataInitReady = setInterval(() => {
      if (self.form.codigoUsuario.value  !== '') {
        clearInterval(checkDataInitReady);

        new AutoNumeric('#codigoUsuario', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          leadingZero: 'keep',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#codigoUsuario").set(self.form.codigoUsuario.value);

        new AutoNumeric('#cedula', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#cedula").set(self.form.cedula.value);

        var indices = ["nombre1","nombre2","apellido1","apellido2","fechaNacimiento","cedula","correoPrincipal","correoSecundario","telefono1","telefono2","estatus"];

        indices.forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = false;
        });

      }
    }, 1000);

  },
  mounted: async function () {
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

        self.submitActualizar.disabled = true;

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

        self.submitActualizar.disabled = true;

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

        self.form.estado.validar = true;
        self.form.municipio.validar = true;
        self.form.parroquia.validar = true;
        self.form.division.validar = true;
        self.form.cargo.validar = true;

        self.form.estado.value = "";

      }else{

        $(e.target).parents("form").find(".form-group .mensaje").html("").removeClass("invalid-feedback");
        $(e.target).parents("form").find(".form-group .form-control").removeClass("error");

        self.form.estado.disabled = true;
        self.form.municipio.disabled = true;
        self.form.parroquia.disabled = true;
        self.form.division.disabled = true;
        self.form.cargo.disabled = true;

        self.form.estado.validar = false;
        self.form.municipio.validar = false;
        self.form.parroquia.validar = false;
        self.form.division.validar = false;
        self.form.cargo.validar = false;

        self.form.estado.value = "";
        self.form.municipio.value = "";
        self.form.parroquia.value = "";
        self.form.division.value = "";
        self.form.cargo.value = "";

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
          idUsuario: self.idUsuario,
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
          estatus: self.form.estatus.value,
          fechaIngreso: self.form.fechaIngreso.value,
          fechaEngreso: self.form.fechaEgreso.value,
          tipoDocumento: self.form.tipoDocumento.value,
          idUsuarioDocumentoIdentidad: self.form.idUsuarioDocumentoIdentidad.value
        }

        self.submitActualizar.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitActualizar.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });

        axios.post('/modificarUsuario', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            var indices = ["nombre1","nombre2","apellido1","apellido2","fechaNacimiento","cedula","correoPrincipal","correoSecundario","telefono1","telefono2", "estatus"];

            if(self.form.empleado.checked){
              indices.push("estado","municipio","parroquia","division","cargo","fechaIngreso","fechaEgreso");
            }

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

          var indices = ["nombre1","nombre2","apellido1","apellido2","fechaNacimiento","cedula","correoPrincipal","correoSecundario","telefono1","telefono2"];

          if(self.form.empleado.checked){
            indices.push("estado","municipio","parroquia","division","cargo","fechaIngreso","fechaEgreso");
          }

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
        self.actualizar();
      }

    },
    limpiarFecha: function(nameRef){
      self.form[nameRef].value = "";
    },
    limpiarMensajeError2: function(nameRef){

      if(self.$refs[nameRef]){
        $(self.$refs[nameRef].$el).children("input").removeClass("error");
        $(self.$refs[nameRef].$el).parents(".form-group").find(".mensaje").html("").removeClass("invalid-feedback");
      }

    },
    fechaMinima: function(fecha_minima, fecha_egreso = null){

      self.limpiarMensajeError2("fechaIngreso");

      const fecha_e = (fecha_egreso !== "" && fecha_egreso !== null) ? fecha_egreso : "";

      if(fecha_minima !== ""){

        self.form.fechaEgreso.minValue = fecha_minima;
        self.form.fechaEgreso.value = fecha_e;
        self.form.fechaEgreso.disabled = false;

      }


    }

  }// Fin methods

});
