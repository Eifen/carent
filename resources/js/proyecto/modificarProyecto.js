require('bootstrap');
import Vue from 'vue';
import { BootstrapVue } from 'bootstrap-vue';
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
Vue.use(Vuelidate);

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
                 monedas: response.data.monedas,
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
    idProyecto: null,
    /*ELIMINAR*/
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    /***********/
    comboClientes: [],
    comboEstatus: [],
    comboDivisiones: [],
    comboMonedas: [],
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
          html: "Modificar Proyecto",
          show: true
        },
        submit: {
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de modificar este proyecto",
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
        gerente: null,
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
      montoEn:{
        disabled: true,
        value: ""
      },
      monto:{
        autonumeric: null,
        disabled: true,
        simbolo: "",
        value: 0
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
    /*ELIMINAR*/
    alert:{
      message: "",
      mostrar: false
    },
    submitActualizar: {
      content: "Actualizar Datos",
      disabled: false,
      show:true
    }
    /*************/
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
        gerente: {
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
  beforeCreate: async function(){

    self = this;

    const dataInit = await datosIniciales();

    if(dataInit.response){

      const ahora = new Date()
      const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate())
      self.form.camposAtributos.fechaContratacion.max = hoy;

      dataInit.estatus.forEach((item, i) => {
        self.comboEstatus.push({text:item.descripcion, value: item.id});
      });

      self.idProyecto = dataInit.info.id;
      self.form.campos.descripcion = dataInit.info.descripcion;
      self.form.camposAtributos.cliente.valor = dataInit.info.razon_social;
      self.form.campos.estatus = dataInit.info.id_estatus;
      self.form.campos.fechaContratacion = dataInit.info.fecha_contratacion;
      //self.form.camposAtributos.socio.valor =
      //self.form.camposAtributos.gerente.valor = 



      self.divisiones_v = dataInit.infodivi;
      self.form.descripcion.value = dataInit.info.descripcion;
      self.form.cliente.value = dataInit.info.id_cliente;
      self.form.horas.value = dataInit.info.horas_contratadas;
      self.form.estatus.value = dataInit.info.id_estatus;
      self.form.montoEn.value = dataInit.info.id_moneda;
      self.form.monto.value = dataInit.info.monto;
      self.form.monto.simbolo = dataInit.info.simbolo;
      self.comboClientes = dataInit.clientes;
      //self.comboEstatus = dataInit.estatus;
      self.comboDivisiones = dataInit.divisiones;
      self.comboMonedas = dataInit.monedas;
      self.form.descripcion.disabled = false;
      self.form.cliente.disabled = false;
      self.form.camposAtributos.fechaContratacion.disabled = false;
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
  created: async function () {

    let checkDataInitReady = setInterval(() => {

      if (self.form.mostrar) {

        clearInterval(checkDataInitReady);

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
  methods:{

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
    buscarSocio: function(){

      self.limpiarMensajeError(self.form.camposAtributos.socio);
      self.$refs["ref-lista-socio"].hide();
      self.form.camposAtributos.socio.listaDropdown.listado = [];
      self.form.camposAtributos.socio.listaDropdown.noResultado = false;
      self.form.campos.socio = null;
      self.form.camposAtributos.socio.valorFocus = null;
      self.form.camposAtributos.socio.valorBlur = null;

      if(self.form.camposAtributos.socio.valor !== ''){

        self.form.camposAtributos.socio.help = self.form.camposAtributos.socio.helpLoading;

        axios.get('/buscarSocioProyecto',{
          params: {
            nombreSocio: self.form.camposAtributos.socio.valor
          }
        })
        .then(function (response) {

          self.form.camposAtributos.socio.help = self.form.camposAtributos.socio.helpInit;

          if(response.status === 200 && response.data.response === true){

            self.form.camposAtributos.socio.listaDropdown.listado = response.data.socios;

            if(response.data.socios.length === 0){
              self.form.camposAtributos.socio.listaDropdown.noResultado = true;
            }

            self.mostrarListado("ref-lista-socio");

          }else{

            throw "error";

          }

        })
        .catch(error => {

          self.form.camposAtributos.socio.help = self.form.camposAtributos.socio.helpInit;
          self.form.camposAtributos.socio.invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás generar el proyecto.";
          self.form.camposAtributos.socio.state = false;

        });

      }// Fin if

    },
    elegirSocio: function(id, nombre){

      self.form.camposAtributos.socio.valor = nombre;
      self.form.camposAtributos.socio.valorFocus = nombre;
      self.form.camposAtributos.socio.valorBlur = nombre;
      self.form.camposAtributos.socio.state = true;
      self.form.campos.socio = id;

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
    monedaSeleccionada: function(){

      let valor = self.$refs["montoEn"].$el.value;

      if((valor.trim() !== "") && (valor !== null)){
        self.form.camposAtributos.monto.disabled = false;
      }else{
        self.form.camposAtributos.monto.disabled = true;
      }

      self.limpiarMensajeError(self.form.camposAtributos.monto);

    },
    confirmarModificarProyecto: function(){

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
    limpiarMensajeError2: function(refName){

      self.form.camposAtributos[refName].invalidFeedback = "";
      self.form.camposAtributos[refName].state = null;

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
          fechaContratacion: self.form.campos.fechaContratacion,
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
