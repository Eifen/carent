require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Vuelidate from 'vuelidate';
import AutoNumeric from 'autonumeric';
import { required, minLength, minValue } from 'vuelidate/lib/validators';
var self;

Vue.component('multiselect', Multiselect);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
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
    comboEmpresas: [],
    comboDivisiones: [],
    comboMonedas: [],
    refreshForm: false,
    form: {
      alert: {
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      botones: {
        cancelar: {
          disabled: false,
          html: "No, deseo cancelar esta acción",
          show: false
        },
        confirmar: {
          html: "Crear Nuevo Proyecto",
          show: true
        },
        refresh: {
          html: "Quiero crear un nuevo Proyecto!",
          show: false
        },
        submit: {
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de crear este proyecto",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        }
      },
      campos: {
        descripcion: null,
        cliente: null,
        estatus: null,
        fechaContratacion: null,
        socio: null,
        socioCalidad: null,
        gerente: null,
        montoEn: null,
        monto: null,
        empresa: null,
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
        empresa: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        fechaContratacion: {
          disabled: true,
          invalidFeedback: "",
          max: null,
          state: null
        },
        socio: {
          disabled: true,
          help: "",
          helpInit: "Socio que lleva el proyecto",
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
        socioCalidad: {
          disabled: true,
          help: "",
          helpInit: "Socio de calidad",
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
        gerente: {
          disabled: true,
          help: "",
          helpInit: "Quien gerencia el proyecto",
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
        montoEn: {
          disabled: true,
          invalidFeedback: "",
          simbolo: "",
          state: null
        },
        monto: {
          autonumeric: null,
          decPlace: 2,
          decString: ",",
          disabled: true,
          invalidFeedback: "",
          state: null,
          thouSep: "."
        },
        divisiones: {
          disabled: true,
          divisiones: [],
          invalidFeedback: "",
          state: null
        },
        horas:{
          disabled: true,
          state: null
        }
      },
      mostrar: false
    },
    loading: true
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
        socio: {
          required
        },
        socioCalidad: {
          required
        },
        gerente: {
          required
        },
        montoEn: {
          required
        },
        monto: {
          required
        },
        empresa: {
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

        const ahora = new Date()
        const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate())
        self.form.camposAtributos.fechaContratacion.max = hoy;

        response.data.estatus.forEach((item, i) => {
          self.comboEstatus.push({text:item.descripcion, value: item.id});
        });

        response.data.empresas.forEach((item, i) => {
          self.comboEmpresas.push({text:item.razon_social, value: item.id});
        });

        response.data.monedas.forEach((item, i) => {
          self.comboMonedas.push({text:item.moneda, value: item.id, simbolo: item.simbolo});
        });

        self.comboDivisiones = response.data.divisiones;
        self.form.camposAtributos.descripcion.disabled = false;
        self.form.camposAtributos.cliente.disabled = false;
        self.form.camposAtributos.cliente.help = self.form.camposAtributos.cliente.helpInit;
        self.form.camposAtributos.estatus.disabled = false;
        self.form.camposAtributos.empresa.disabled = false;
        self.form.camposAtributos.fechaContratacion.disabled = false;
        self.form.camposAtributos.socio.disabled = false;
        self.form.camposAtributos.socio.help = self.form.camposAtributos.socio.helpInit;
        self.form.camposAtributos.socioCalidad.disabled = false;
        self.form.camposAtributos.socioCalidad.help = self.form.camposAtributos.socioCalidad.helpInit;
        self.form.camposAtributos.gerente.disabled = false;
        self.form.camposAtributos.gerente.help = self.form.camposAtributos.gerente.helpInit;
        self.form.camposAtributos.montoEn.disabled = false;
        self.form.camposAtributos.divisiones.disabled = false;

        self.form.botones.submit.html = self.form.botones.submit.htmlInit;
        self.form.botones.submit.disabled = false;

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

      if(self.form.mostrar) {

        clearInterval(checkDataInitReady);

        let monto = self.$refs["monto"].$el

        self.form.camposAtributos.monto.autonumeric = new AutoNumeric(monto, {
          decimalPlaces: 2,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          emptyInputBehavior: 0,
          maximumValue: '99999999999999999999.99',
          minimumValue: 0,
          modifyValueOnWheel: false
        });

      }

    }, 1000);

  },
  methods:{

    asignarHoras: function(valor){

      self.form.camposAtributos.divisiones.divisiones = [];

      valor.forEach(function(item, index){

        const division = {
          descripcion: item.descripcion,
          gerente: {
            disabled: true,
            help: "",
            helpInit: "Gerente para esta división, él estará a cargo de su división para este proyecto",
            helpLoading: '<i class="fas fa-cog fa-spin"></i> buscando',
            id: null,
            invalidFeedback: "",
            listado: [],
            state: null
          },
          horas: {
            invalidFeedback: "",
            state: null,
            value: 0,
          },
          id: item.id
        }

        self.$set(self.form.camposAtributos.divisiones.divisiones, index, division);
        self.gerenteDivision(index, item.id);

      });

      if(self.form.camposAtributos.divisiones.divisiones.length === 0){
        self.form.campos.horas = 0;
        self.form.camposAtributos.horas.invalidFeedback = "";
        self.form.camposAtributos.horas.state = null;
      }

      self.horasTotales();

    },
    gerenteDivision: function(index, id_division){

      self.form.camposAtributos.divisiones.divisiones[index].gerente.help = self.form.camposAtributos.divisiones.divisiones[index].gerente.helpLoading;

      axios.get('/proyectoGerentesDivision',{
        params: {
          id_division: id_division
        }
      })
      .then(function (response) {

        self.form.camposAtributos.divisiones.divisiones[index].gerente.help = self.form.camposAtributos.divisiones.divisiones[index].gerente.helpInit;
        self.form.camposAtributos.divisiones.divisiones[index].gerente.disabled = false;

        self.form.camposAtributos.divisiones.divisiones[index].gerente.listado = [];
        response.data.gerentes.forEach((item, i) => {
          self.form.camposAtributos.divisiones.divisiones[index].gerente.listado.push({text:item.nombre, value: item.id});
        });

      })
      .catch(error => {

        self.form.camposAtributos.divisiones.divisiones[index].gerente.help = self.form.camposAtributos.divisiones.divisiones[index].gerente.helpInit;
        self.form.camposAtributos.divisiones.divisiones[index].gerente.disabled = false;
        self.form.camposAtributos.divisiones.divisiones[index].gerente.state = false;

        var mensaje = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";
        self.form.camposAtributos.divisiones.divisiones[index].gerente.invalidFeedback = mensaje;

      });

    },
    cantidadHora(value){

      let regex = /^(?:[1-9][0-9]*)$/;

      if(regex.test(value)){
        return value;
      }else{
        return "";
      }

    },
    horaDivision: function(index){

      self.limpiarMensajeError(self.form.camposAtributos.divisiones.divisiones[index].horas);
      self.horasTotales();

    },
    horasTotales: function(){

      var total = 0;

      for(var i = 0; i < self.form.camposAtributos.divisiones.divisiones.length; i++){
        total = total + parseInt(self.form.camposAtributos.divisiones.divisiones[i].horas.value);
      }

      total = (isNaN(total)) ? 0 : total;

      self.form.campos.horas = total;

      self.limpiarMensajeError(self.form.camposAtributos.horas);

    },
    limpiarMensajeError: function(objeto){

      objeto.state = null;
      objeto.invalidFeedback = "";

    },
    monedaSeleccionada: function(){

      let valor = self.$refs["montoEn"].$el.value;

      if((valor.trim() !== "") && (valor !== null)){
        self.form.camposAtributos.monto.disabled = false;
      }else{
        self.form.camposAtributos.monto.disabled = true;
      }

      self.limpiarMensajeError(self.form.camposAtributos.monto);

    },
    confirmarCrearProyecto: function(){

      var formValido = true;

      self.mostrarAlertForm(self.form.alert);

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("state")){
          self.form.camposAtributos[indice].state = (self.form.camposAtributos[indice].state === true) ? true : null;
        }

        if(self.form.camposAtributos[indice].hasOwnProperty("invalidFeedback")){
          self.form.camposAtributos[indice].invalidFeedback = "";
        }

      });

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

      if(formValido){

        Object.keys(self.form.camposAtributos.divisiones.divisiones).forEach((indice, i) => {
          self.form.camposAtributos.divisiones.divisiones[indice].gerente.state = null;
          self.form.camposAtributos.divisiones.divisiones[indice].gerente.invalidFeedback = "";
        });

        for(var i = 0; i < self.form.camposAtributos.divisiones.divisiones.length; i++){

          const gerente = self.form.camposAtributos.divisiones.divisiones[i].gerente.id;
          const hora = self.form.camposAtributos.divisiones.divisiones[i].horas.value;

          if(gerente === null){
            self.form.camposAtributos.divisiones.divisiones[i].gerente.invalidFeedback = "Debe elegir a un gerente";
            self.form.camposAtributos.divisiones.divisiones[i].gerente.state = false;
            formValido = false;
            zenscroll.toY(self.$refs['division-'+i].$el);
            break
          }else if(hora === 0){
            self.form.camposAtributos.divisiones.divisiones[i].horas.invalidFeedback = "Debe ser mayor a 0";
            self.form.camposAtributos.divisiones.divisiones[i].horas.state = false;
            formValido = false;
            zenscroll.toY(self.$refs['hora-'+i].$el);
            break
          }

        }

      }

      if(formValido){

        self.form.botones.confirmar.show = false;
        self.form.botones.submit.show = true;
        self.form.botones.cancelar.show = true;

        self.mostrarAlertForm(self.form.alert, true, "warning", "¿Estas seguro de crear este nuevo proyecto?", false, false, 0);

      }

    },
    cancelarCrearProyecto: function(){

      self.form.botones.confirmar.show = true;
      self.form.botones.submit.show = false;
      self.form.botones.cancelar.show = false;

      self.mostrarAlertForm(self.form.alert);

    },
    crear: async function(){

      self.mostrarAlertForm(self.form.alert);

      const divisiones = [];
      self.form.camposAtributos.divisiones.divisiones.forEach((item, i) => {
        let hora = parseInt(item.horas.value);
        divisiones.push({id: item.id, horas: hora, id_gerente: item.gerente.id});
      });

      //Obtenemos valores
      let parametros = {
        descripcion:  self.form.campos.descripcion,
        cliente: self.form.campos.cliente,
        fechaContratacion: self.form.campos.fechaContratacion,
        socio: self.form.campos.socio,
        socioCalidad: self.form.campos.socioCalidad,
        gerente: self.form.campos.gerente,
        divisiones: divisiones,
        estatus: self.form.campos.estatus,
        id_moneda: self.form.campos.montoEn,
        monto: self.form.camposAtributos.monto.autonumeric.get(),
        empresa: self.form.campos.empresa
      }

      self.form.botones.cancelar.disabled = true;
      self.form.botones.submit.disabled = true;
      self.form.botones.submit.html = self.form.botones.submit.htmlLoading;

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "horas"){
          self.form.camposAtributos[indice].disabled = true;
        }

      });

      axios.post('/crearProyecto', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.form.botones.cancelar.show = false;
          self.form.botones.submit.show = false;
          self.form.botones.refresh.show = true;

          self.mostrarAlertForm(self.form.alert, true, "success", response.data.message, false, false, 0);

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        Object.keys(self.form.camposAtributos).forEach((indice, i) => {

          if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "horas"){
            self.form.camposAtributos[indice].disabled = false;
          }

        });

        self.form.botones.submit.disabled = false;
        self.form.botones.submit.html = self.form.botones.submit.htmlInit
        self.form.botones.cancelar.disabled = false;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlertForm(self.form.alert, true, variante, mensaje, true, true, 10);

      });

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
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/formNuevoProyecto";
    },
    buscarCliente: function(){

      self.limpiarMensajeError(self.form.camposAtributos.cliente);
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
          self.form.camposAtributos.cliente.invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás generar el proyecto.";
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
    buscarSocio: function(tipoSocio){

      self.limpiarMensajeError(self.form.camposAtributos[tipoSocio]);
      self.$refs["ref-lista-"+tipoSocio].hide();
      self.form.camposAtributos[tipoSocio].listaDropdown.listado = [];
      self.form.camposAtributos[tipoSocio].listaDropdown.noResultado = false;
      self.form.campos[tipoSocio] = null;
      self.form.camposAtributos[tipoSocio].valorFocus = null;
      self.form.camposAtributos[tipoSocio].valorBlur = null;

      if(self.form.camposAtributos[tipoSocio].valor !== ''){

        self.form.camposAtributos[tipoSocio].help = self.form.camposAtributos[tipoSocio].helpLoading;

        axios.get('/buscarSocioProyecto',{
          params: {
            nombreSocio: self.form.camposAtributos[tipoSocio].valor
          }
        })
        .then(function (response) {

          self.form.camposAtributos[tipoSocio].help = self.form.camposAtributos[tipoSocio].helpInit;

          if(response.status === 200 && response.data.response === true){

            self.form.camposAtributos[tipoSocio].listaDropdown.listado = response.data.socios;

            if(response.data.socios.length === 0){
              self.form.camposAtributos[tipoSocio].listaDropdown.noResultado = true;
            }

            self.mostrarListado("ref-lista-"+tipoSocio);

          }else{

            throw "error";

          }

        })
        .catch(error => {

          self.form.camposAtributos[tipoSocio].help = self.form.camposAtributos[tipoSocio].helpInit;
          self.form.camposAtributos[tipoSocio].invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás generar el proyecto.";
          self.form.camposAtributos[tipoSocio].state = false;

        });

      }// Fin if

    },
    elegirSocio: function(id, nombre, tipoSocio){

      self.form.camposAtributos[tipoSocio].valor = nombre;
      self.form.camposAtributos[tipoSocio].valorFocus = nombre;
      self.form.camposAtributos[tipoSocio].valorBlur = nombre;
      self.form.camposAtributos[tipoSocio].state = true;
      self.form.campos[tipoSocio] = id;

    },
    buscarGerente: function(){

      self.limpiarMensajeError(self.form.camposAtributos.gerente);
      self.$refs["ref-lista-gerente"].hide();
      self.form.camposAtributos.gerente.listaDropdown.listado = [];
      self.form.camposAtributos.gerente.listaDropdown.noResultado = false;
      self.form.campos.gerente = null;
      self.form.camposAtributos.gerente.valorFocus = null;
      self.form.camposAtributos.gerente.valorBlur = null;

      if(self.form.camposAtributos.gerente.valor !== ''){

        self.form.camposAtributos.gerente.help = self.form.camposAtributos.gerente.helpLoading;

        axios.get('/buscarGerenteProyecto',{
          params: {
            nombreGerente: self.form.camposAtributos.gerente.valor
          }
        })
        .then(function (response) {

          self.form.camposAtributos.gerente.help = self.form.camposAtributos.gerente.helpInit;

          if(response.status === 200 && response.data.response === true){

            self.form.camposAtributos.gerente.listaDropdown.listado = response.data.gerentes;

            if(response.data.gerentes.length === 0){
              self.form.camposAtributos.gerente.listaDropdown.noResultado = true;
            }

            self.mostrarListado("ref-lista-gerente");

          }else{

            throw "error";

          }

        })
        .catch(error => {

          self.form.camposAtributos.gerente.help = self.form.camposAtributos.gerente.helpInit;
          self.form.camposAtributos.gerente.invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás generar el proyecto.";
          self.form.camposAtributos.gerente.state = false;

        });

      }// Fin if

    },
    elegirGerente: function(id, nombre){

      self.form.camposAtributos.gerente.valor = nombre;
      self.form.camposAtributos.gerente.valorFocus = nombre;
      self.form.camposAtributos.gerente.valorBlur = nombre;
      self.form.camposAtributos.gerente.state = true;
      self.form.campos.gerente = id;

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
