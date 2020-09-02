require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import VueNumeric from 'vue-numeric';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import axios from 'axios';
import Vuelidate from 'vuelidate';
import { required, maxLength, minLength } from 'vuelidate/lib/validators';
import zenscroll from 'zenscroll';
import AutoNumeric from 'autonumeric';

Vue.component('loading', require('../components/loading.vue').default);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.component('confirm',require('../components/confirm.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.component('multiselect', Multiselect);
Vue.use(VueNumeric);
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
    comboTipoConceptos: [],
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
        submit:{
          disabled: false,
          html: "",
          htmlInit: "Registrar Factura",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: true
        }
      },
      campos: {
        concepto: null,
        tipoConcepto: null,
        numeroFactura: null,
        montoFactura: null,
        fechaFactura: null,
        numeroControl: null
      },
      camposAtributos: {
        concepto: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        tipoConcepto: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        numeroFactura: {
          busqueda: false,
          disabled: true,
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
        montoFactura: {
          autonumeric: null,
          decPlace: 2,
          decString: ",",
          disabled: true,
          invalidFeedback: "",
          simboloMoneda: "",
          state: null,
          thouSep: "."
        },
        fechaFactura: {
          disabled: true,
          invalidFeedback: "",
          max: null,
          state: null
        },
        fechaCobroFactura: {
          disabled: true,
          invalidFeedback: "",
          max: null,
          state: null,
          value: ""
        },
        numeroControl: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        observaciones: {
          disabled: true,
          invalidFeedback: "",
          state: null,
          value: ""
        }
      },
      info: {
        estatus : "",
        fecha_contratacion: "",
        gerente: "",
        monto_contratado: "",
        monto_facturado: "",
        monto_gastos: "",
        monto_notas_credito: "",
        proyecto: "",
        simbolo_moneda: "",
        socio: ""
      },
      mostrar: false
    },
    loading: true,
    paginador: {
      max: 0,
      numPaginas: 0,
      pagina:1,
      paginar: 0
    },
    permisos: null,
    tabla: {
      alert:{
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      cargando: true,
      encabezado: [],
      registros: []
    }
  },
  validations: {
    form:{
      campos:{
        concepto: {
          required,
          minLength: minLength(5)
        },
        tipoConcepto: {
          required
        },
        numeroFactura: {
          required,
          maxLength: maxLength(20)
        },
        montoFactura: {
          required
        },
        fechaFactura: {
          required
        },
        numeroControl: {
          required,
          maxLength: maxLength(20)
        }
      }
    }
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialAgregarIngresosGastos')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        const ahora = new Date()
        const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate())
        self.form.camposAtributos.fechaFactura.max = hoy;
        self.form.camposAtributos.fechaCobroFactura.max = hoy;

        self.form.info = {
          estatus: response.data.proyecto.estatus,
          fecha_contratacion: response.data.proyecto.fecha_contratacion,
          gerente: response.data.proyecto.gerente,
          monto_contratado: response.data.proyecto.simbolo_moneda+response.data.proyecto.monto_contratado,
          monto_facturado: response.data.proyecto.simbolo_moneda+response.data.facturado_proyecto.monto_facturado,
          monto_gastos: response.data.proyecto.simbolo_moneda+response.data.facturado_proyecto.monto_gasto,
          monto_notas_credito: response.data.proyecto.simbolo_moneda+response.data.facturado_proyecto.monto_notas_credito,
          proyecto: response.data.proyecto.proyecto,
          simbolo_moneda: response.data.proyecto.simbolo_moneda,
          socio: response.data.proyecto.socio
        }

        self.form.camposAtributos.montoFactura.simboloMoneda = response.data.proyecto.simbolo_moneda;

        response.data.conceptos_factura.forEach((item, i) => {
          self.comboTipoConceptos.push({text:item.descripcion, value: {id: item.id, type: item.id_tipo_concepto_factura} });
        });

        //self.form.camposAtributos.concepto.disabled = false;
        self.form.camposAtributos.tipoConcepto.disabled = false;
        /*self.form.camposAtributos.numeroFactura.disabled = false;
        self.form.camposAtributos.montoFactura.disabled = false;
        self.form.camposAtributos.fechaFactura.disabled = false;
        self.form.camposAtributos.fechaCobroFactura.disabled = false;
        self.form.camposAtributos.numeroControl.disabled = false;
        self.form.camposAtributos.observaciones.disabled = false;*/

        self.form.botones.submit.html = self.form.botones.submit.htmlInit;
        self.form.botones.submit.disabled = false;

        if(response.data.permisos.permiso_actualizar){
          self.tabla.encabezado = [
            { key: 'numero', label: '#' },
            { key: 'tipo_concepto', label: 'Tipo Concepto' },
            { key: 'concepto', label: 'Concepto' },
            { key: 'movimiento', label: 'Movimiento' },
            { key: 'numero_factura', label: 'Nº Factura' },
            { key: 'monto_factura', label: 'Monto' },
            { key: 'fecha_factura', label: 'Fecha Fac.' },
            { key: 'numero_control', label: 'Nº Control' },
            { key: 'opciones', label: ' ' }
          ];
        }else{
          self.tabla.encabezado = [
            { key: 'numero', label: '#' },
            { key: 'tipo_concepto', label: 'Tipo Concepto' },
            { key: 'concepto', label: 'Concepto' },
            { key: 'movimiento', label: 'Movimiento' },
            { key: 'numero_factura', label: 'Nº Factura' },
            { key: 'monto_factura', label: 'Monto' },
            { key: 'fecha_factura', label: 'Fecha Fac.' },
            { key: 'numero_control', label: 'Nº Control' }
          ];
        }

        if(response.data.facturas_cargadas.length === 0){

          let mensaje = "No hay facturas cargadas";
          self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

        }

        self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

        self.permisos = response.data.permisos;

        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;

        self.form.mostrar = true;
        self.loading = false;
        self.tabla.cargando = false;

      }else{

        throw "error";

      }

    })
    .catch(error => {

      self.form.mostrar = false;
      self.mostrarAlert(self.alertGeneral, true, "warning", "Existe un error!, consulte con el administrador del sistema.", false, false, 0);
      self.loading = false;

    });

  },
  created: function () {},
  mounted: function () {

    let checkDataInitReady = setInterval(() => {

      if(self.form.mostrar) {

        clearInterval(checkDataInitReady);

        let monto = self.$refs["montoFactura"].$el

        self.form.camposAtributos.montoFactura.autonumeric = new AutoNumeric(monto, {
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
  updated: function () {},
  methods:{
    mostrarConfirm: function(){
      alert("confirm")
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

    },
    limpiarMensajeError: function(refName){

      self.form.camposAtributos[refName].invalidFeedback = "";
      self.form.camposAtributos[refName].state = null;

    },
    registroTabla: function(datos){

      const registros = [];
      datos.forEach((item, i) => {

        var varianteMovimiento;

        switch (item.tipo_movimiento) {
          case 1: varianteMovimiento = "success"; break;
          case 2: varianteMovimiento = "danger"; break;
          case 3: varianteMovimiento = "warning"; break;
          default: variante = "light";
        }

        const factura = {
          numero: (i + 1),
          tipo_concepto: item.tipo_concepto,
          concepto: item.concepto,
          numero_factura: item.numero_factura,
          monto_factura: item.monto_factura,
          fecha_factura: item.fecha_factura,
          numero_control: item.numero_control,
          movimiento: item.movimiento,
          varianteMovimiento: varianteMovimiento,
          id: item.id,
        };

        registros.push(factura);

      });

      return registros;

    },
    paginaAnterior: function(){
      self.paginador.pagina = ((self.paginador.pagina - 1) === 0) ? 1 : (self.paginador.pagina - 1);
      self.buscar();
    },
    paginaSiguiente: function(){
      self.paginador.pagina = ((self.paginador.pagina + 1) > self.paginador.max) ? self.paginador.pagina : (self.paginador.pagina + 1);
      self.buscar();
    },
    numeroPagina: function(e){
      self.buscar();
    },
    tipoConcepto: function(valor){

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "tipoConcepto"){
          self.form.camposAtributos[indice].disabled = true;
        }

      });

      self.form.camposAtributos.numeroFactura.busqueda = false;

      if(valor !== null && valor.trim !== '' && valor.hasOwnProperty("type")){

        const type = parseInt(valor.type);

        if(type === 3){

          self.form.camposAtributos.numeroFactura.disabled = false;
          self.form.camposAtributos.numeroControl.disabled = false;
          self.form.camposAtributos.observaciones.disabled = false;

          self.form.camposAtributos.numeroFactura.busqueda = true;

        }else if(type !== 3){

          self.form.camposAtributos.concepto.disabled = false;
          self.form.camposAtributos.numeroFactura.disabled = false;
          self.form.camposAtributos.montoFactura.disabled = false;
          self.form.camposAtributos.fechaFactura.disabled = false;
          self.form.camposAtributos.fechaCobroFactura.disabled = false;
          self.form.camposAtributos.numeroControl.disabled = false;
          self.form.camposAtributos.observaciones.disabled = false;

        }

      }

      self.limpiarMensajeError('tipoConcepto');

    },
    registrar: async function(){

      var formValido = true;

      await self.mostrarAlert(self.form.alert);

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

        //Obtenemos valores
        let parametros = {
          concepto: self.form.campos.concepto,
          tipo_concepto: self.form.campos.tipoConcepto.id,
          numero_factura: self.form.campos.numeroFactura,
          monto_factura: self.form.camposAtributos.montoFactura.autonumeric.get(),
          fecha_factura: self.form.campos.fechaFactura,
          fecha_cobro_factura: self.form.camposAtributos.fechaCobroFactura.value,
          numero_control: self.form.campos.numeroControl,
          observaciones: self.form.camposAtributos.observaciones.value
        }

        self.form.botones.submit.disabled = true;
        self.form.botones.submit.html = self.form.botones.submit.htmlLoading;

        Object.keys(self.form.camposAtributos).forEach((indice, i) => {

          if(self.form.camposAtributos[indice].hasOwnProperty("disabled")){
            self.form.camposAtributos[indice].disabled = true;
          }

        });

        axios.post('/registrarFactura', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            self.tabla.registros = [];
            self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

            self.form.campos.concepto = null;
            self.form.campos.tipoConcepto = null;
            self.form.campos.numeroFactura = null;
            self.form.campos.fechaFactura = null;
            self.form.camposAtributos.fechaCobroFactura.value = "";
            self.form.campos.numeroControl = null;
            self.form.camposAtributos.observaciones.value = "";

            self.form.info.monto_facturado = self.form.info.simbolo_moneda+response.data.facturado_proyecto.monto_facturado;
            self.form.info.monto_gastos = self.form.info.simbolo_moneda+response.data.facturado_proyecto.monto_gasto;

            Object.keys(self.form.camposAtributos).forEach((indice, i) => {

              if(self.form.camposAtributos[indice].hasOwnProperty("disabled")){
                self.form.camposAtributos[indice].disabled = false;
              }

            });

            self.$nextTick(() => {
              self.$v.$reset();
            });

            self.form.botones.submit.disabled = false;
            self.form.botones.submit.html = self.form.botones.submit.htmlInit;

            self.mostrarAlert(self.form.alert, true, "success", response.data.message, true, true, 10);

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

          if(error.message){

            var mensaje = error.message;
            var variante = "warning";

          }else{

            var mensaje = "Existe un error!, consulte con el administrador del sistema.";
            var variante = "danger";

          }

          self.mostrarAlert(self.form.alert, true, variante, mensaje, true, true, 10);

        });

      }// Fin if

    },
    validadorMensajes: function(indice,campo){

      var mensaje,
          respuesta = true;

      if(!campo[indice] && indice === "required"){
        mensaje = "Este campo es requerido!";
        respuesta = false;
      }else if(!campo[indice] && indice === "maxLength"){
        let maxChar = campo.$params[indice].max;
        mensaje = "Debe contener como máximo "+maxChar+" caracteres!";
        respuesta = false;
      }else if(!campo[indice] && indice === "minLength"){
        let minChar = campo.$params[indice].min;
        mensaje = "Debe contener al menos "+minChar+" caracteres!";
        respuesta = false;
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        e.preventDefault();
      }

    },
    buscarFactura: function(){

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
  }

});
