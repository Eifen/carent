require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import AutoNumeric from 'autonumeric';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Vuelidate from 'vuelidate';
import { required, minLength, minValue } from 'vuelidate/lib/validators';
var self;

Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.use(Vuelidate);

new Vue({

  el: '#app',
  data: {
    alertGeneral: {
      contador: false,
      iconCerrar: false,
      mensaje: "",
      mostrar: false,
      ocultarSeg: 0,
      variante: ""
    },
    comboEstatus: [],
    comboDivisiones: [],
    comboMonedas: [],
    refreshForm: false,
    form: {
      campos: {
        descripcion: null,
        cliente: null,
        estatus: null,
        fechaContratacion: null,
        montoEn: null,
        monto: null,
        divisiones: null,
        horas: 0
      },
      camposAtributos: {
        descripcion:{
          disabled: true,
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
          state: null
        },
        fechaContratacion: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        montoEn: {
          disabled: true,
          invalidFeedback: "",
          simbolo: "",
          state: null
        },
        monto: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        divisiones: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        horas:{
          asignar: false,
          disabled: true,
          state: null
        }
      },
      mostrar: false
    },
    loading: true,
    submitCrear: {
      content: "Crear nuevo Proyecto",
      disabled: true,
      show:true
    }
  },
  validations: {
    form:{
      campos:{
        descripcion: {
          required,
          minLength: minLength(5)
        },
        cliente: {
          required
        },
        estatus: {
          required
        },
        fechaContratacion: {
          required
        },
        montoEn: {
          required
        },
        monto: {
          required
        },
        divisiones: {
          required
        },
        horas: {
          required,
          minValue: minValue(1)
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

        response.data.monedas.forEach((item, i) => {
          self.comboMonedas.push({text:item.moneda, value: item.id, simbolo: item.simbolo});
        });

        self.comboDivisiones = response.data.divisiones;
        self.form.camposAtributos.descripcion.disabled = false;
        self.form.camposAtributos.cliente.disabled = false;
        self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpInit;
        self.form.camposAtributos.estatus.disabled = false;
        self.form.camposAtributos.fechaContratacion.disabled = false;
        self.form.camposAtributos.montoEn.disabled = false;
        self.form.camposAtributos.divisiones.disabled = false;
        self.submitCrear.disabled = false;

        self.form.mostrar = true;
        self.loading = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.mostrarAlertForm(self.alertGeneral, true, "warning", "Existe un error!, consulte con el administrador del sistema.", false, false, 0);
      self.loading = false;
      self.form.mostrar = true;

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

        self.form.camposAtributos.monto.autonumeric = new AutoNumeric('#monto', {
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

      self.form.camposAtributos.horas.asignar = (valor.length > 0) ? true : false;

      if(!self.form.camposAtributos.horas.asignar){
        self.form.camposAtributos.horas.value = 0;
        self.form.camposAtributos.horas.invalidFeedback = "";
        self.form.camposAtributos.horas.state = null;
      }

    },
    formatoHoraAsignada: function(input){

      let regex = /^(?:[1-9][0-9]*|0)$/;
    
      if(!regex.test(input.target.value)){
        input.preventDefault();
        self.horasTotales();
      }

      self.form.camposAtributos.horas.invalidFeedback = "";
      self.form.camposAtributos.horas.state = null;

    },
    horasTotales: function(){

      var total = 0;

      const horas = document.getElementsByClassName("hora-asignada");

      for(var i = 0; i < horas.length; i++){

        console.log(parseInt(self.$refs["asignar-"+i][0].$el.value))

      }

      /*$(".hora-asignada").each(function(index,item){
        let hora = ($(item).val().trim() === "") ? 0 : parseInt($(item).val());
        total = parseInt(total) + hora;
      });

      self.form.horas.value = total;*/

    },
    limpiarMensajeError: function(refName){

      self.form.camposAtributos[refName].invalidFeedback = "";
      self.form.camposAtributos[refName].state = null;

    },
    monedaSeleccionada: function(){

      let valor = self.$refs["montoEn"].$el.value;

      if((valor.trim() !== "") && (valor !== null)){

        self.form.camposAtributos.monto.autonumeric.update({ currencySymbol : self.form.camposAtributos.montoEn.simbolo+" "});
        self.form.camposAtributos.monto.disabled = false;

      }else{

        self.form.camposAtributos.monto.autonumeric.update({ currencySymbol : ""});
        self.form.camposAtributos.monto.disabled = true;

      }

      self.limpiarMensajeError("montoEn");

    },
    crear: async function(){

      var formValido = true;

      await self.mostrarAlertForm(self.alertGeneral);

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("state")){
          self.form.camposAtributos[indice].state = (self.form.camposAtributos[indice].state === true) ? true : null;
        }

        if(self.form.camposAtributos[indice].hasOwnProperty("invalidFeedback")){
          self.form.camposAtributos[indice].invalidFeedback = "";
        }

      });

      var formValido = true;

      const arrayCampos = Object.keys(self.form.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.form.campos[indice];
        campo.$touch();

        if(campo.$invalid){

          self.form.camposAtributos[indice].state = false;
          const valorCampo = self.$v.form.campos[indice].$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.form.camposAtributos[indice].invalidFeedback = mensajeError.mensaje;

            if(!mensajeError.respuesta){
              break
            }

          }

          zenscroll.toY(self.$refs[indice].$el);
          formValido = false;
          break;

        }

      }

      console.log("crear");

      return;

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

        let monto = (self.form.camposAtributos.monto.autonumeric === null) ? 0 : self.form.camposAtributos.monto.autonumeric.get()

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
        console.log(campo.$params[indice])
        respuesta = false;
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/formNuevoProyecto";
    },
    buscarCliente: function(){

      self.limpiarMensajeError("cliente");
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

      self.form.camposAtributos.cliente.valor = razon_social;
      self.form.camposAtributos.cliente.valorFocus = razon_social;
      self.form.camposAtributos.cliente.valorBlur = razon_social;
      self.form.camposAtributos.cliente.state = true;
      self.form.campos.cliente = id;

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

    },
    mostrarAlertForm: function(alert, mostrar = false, variante = "", mensaje = "", iconCerrar = false, contador = false, ocultarSeg = 0){

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
