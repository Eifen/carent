require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
var self;

Vue.use(VueTheMask);
Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);

const errorInit = () => {

  Object.keys(self.form).forEach(function(indiceObjecto, indice) {

    if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
      self.form[indiceObjecto].disabled = true;
    }

  });

  self.submitActualizar.disabled = true;

  self.alertForm = {
    class : "alert alert-warning",
    message : "Existe un error!, consulte con el administrador del sistema.",
    show: true
  };

  self.loading = false;

}

const datosIniciales = () => {

  return new Promise((resolve, reject) => {

    axios.get('/detalleProyectoModificar')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        resolve({
                 info: response.data.info,
                 infodivi: response.data.infodivi,
                 detalleUsuarioG: response.data.info,
                 clientes: response.data.clientes,
                 divisiones: response.data.divisiones,
                 estatus: response.data.estatus,
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

var app = new Vue({

  el: '#modificarProyecto',
  data: {
    idProyecto: null,
    alertForm: {
      class: "",
      message: "",
      show: false
    },
     comboClientes: [],
     comboEstatus: [],
     comboDivisiones: [],

    refreshForm: false,
    form: {
      descripcion:{
        disabled: true,
        value: ""
      },
      cliente:{
        disabled: true,
        value: ""
      },
      horas:{
        asignar: true,
        disabled: true,
        value: 1
      },
      fechaContratacion:{
        disabled: true,
        value: ""
      },
      estatus: {
        disabled: true,
        value: ""
      },
      divisiones: {
        disabled: true,
        validar: false,
        value: "",
      },
      mostrar: false
    },
    loading: true,
    alert:{
      message: "",
      mostrar: false
    },
    submitActualizar: {
      content: "Actualizar Datos",
      disabled: false,
      show:true
    },
    dataInicial: false
  },

  beforeCreate: async function(){

    self = this;

    const dataInit = await datosIniciales();

    if(dataInit.response){

        self.idProyecto = dataInit.info.id;
        self.divisiones_v = dataInit.infodivi;
        self.form.descripcion.value = dataInit.info.descripcion;
        self.form.cliente.value = dataInit.info.id_cliente;
        self.form.horas.value = dataInit.info.horas_contratadas;
        self.form.fechaContratacion.value = dataInit.info.fecha_contratacion;
        self.form.estatus.value = dataInit.info.id_estatus;
        self.comboClientes = dataInit.clientes;
        self.comboEstatus = dataInit.estatus;
        self.comboDivisiones = dataInit.divisiones;
        self.form.descripcion.disabled = false;
        self.form.cliente.disabled = false;
        self.form.fechaContratacion.disabled = false;
        self.form.estatus.disabled = false;
        self.form.mostrar = true;

        var data = [];
        for (var i = 0; i < dataInit.infodivi.length; i++) {
          for (var j = 0; j < self.comboDivisiones.length; j++) {
            if (dataInit.infodivi[i].id_division === self.comboDivisiones[j].id) {
              data[i] = self.comboDivisiones[j];
            }
          }
        }
        self.form.divisiones.value = data;
        self.form.divisiones.disabled = false;

        self.loading = false;

      }else{
        errorInit();
      }

  },
  created: async function () {

    let checkDataInitReady = setInterval(() => {

      if (self.form.mostrar) {

        clearInterval(checkDataInitReady);
        new AutoNumeric('#horas', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          emptyInputBehavior: 1,
          minimumValue: 1,
          modifyValueOnWheel: false
        });
         AutoNumeric.getAutoNumericElement("#horas").set(self.form.horas.value);
         var indices = ["descripcion","cliente","horas","fechaContratacion","estatus","divisiones"];

        indices.forEach(function(indiceObjecto, indice) {
          if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
            self.form[indiceObjecto].disabled = false;
          }
        });

        self.divisiones_v.forEach(function(item, index){

          self.$refs["asignar-"+item.id_division][0].value = self.divisiones_v[index].horas_contratadas;

        });

      }

    }, 1000);

  },
  mounted: async function () {
  },
  updated: function () {},
  methods:{

    asignarHoras: function(valor){

      self.form.horas.asignar = (valor.length > 0) ? true : false;

      if(!self.form.horas.asignar){
        self.form.horas.value = 0;
        $("#horas").parent().find(".mensaje").html("").removeClass("invalid-feedback");
        $("#horas").removeClass("error");
      }

    },
    formatoHoraAsignada: function(input){

      let regex = /^\d+$/;

      if(!regex.test(input.key)){
        input.preventDefault();
        self.horasTotales();
      }

      $("#horas").parent().find(".mensaje").html("").removeClass("invalid-feedback");
      $("#horas").removeClass("error");

    },
    horasTotales: function(){

      var total = 0;

      $(".hora-asignada").each(function(index,item){
        let hora = ($(item).val().trim() === "") ? 0 : parseInt($(item).val());
        total = parseInt(total) + hora;
      });

      self.form.horas.value = total;

    },
    valuesForm: function(e){

      if(e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'email'){
        self.form[e.target.id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      self.limpiarMensajeError(e);

    },
    limpiarMensajeErrorMultiselect: function(){
      $(".multiselect").parent().find(".mensaje").html("").removeClass("invalid-feedback");
      $(".multiselect").removeClass("error");
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

        if(self.form.divisiones.value.length === 0){
          formValido = false;
          $(".multiselect").parent().find(".mensaje").html("Seleccione una opción").addClass("invalid-feedback");
          $(".multiselect").addClass("error");
          zenscroll.toY($("#divisiones").offset().top - 100);
        }else if(parseInt(self.form.horas.value) === 0){
          formValido = false;
          $("#horas").parent().find(".mensaje").html("Debe ser mayor a 0").addClass("invalid-feedback");
          $("#horas").addClass("error");
          zenscroll.toY($("#horas").offset().top - 100);
        }

      }

      if(formValido){

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        const divisiones = [];
        self.form.divisiones.value.forEach((item, i) => {
          let hora = (self.$refs["asignar-"+item.id][0].value.trim() === "") ? 0 : parseInt(self.$refs["asignar-"+item.id][0].value);
          divisiones.push({id:item.id, horas: hora});
        });

        //Obtenemos valores

        let parametros = {
          idProyecto: self.idProyecto,
          descripcion:  self.form.descripcion.value,
          cliente: self.form.cliente.value,
          fechaContratacion: self.form.fechaContratacion.value,
          divisiones: divisiones,
          estatus: self.form.estatus.value
        }

        self.submitActualizar.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitActualizar.disabled = true;

        axios.post('/modificarProyecto', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            var indices = [];

            indices.forEach(function(indiceObjecto, indice) {
              if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
                self.form[indiceObjecto].disabled = false;
              }
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

          var indices = [];

          indices.forEach(function(indiceObjecto, indice) {
            if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
              self.form[indiceObjecto].disabled = false;
            }
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

          if(input.type === 'text'){

            if(input.getAttribute("data-min")){

              let minChar = input.getAttribute("data-min");
              let numChar = input.value.length
              let regexName = /^[A-Za-zÀ-ÖØ-öø-ÿ 0-9 -]+$/;

              if(numChar < minChar){

                respuesta = false;
                mensaje   = "El campo debe contener al menos "+minChar+" caracteres!";
                zenscroll.toY($(input).offset().top - 100);

              }else if(!regexName.test(input.value)){

                respuesta = false;
                mensaje = "Solo se permiten letras y este caracter (',´)!";
                zenscroll.toY($(input).offset().top - 100);

              }

            }else if(input.getAttribute("data-date")){

              let numChar = input.value.length

              if(numChar < 10){
                respuesta = false;
                mensaje   = "Fecha incorrecta!";
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
  }// Fin methods

});
