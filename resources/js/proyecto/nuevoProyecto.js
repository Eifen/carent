require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import AutoNumeric from 'autonumeric';
import VueTheMask from 'vue-the-mask';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Vuelidate from 'vuelidate';
import { required, minLength, email } from 'vuelidate/lib/validators';
var self;

Vue.use(VueTheMask);
Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.use(BootstrapVue);
Vue.use(Vuelidate);

new Vue({

  el: '#app',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    comboEstatus: [],
    comboDivisiones: [],
    comboMonedas: [],
    refreshForm: false,
    form: {
      campos: {
        descripcion: null,
        cliente: null,
        estatus: null
      },
      camposAtributos: {
        descripcion:{
          disabled: false,
          invalidFeedback: "",
          state: null
        },
        cliente: {
          disabled: true,
          help: "",
          helpInit: "Cliente que esta asociado al proyecto",
          helpLoading: '<i class="fas fa-cog fa-spin"></i> buscando',
          invalidFeedback: "",
          listaDropdown: {
            listado: [],
            noResultado: false
          },
          state: null,
          valor: null,
          valorBlur: null,
          valorFocus: null
        },
        estatus: {
          disabled: true,
          invalidFeedback: "",
          state: null,
        }
      },
      horas:{
        asignar: false,
        disabled: true,
        value: 0
      },
      fechaContratacion:{
        disabled: true,
        value: ""
      },
      montoEn:{
        disabled: true,
        value: ""
      },
      monto:{
        autonumeric: null,
        disabled: true,
        value: 0
      },
      estatus: {
        disabled: true,
        value: ""
      },
      divisiones: {
        disabled: true,
        validar: false,
        value: ""
      },
      mostrar: false
    },
    loading: true,
    submitCrear: {
      content: "Crear nuevo Proyecto",
      disabled: false,
      show:true
    }
  },
  validations: {
    form:{
      campos:{
        descripcion: {
          required
        },
        cliente: {
          required
        },
        estatus: {
          required
        }
      }
    }
  },
  beforeCreate: function(){

    self = this;

    axios.get('/dataInicialNuevoProyecto')
    .then(function (response) {

      if(response.status === 200){

        response.data.estatus.forEach((item, i) => {
          self.comboEstatus.push({text:item.descripcion, value: item.id});
        });

        self.comboDivisiones = response.data.divisiones;
        self.comboMonedas = response.data.monedas;
        self.form.camposAtributos.descripcion.disabled = false;
        self.form.camposAtributos.cliente.disabled = false;
        self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpInit;
        self.form.camposAtributos.estatus.disabled = false;


        self.form.fechaContratacion.disabled = false;
        self.form.estatus.disabled = false;
        self.form.montoEn.disabled = false;
        self.form.divisiones.disabled = false;
        self.form.mostrar = true;

        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

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

    let checkDataInitReady = setInterval(() => {

      if (self.form.mostrar) {

        clearInterval(checkDataInitReady);
        new AutoNumeric('#horas', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          emptyInputBehavior: 0,
          minimumValue: 0,
          modifyValueOnWheel: false
        });

        self.form.autonumeric = new AutoNumeric('#monto', {
          decimalPlaces: 2,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          emptyInputBehavior: 0,
          minimumValue: 0,
          modifyValueOnWheel: false
        });

      }

    }, 1000);

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
    monedaSeleccionada: function(e){

      let simbolo = $(e.target).children("option:selected").attr("simbolo");
      self.form.autonumeric.update({ currencySymbol : simbolo+" "});
      self.form.monto.disabled = (($(e.target).val().trim() !== "") && ($(e.target).val() !== null)) ? false : true;
      self.limpiarMensajeError(e);

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

        let monto = (self.form.autonumeric === null) ? 0 : self.form.autonumeric.get()

        //Obtenemos valores
        let parametros = {
          descripcion:  self.form.descripcion.value,
          cliente: self.form.cliente.value,
          fechaContratacion: self.form.fechaContratacion.value,
          divisiones: divisiones,
          estatus: self.form.estatus.value,
          id_moneda: self.form.montoEn.value,
          monto: monto
        }

        self.submitCrear.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitCrear.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
            self.form[indiceObjecto].disabled = true;
          }
        });

        axios.post('/crearProyecto', parametros)
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

          Object.keys(self.form).forEach(function(indiceObjecto, indice) {
            if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
              self.form[indiceObjecto].disabled = false;
            }
          });

          self.submitCrear.content = 'Crear nuevo Proyecto';
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
                mensaje = "Solo se permiten letras y este caracter (',´,0-9)!";
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
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/formNuevoProyecto";
    },
    limpiarErrorCampo: function(formulario,indice){

      self[formulario].camposAtributos[indice].invalidFeedback = "";
      self[formulario].camposAtributos[indice].state = null;

    },
    buscarCliente: function(){

      self.limpiarErrorCampo("form","cliente");
      self.$refs["ref-lista-cliente"].hide();
      self.form.camposAtributos.cliente.listaDropdown.listado = [];
      self.form.camposAtributos.cliente.listaDropdown.noResultado = false;
      self.form.campos.cliente = null;
      self.form.camposAtributos.cliente.valorFocus = null;
      self.form.camposAtributos.cliente.valorBlur = null;

      if(self.form.camposAtributos.cliente.valor !== ''){

        self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpLoading;

        axios.get('/buscarClienteProyecto',{
          params: {
            nombreCliente: self.form.camposAtributos.cliente.valor
          }
        })
        .then(function (response) {

          self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpInit;

          if(response.status === 200 && response.data.response === true){

            self.form.camposAtributos.cliente.listaDropdown.listado = response.data.clientes;

            if(response.data.clientes.length === 0){
              self.form.camposAtributos.cliente.listaDropdown.noResultado = true;
            }

            self.mostrarListado("ref-lista-cliente");

          }else{

            throw "error";

          }

        })
        .catch(error => {

          self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpInit;
          self.form.camposAtributos.cliente.invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás generar la multa.";
          self.form.camposAtributos.cliente.state = false;

        });

      }// Fin if

    },
    mostrarListado: function(indice){

      self.$refs[indice].visibleChangePrevented = true;
      self.$refs[indice].show();

    },
    elegirCliente: function(id, razon_social){

      self.form.campos.funcionario = id;
      self.form.camposAtributos.cliente.valor = razon_social;
      self.form.camposAtributos.cliente.valorFocus = razon_social;
      self.form.camposAtributos.cliente.valorBlur = razon_social;
      self.form.camposAtributos.cliente.state = true;

    },
    valorBlur: function(indice){

      if(self.form.camposAtributos[indice].valorBlur !== null){
        self.form.camposAtributos[indice].valor = self.form.camposAtributos[indice].valorBlur;
      }

    },
    valorFocus: function(indice){

      if(self.form.camposAtributos[indice].valorFocus !== null){
        self.form.camposAtributos[indice].valor = self.form.camposAtributos[indice].valorFocus;
      }

    },
    listadoNoValido: function(indice){

      self.form.camposAtributos[indice].state = false;
      self.form.camposAtributos[indice].invalidFeedback = "Debe seleccionar una opción válida";

    }

  }// Fin methods

});
