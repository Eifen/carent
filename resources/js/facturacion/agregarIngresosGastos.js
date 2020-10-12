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
import { required, requiredIf, maxLength, minLength } from 'vuelidate/lib/validators';
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
    botones: {
      agregarFactura: {
        disabled: false,
        html: "",
        htmlInit: "AGREAR FACTURA",
        htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
        show: true
      }
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
        cancelar: {
          disabled: false,
          html: "No, deseo cancelar esta acción",
          show: false
        },
        confirmar: {
          html: "Registrar Factura/Gasto",
          show: true
        },
        submit:{
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de realizar esta acción",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        }
      },
      campos: {
        tipoConcepto: null,
        numeroFactura: null,
        montoFactura: null,
        fechaFactura: null,
        concepto: null,
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
          help: "",
          helpInit: "Ejemplo: AABB0123C-5",
          helpLoading: '<i class="fas fa-cog fa-spin"></i> buscando',
          idFacturaAnular: null,
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
        monto_otros_gastos: "",
        proyecto: "",
        socio: ""
      },
      mostrar: false
    },
    loading: true,
    modalMasInfo: {
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
          html: "No",
          show: false
        },
        confirmar: {
          html: "Modificar Factura/Gasto",
          show: true
        },
        submit:{
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de realizar esta acción",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        }
      },
      form: {
        campos: {
          montoFacturaMod: null,
          fechaFacturaMod: null,
          conceptoMod: null,
          numeroControlMod: null
        },
        camposAtributos:{
          montoFacturaMod: {
            autonumeric: null,
            decPlace: 2,
            decString: ",",
            disabled: true,
            invalidFeedback: "",
            simboloMoneda: "",
            state: null,
            thouSep: "."
          },
          conceptoMod: {
            disabled: true,
            invalidFeedback: "",
            state: null
          },
          montoFacturaMod: {
            autonumeric: null,
            decPlace: 2,
            decString: ",",
            disabled: true,
            invalidFeedback: "",
            state: null,
            thouSep: "."
          },
          fechaFacturaMod: {
            disabled: true,
            invalidFeedback: "",
            max: null,
            state: null
          },
          fechaCobroFacturaMod: {
            disabled: true,
            invalidFeedback: "",
            max: null,
            state: null,
            value: ""
          },
          numeroControlMod: {
            disabled: true,
            invalidFeedback: "",
            state: null
          },
          observacionesMod: {
            disabled: true,
            invalidFeedback: "",
            state: null,
            value: ""
          }
        }
      },
      idConceptoFactura: null,
      idFactura: null,
      titulo: ""
    },
    modalEliminar: {
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
          html: "No",
          show: true
        },
        hide: {
          show: false
        },
        submit:{
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de realizar esta acción",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: true
        }
      }
    },
    paginador: {
      max: 0,
      numPaginas: 0,
      pagina:1,
      paginar: 0
    },
    permisos: null,
    simboloMoneda: null,
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
        tipoConcepto: {
          required
        },
        numeroFactura: {
          required: requiredIf(function() {

            const requerido = (!this.form.camposAtributos.numeroFactura.disabled && !this.form.camposAtributos.tipoConcepto.disabled) ? true : false;
            return requerido;

          }),
          maxLength: maxLength(20)
        },
        montoFactura: {
          required
        },
        fechaFactura: {
          required: requiredIf(function() {
            return (!this.form.camposAtributos.fechaFactura.disabled);
          })
        },
        concepto: {
          required: requiredIf(function() {
            return (!this.form.camposAtributos.concepto.disabled);
          }),
          minLength: minLength(5)
        },
        numeroControl: {
          required: requiredIf(function() {
            return (!this.form.camposAtributos.numeroControl.disabled);
          }),
          maxLength: maxLength(20)
        }
      }
    },
    modalMasInfo: {
      form:{
        campos:{
          montoFacturaMod: {
            required
          },
          fechaFacturaMod: {
            required: requiredIf(function() {
              return (!this.modalMasInfo.form.camposAtributos.fechaFacturaMod.disabled);
            })
          },
          conceptoMod: {
            required: requiredIf(function() {
              return (!this.modalMasInfo.form.camposAtributos.conceptoMod.disabled);
            }),
            minLength: minLength(5)
          },
          numeroControlMod: {
            required: requiredIf(function() {
              return (!this.modalMasInfo.form.camposAtributos.numeroControlMod.disabled);
            }),
            maxLength: maxLength(20)
          }
        }
      }
    }
  },
  beforeCreate: function(){

    self = this;

    //Se utiliza el metodo get para obtener los valores inciales
    axios.get('/dataInicialAgregarIngresosGastos',{
      params: {
        id_proyecto: proyecto_id
      }
    })
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        const ahora = new Date()
        const hoy = new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate())
        self.form.camposAtributos.fechaFactura.max = hoy;
        self.form.camposAtributos.fechaCobroFactura.max = hoy;
        self.modalMasInfo.form.camposAtributos.fechaFacturaMod.max = hoy;
        self.modalMasInfo.form.camposAtributos.fechaCobroFacturaMod.max = hoy;

        self.simboloMoneda = response.data.proyecto.simbolo_moneda;

        self.form.info = {
          estatus: response.data.proyecto.estatus,
          fecha_contratacion: response.data.proyecto.fecha_contratacion,
          gerente: response.data.proyecto.gerente,
          monto_contratado: self.simboloMoneda+response.data.proyecto.monto_contratado,
          monto_facturado: self.simboloMoneda+response.data.facturado_proyecto.monto_facturado,
          monto_gastos: self.simboloMoneda+response.data.facturado_proyecto.monto_gasto,
          monto_notas_credito: self.simboloMoneda+response.data.facturado_proyecto.monto_notas_credito,
          monto_otros_gastos: self.simboloMoneda+response.data.facturado_proyecto.monto_otros_gastos,
          proyecto: response.data.proyecto.proyecto,
          simbolo_moneda: self.simboloMoneda,
          socio: response.data.proyecto.socio
        }

        self.form.camposAtributos.numeroFactura.help = self.form.camposAtributos.numeroFactura.helpInit;

        response.data.conceptos_factura.forEach((item, i) => {
          self.comboTipoConceptos.push({text:item.descripcion, value: {id: item.id, type: item.id_tipo_concepto_factura} });
        });

        self.form.camposAtributos.tipoConcepto.disabled = false;
        self.form.camposAtributos.observaciones.disabled = false;

        self.form.botones.submit.html = self.form.botones.submit.htmlInit;
        self.form.botones.submit.disabled = false;

        self.modalMasInfo.botones.submit.html = self.modalMasInfo.botones.submit.htmlInit;
        self.modalEliminar.botones.submit.html = self.modalEliminar.botones.submit.htmlInit;

        self.tabla.encabezado = [
          { key: 'numero', label: '#' },
          { key: 'numero_factura', label: 'Nº Factura' },
          { key: 'tipo_concepto', label: 'Tipo Concepto' },
          { key: 'movimiento', label: 'Movimiento' },
          { key: 'monto_factura_formatted', label: 'Monto' },
          { key: 'fecha_factura_formatted', label: 'Fecha Fact.' },
          { key: 'opciones', label: ' ' }
        ];

        if(response.data.facturas_cargadas.length === 0){

          let mensaje = "No hay facturas cargadas";
          self.mostrarAlert(self.tabla.alert, true, "warning", mensaje, false, false, 0);

        }

        self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

        self.permisos = response.data.permisos;

        self.paginador.numPaginas = (parseInt(response.data.numero_paginas) === 0) ? 1 : response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;

        self.botones.agregarFactura.html = self.botones.agregarFactura.htmlInit;

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

        self.$refs["agregar-factura"].$on('shown', () => {

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

        });

        self.$refs["agregar-factura"].$on('hidden', () => {

          self.form.campos.concepto = null;
          self.form.campos.tipoConcepto = null;
          self.form.campos.numeroFactura = null;
          self.form.campos.fechaFactura = null;
          self.form.camposAtributos.fechaCobroFactura.value = "";
          self.form.campos.numeroControl = null;
          self.form.camposAtributos.observaciones.value = "";
          self.form.campos.montoFactura = null;
          self.form.camposAtributos.montoFactura.autonumeric.set(0);

          Object.keys(self.form.camposAtributos).forEach((indice, i) => {

            if(self.form.camposAtributos[indice].hasOwnProperty("state")){
              self.form.camposAtributos[indice].state = null;
            }

            if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "tipoConcepto" && indice !== "observaciones"){
              self.form.camposAtributos[indice].disabled = true;
            }else if(indice === "tipoConcepto" || indice === "observaciones"){
              self.form.camposAtributos[indice].disabled = false;
            }

          });

          self.form.botones.confirmar.show = true;
          self.form.botones.submit.show = false;
          self.form.botones.cancelar.show = false;

          self.mostrarAlert(self.form.alert);

          self.form.camposAtributos.numeroFactura.busqueda = false;
          self.form.camposAtributos.numeroFactura.valor = null;
          self.form.camposAtributos.numeroFactura.valorFocus = null;
          self.form.camposAtributos.numeroFactura.valorBlur = null;

        });

        self.$refs["modal-mas-info"].$on('shown', () => {

          let monto = self.$refs["montoFacturaMod"].$el

          self.modalMasInfo.form.camposAtributos.montoFacturaMod.autonumeric = new AutoNumeric(monto, {
            decimalPlaces: 2,
            decimalCharacter: ',',
            digitGroupSeparator: '.',
            emptyInputBehavior: 0,
            maximumValue: '99999999999999999999.99',
            minimumValue: 0,
            modifyValueOnWheel: false
          });

        });

        self.$refs["modal-mas-info"].$on('hidden', () => {

          Object.keys(self.modalMasInfo.form.camposAtributos).forEach((indice, i) => {

            if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("state")){
              self.modalMasInfo.form.camposAtributos[indice].state = null;
            }

            if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("disabled")){
              self.modalMasInfo.form.camposAtributos[indice].disabled = true;
            }

          });

          self.modalMasInfo.botones.confirmar.show = true;
          self.modalMasInfo.botones.submit.show = false;
          self.modalMasInfo.botones.cancelar.show = false;

          self.mostrarAlert(self.modalMasInfo.alert);

        });

      }

    }, 1000);

  },
  methods:{
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
    limpiarMensajeError: function(elemento){

      elemento.invalidFeedback = "";
      elemento.state = null;

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
          monto_factura_formatted: self.simboloMoneda+item.monto_factura_formatted,
          fecha_factura: item.fecha_factura,
          fecha_factura_formatted: item.fecha_factura_formatted,
          fecha_cobro_factura: item.fecha_cobro_factura,
          fecha_cobro_factura_formatted: item.fecha_cobro_factura_formatted,
          numero_control: item.numero_control,
          movimiento: item.movimiento,
          varianteMovimiento: varianteMovimiento,
          id: item.id,
          observaciones: item.observaciones,
          id_concepto_factura: item.id_concepto_factura
        };

        registros.push(factura);

      });

      return registros;

    },
    paginaAnterior: function(){
      self.paginador.pagina = ((self.paginador.pagina - 1) === 0) ? 1 : (self.paginador.pagina - 1);
      self.buscarFacturasCargadas();
    },
    paginaSiguiente: function(){
      self.paginador.pagina = ((self.paginador.pagina + 1) > self.paginador.max) ? self.paginador.pagina : (self.paginador.pagina + 1);
      self.buscarFacturasCargadas();
    },
    numeroPagina: function(e){
      self.buscarFacturasCargadas();
    },
    buscarFacturasCargadas: function(){

      //Obtenemos los valores
      let desde = (self.paginador.pagina - 1) * self.paginador.paginar;
      let parametros = {
        desde: desde,
        id_proyecto: proyecto_id,
        paginar: self.paginador.paginar
      };

      axios.get('/buscarFacturasCargadas', {params: parametros})
      .then(function (response) {

        // Se le asigna los valores a las variables
        self.paginador.numPaginas = response.data.paginas;
        self.paginador.max = parseInt(response.data.paginas);

        self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

      }).catch(error => {


      });

    },
    tipoConcepto: function(valor){

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "tipoConcepto" && indice !== "observaciones"){
          self.form.camposAtributos[indice].disabled = true;
        }

        if(self.form.camposAtributos[indice].hasOwnProperty("state")){
          self.form.camposAtributos[indice].state = null;
        }

        if(self.form.camposAtributos[indice].hasOwnProperty("invalidFeedback")){
          self.form.camposAtributos[indice].invalidFeedback = "";
        }

      });

      self.form.camposAtributos.numeroFactura.help = self.form.camposAtributos.numeroFactura.helpInit;
      self.form.camposAtributos.numeroFactura.busqueda = false;
      self.form.camposAtributos.numeroFactura.idFacturaAnular = null;

      if(valor !== null && valor.trim !== '' && valor.hasOwnProperty("type")){

        const type = parseInt(valor.type);
        const id = parseInt(valor.id);

        if(type === 3){

          self.form.camposAtributos.numeroFactura.disabled = false;
          self.form.camposAtributos.observaciones.disabled = false;
          self.form.camposAtributos.numeroFactura.busqueda = true;

        }else if(type === 2 && id === 5){

          self.form.camposAtributos.montoFactura.disabled = false;

        }else{

          self.form.camposAtributos.concepto.disabled = false;
          self.form.camposAtributos.numeroFactura.disabled = false;
          self.form.camposAtributos.montoFactura.disabled = false;
          self.form.camposAtributos.fechaFactura.disabled = false;
          self.form.camposAtributos.fechaCobroFactura.disabled = false;
          self.form.camposAtributos.numeroControl.disabled = false;
          self.form.camposAtributos.observaciones.disabled = false;

        }

        self.form.campos.concepto = null;
        self.form.campos.numeroFactura = null;
        self.form.campos.fechaFactura = null;
        self.form.camposAtributos.fechaCobroFactura.value = "";
        self.form.campos.numeroControl = null;
        self.form.camposAtributos.observaciones.value = "";
        self.form.campos.montoFactura = null;
        self.form.camposAtributos.montoFactura.autonumeric.set(0);

      }

      self.limpiarMensajeError(self.form.camposAtributos.tipoConcepto);

    },
    confirmaRegistrarFactura: async function(){

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

        self.form.botones.confirmar.show = false;
        self.form.botones.submit.show = true;
        self.form.botones.cancelar.show = true;

        self.mostrarAlert(self.form.alert, true, "warning", "¿Estas seguro de registrar esta factura/gasto?", false, false, 0);

      }

    },
    cancelarRegistrarFactura: function(){

      self.form.botones.confirmar.show = true;
      self.form.botones.submit.show = false;
      self.form.botones.cancelar.show = false;

      self.mostrarAlert(self.form.alert);

    },
    registrar: async function(id){

      self.mostrarAlert(self.form.alert);

      //Obtenemos valores
      let parametros = {
        concepto: self.form.campos.concepto,
        tipo_concepto: self.form.campos.tipoConcepto.id,
        numero_factura: self.form.campos.numeroFactura,
        monto_factura: self.form.camposAtributos.montoFactura.autonumeric.get(),
        fecha_factura: self.form.campos.fechaFactura,
        fecha_cobro_factura: self.form.camposAtributos.fechaCobroFactura.value,
        numero_control: self.form.campos.numeroControl,
        observaciones: self.form.camposAtributos.observaciones.value,
        id_proyecto: proyecto_id,
        id_factura_anular: self.form.camposAtributos.numeroFactura.idFacturaAnular,
        paginar: self.paginador.paginar
      }

      self.form.botones.cancelar.disabled = true;
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
          self.form.campos.montoFactura = null;
          self.form.camposAtributos.montoFactura.autonumeric.set(0);

          self.form.info.monto_facturado = self.simboloMoneda+response.data.facturado_proyecto.monto_facturado;
          self.form.info.monto_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_gasto;
          self.form.info.monto_notas_credito = self.simboloMoneda+response.data.facturado_proyecto.monto_notas_credito;
          self.form.info.monto_otros_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_otros_gastos;

          Object.keys(self.form.camposAtributos).forEach((indice, i) => {

            if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "tipoConcepto" && indice !== "observaciones"){
              self.form.camposAtributos[indice].disabled = true;
            }else if(indice === "tipoConcepto" || indice === "observaciones"){
              self.form.camposAtributos[indice].disabled = false;
            }

            if(self.form.camposAtributos[indice].hasOwnProperty("state")){
              self.form.camposAtributos[indice].state = null;
            }

            if(self.form.camposAtributos[indice].hasOwnProperty("invalidFeedback")){
              self.form.camposAtributos[indice].invalidFeedback = "";
            }

          });

          self.form.camposAtributos.numeroFactura.busqueda = false;
          self.form.camposAtributos.numeroFactura.valor = null;
          self.form.camposAtributos.numeroFactura.valorFocus = null;
          self.form.camposAtributos.numeroFactura.valorBlur = null;
          self.form.camposAtributos.numeroFactura.idFacturaAnular = null;

          self.$nextTick(() => {
            self.$v.$reset();
          });

          self.form.botones.submit.disabled = false;
          self.form.botones.submit.html = self.form.botones.submit.htmlInit;
          self.form.botones.cancelar.disabled = false;

          self.mostrarAlert(self.form.alert, true, "success", response.data.message, true, true, 10);

          self.form.botones.confirmar.show = true;
          self.form.botones.submit.show = false;
          self.form.botones.cancelar.show = false;

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
        self.form.botones.submit.html = self.form.botones.submit.htmlInit;
        self.form.botones.cancelar.disabled = false;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlert(self.form.alert, true, variante, mensaje, true, true, 10);

      });

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

      self.limpiarMensajeError(self.form.camposAtributos.numeroFactura);
      self.$refs["ref-lista-facturas"].hide();
      self.form.camposAtributos.numeroFactura.listaDropdown.listado = [];
      self.form.camposAtributos.numeroFactura.listaDropdown.noResultado = false;
      self.form.campos.numeroFactura = null;
      self.form.camposAtributos.numeroFactura.valorFocus = null;
      self.form.camposAtributos.numeroFactura.valorBlur = null;
      self.form.camposAtributos.numeroFactura.idFacturaAnular = null;

      if(self.form.camposAtributos.numeroFactura.valor !== ''){

        axios.get('/buscarFacturaProyectoNotaCredito',{
          params: {
            id_proyecto: proyecto_id,
            numero_factura: self.form.camposAtributos.numeroFactura.valor
          }
        })
        .then(function (response) {

          self.form.camposAtributos.numeroFactura.help = self.form.camposAtributos.numeroFactura.helpInit;

          if(response.status === 200 && response.data.response === true){

            self.form.camposAtributos.numeroFactura.listaDropdown.listado = response.data.facturas;

            if(response.data.facturas.length === 0){
              self.form.camposAtributos.numeroFactura.listaDropdown.noResultado = true;
            }

            self.mostrarListado("ref-lista-facturas");

          }else{

            throw "error";

          }

        })
        .catch(error => {

          self.form.camposAtributos.numeroFactura.help = self.form.camposAtributos.numeroFactura.helpInit;
          self.form.camposAtributos.numeroFactura.invalidFeedback = "Ocurrio un error, intenta nuevamente; con este error no podrás la factura.";
          self.form.camposAtributos.numeroFactura.state = false;

        });

      }// Fin if

    },
    mostrarListado: function(indice){

      self.$refs[indice].visibleChangePrevented = true;
      self.$refs[indice].show();

    },
    elegirFactura: function(factura){

      self.form.camposAtributos.numeroFactura.valor = factura.numero_factura;
      self.form.camposAtributos.numeroFactura.valorFocus = factura.numero_factura;
      self.form.camposAtributos.numeroFactura.valorBlur = factura.numero_factura;
      self.form.camposAtributos.numeroFactura.state = true;
      self.form.camposAtributos.numeroFactura.idFacturaAnular = factura.id;
      self.form.campos.numeroFactura = factura.numero_factura;

      self.form.campos.concepto = factura.concepto;
      self.form.campos.fechaFactura = factura.fecha_factura;
      self.form.campos.numeroControl = factura.numero_control;
      self.form.camposAtributos.observaciones.value = factura.observaciones;
      self.form.camposAtributos.fechaCobroFactura.value = factura.fecha_cobro_factura;
      self.form.campos.montoFactura = factura.monto_factura;

      let monto = self.$refs["montoFactura"].$el
      AutoNumeric.getAutoNumericElement(monto).set(factura.monto_factura);

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
    eliminar_factura: function(id_factura){

      self.$refs["modal-eliminar-factura-"+id_factura].$on('hidden', () => {

        self.modalEliminar.botones.cancelar.show = true;
        self.modalEliminar.botones.submit.show = true;
        self.modalEliminar.botones.hide.show = false;

        self.modalEliminar.botones.cancelar.disabled = false;
        self.modalEliminar.botones.submit.disabled = false;

        self.modalEliminar.botones.submit.html = self.modalEliminar.botones.submit.htmlInit;

        self.mostrarAlert(self.modalEliminar.alert);

      });

      self.mostrarAlert(self.modalEliminar.alert);

      self.modalEliminar.botones.cancelar.disabled = true;
      self.modalEliminar.botones.cancelar.show = false;
      self.modalEliminar.botones.submit.disabled = true;
      self.modalEliminar.botones.submit.html = self.modalEliminar.botones.submit.htmlLoading;

      let parametros = {
        id_factura: id_factura,
        id_proyecto: proyecto_id,
        paginar: self.paginador.paginar
      }

      axios.post('/eliminarFactura', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.tabla.registros = [];
          self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

          self.form.info.monto_facturado = self.simboloMoneda+response.data.facturado_proyecto.monto_facturado;
          self.form.info.monto_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_gasto;
          self.form.info.monto_notas_credito = self.simboloMoneda+response.data.facturado_proyecto.monto_notas_credito;
          self.form.info.monto_otros_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_otros_gastos;

          self.modalEliminar.botones.submit.show = false;
          self.modalEliminar.botones.hide.show = true;

          self.mostrarAlert(self.modalEliminar.alert, true, "success", response.data.message, false, true, 3);

          setTimeout(function(){

            self.$refs["modal-eliminar-factura-"+id_factura].hide();

          }, 3000);

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.modalEliminar.botones.submit.disabled = false;
        self.modalEliminar.botones.submit.html = self.form.botones.submit.htmlInit;
        self.modalEliminar.botones.cancelar.disabled = false;
        self.modalEliminar.botones.cancelar.show = true;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlert(self.modalEliminar.alert, true, variante, mensaje, true, true, 10);

      });

    },
    verMasInfo: function(data){

      self.modalMasInfo.form.campos.montoFacturaMod = data.monto_factura;
      self.modalMasInfo.form.campos.fechaFacturaMod = data.fecha_factura;
      self.modalMasInfo.form.campos.conceptoMod = data.concepto;
      self.modalMasInfo.form.campos.numeroControlMod = data.numero_control;
      self.modalMasInfo.form.camposAtributos.observacionesMod.value = data.observaciones;
      self.modalMasInfo.form.camposAtributos.fechaCobroFacturaMod.value = data.fecha_cobro_factura;
      self.modalMasInfo.idConceptoFactura = data.id_concepto_factura;
      self.modalMasInfo.idFactura = data.id;

      self.modalMasInfo.titulo = (data.numero_factura === null) ? "("+data.tipo_concepto+")" : data.numero_factura+" ("+data.tipo_concepto+")";

      var indicesDisabled = [];

      if([1,2,3].includes(data.id_concepto_factura)){

        indicesDisabled = ["montoFacturaMod","conceptoMod","montoFacturaMod","fechaFacturaMod","fechaCobroFacturaMod","numeroControlMod","observacionesMod"];

      }else if(data.id_concepto_factura === 4){

        indicesDisabled = ["observacionesMod"];

      }else if(data.id_concepto_factura === 5){

        indicesDisabled = ["montoFacturaMod","observacionesMod"];

      }

      indicesDisabled.forEach((indice) => {

        if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("disabled")){
          self.modalMasInfo.form.camposAtributos[indice].disabled = false;
        }

      });

      self.$refs["modal-mas-info"].show();

    },
    confirmarModificarFactura: async function(){

      var formValido = true;

      await self.mostrarAlert(self.modalMasInfo.alert);

      Object.keys(self.modalMasInfo.form.camposAtributos).forEach((indice, i) => {

        if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("state")){
          self.modalMasInfo.form.camposAtributos[indice].state = (self.modalMasInfo.form.camposAtributos[indice].state === true) ? true : null;
        }

        if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("invalidFeedback")){
          self.modalMasInfo.form.camposAtributos[indice].invalidFeedback = "";
        }

      });

      var formValido = true;

      const arrayCampos = Object.keys(self.modalMasInfo.form.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.modalMasInfo.form.campos[indice];
        campo.$touch();

        if(campo.$invalid){

          self.modalMasInfo.form.camposAtributos[indice].state = false;
          const valorCampo = self.$v.modalMasInfo.form.campos[indice].$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.modalMasInfo.form.camposAtributos[indice].invalidFeedback = mensajeError.mensaje;

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

        self.modalMasInfo.botones.confirmar.show = false;
        self.modalMasInfo.botones.submit.show = true;
        self.modalMasInfo.botones.cancelar.show = true;

        self.mostrarAlert(self.modalMasInfo.alert, true, "warning", "¿Estas seguro de modificar esta factura/gasto?", false, false, 0);

      }

    },
    cancelarModificarFactura: function(){

      self.modalMasInfo.botones.confirmar.show = true;
      self.modalMasInfo.botones.submit.show = false;
      self.modalMasInfo.botones.cancelar.show = false;

      self.mostrarAlert(self.modalMasInfo.alert);

    },
    modificar: function(){

      self.mostrarAlert(self.modalMasInfo.alert);

      //Obtenemos valores
      let parametros = {
        concepto: self.modalMasInfo.form.campos.conceptoMod,
        monto_factura: self.modalMasInfo.form.camposAtributos.montoFacturaMod.autonumeric.get(),
        fecha_factura: self.modalMasInfo.form.campos.fechaFacturaMod,
        fecha_cobro_factura: self.modalMasInfo.form.camposAtributos.fechaCobroFacturaMod.value,
        numero_control: self.modalMasInfo.form.campos.numeroControlMod,
        observaciones: self.modalMasInfo.form.camposAtributos.observacionesMod.value,
        id_proyecto: proyecto_id,
        id_factura: self.modalMasInfo.idFactura,
        paginar: self.paginador.paginar
      }

      self.modalMasInfo.botones.cancelar.disabled = true;
      self.modalMasInfo.botones.submit.disabled = true;
      self.modalMasInfo.botones.submit.html = self.modalMasInfo.botones.submit.htmlLoading;

      Object.keys(self.modalMasInfo.form.camposAtributos).forEach((indice, i) => {

        if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("disabled")){
          self.modalMasInfo.form.camposAtributos[indice].disabled = true;
        }

      });

      axios.post('/modificarFactura', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.tabla.registros = [];
          self.tabla.registros = self.registroTabla(response.data.facturas_cargadas);

          self.form.info.monto_facturado = self.simboloMoneda+response.data.facturado_proyecto.monto_facturado;
          self.form.info.monto_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_gasto;
          self.form.info.monto_notas_credito = self.simboloMoneda+response.data.facturado_proyecto.monto_notas_credito;
          self.form.info.monto_otros_gastos = self.simboloMoneda+response.data.facturado_proyecto.monto_otros_gastos;

          var indicesDisabled = [];

          if([1,2,3].includes(self.modalMasInfo.idConceptoFactura)){

            indicesDisabled = ["montoFacturaMod","conceptoMod","montoFacturaMod","fechaFacturaMod","fechaCobroFacturaMod","numeroControlMod","observacionesMod"];

          }else if(self.modalMasInfo.idConceptoFactura === 4){

            indicesDisabled = ["observacionesMod"];

          }else if(self.modalMasInfo.idConceptoFactura === 5){

            indicesDisabled = ["montoFacturaMod","observacionesMod"];

          }

          indicesDisabled.forEach((indice) => {

            if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("disabled")){
              self.modalMasInfo.form.camposAtributos[indice].disabled = false;
            }

          });

          self.modalMasInfo.botones.submit.disabled = false;
          self.modalMasInfo.botones.submit.html = self.form.botones.submit.htmlInit;
          self.modalMasInfo.botones.cancelar.disabled = false;

          self.mostrarAlert(self.modalMasInfo.alert, true, "success", response.data.message, true, true, 10);

          self.modalMasInfo.botones.confirmar.show = true;
          self.modalMasInfo.botones.submit.show = false;
          self.modalMasInfo.botones.cancelar.show = false;

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        var indicesDisabled = [];

        if([1,2,3].includes(self.modalMasInfo.idConceptoFactura)){

          indicesDisabled = ["montoFacturaMod","conceptoMod","montoFacturaMod","fechaFacturaMod","fechaCobroFacturaMod","numeroControlMod","observacionesMod"];

        }else if(self.modalMasInfo.idConceptoFactura === 4){

          indicesDisabled = ["observacionesMod"];

        }else if(self.modalMasInfo.idConceptoFactura === 5){

          indicesDisabled = ["montoFacturaMod","observacionesMod"];

        }

        indicesDisabled.forEach((indice) => {

          if(self.modalMasInfo.form.camposAtributos[indice].hasOwnProperty("disabled")){
            self.modalMasInfo.form.camposAtributos[indice].disabled = false;
          }

        });

        self.modalMasInfo.botones.submit.disabled = false;
        self.modalMasInfo.botones.submit.html = self.form.botones.submit.htmlInit;
        self.modalMasInfo.botones.cancelar.disabled = false;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlert(self.modalMasInfo.alert, true, variante, mensaje, true, true, 10);

      });

    }
  }

});
