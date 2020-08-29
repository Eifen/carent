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
import { required } from 'vuelidate/lib/validators';
import zenscroll from 'zenscroll';

Vue.component('loading', require('../components/loading.vue').default);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.component('multiselect', Multiselect);
Vue.component('alert',require('../components/alert.vue').default);
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
    comboConceptos: [],
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
        numeroFactura: null,
        fechaFactura: null,
        numeroControl: null,
        observaciones: null
      },
      camposAtributos: {
        concepto: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        numeroFactura: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        fechaFactura: {
          disabled: true,
          invalidFeedback: "",
          max: null,
          state: null
        },
        numeroControl: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        observaciones: {
          disabled: true,
          invalidFeedback: "",
          state: null
        }
      },
      info: {
        estatus : "",
        fecha_contratacion: "",
        gerente: "",
        monto_contratado: "",
        monto_facturado: "",
        monto_gastos: "",
        proyecto: "",
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
          required
        },
        numeroFactura: {
          required
        },
        fechaFactura: {
          required
        },
        numeroControl: {
          required
        },
        observaciones: {
          required
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

        self.form.info = {
          estatus: response.data.proyecto.estatus,
          fecha_contratacion: response.data.proyecto.fecha_contratacion,
          gerente: response.data.proyecto.gerente,
          monto_contratado: response.data.proyecto.simbolo_moneda+response.data.proyecto.monto_contratado,
          monto_facturado: response.data.proyecto.simbolo_moneda+0,
          monto_gastos: response.data.proyecto.simbolo_moneda+0,
          proyecto: response.data.proyecto.proyecto,
          socio: response.data.proyecto.socio
        }

        response.data.conceptos_factura.forEach((item, i) => {
          self.comboConceptos.push({text:item.descripcion, value: item.id});
        });

        self.form.camposAtributos.concepto.disabled = false;
        self.form.camposAtributos.numeroFactura.disabled = false;
        self.form.camposAtributos.fechaFactura.disabled = false;
        self.form.camposAtributos.numeroControl.disabled = false;
        self.form.camposAtributos.observaciones.disabled = false;

        self.form.botones.submit.html = self.form.botones.submit.htmlInit;
        self.form.botones.submit.disabled = false;

        /*if(response.data.permisos.permiso_actualizar){
          self.tabla.encabezado = [
            { key: 'numero', label: '#' },
            { key: 'proyecto', label: 'Proyecto' },
            { key: 'fecha_contratacion', label: 'Fecha Contrato' },
            { key: 'monto_contratado', label: 'Monto Contratado' },
            'estatus',
            { key: 'opciones', label: ' ' },
            { key: 'editar', label: ' ' }
          ];
        }else{
          self.tabla.encabezado = [
           { key: 'numero', label: '#' },
           { key: 'proyecto', label: 'Proyecto' },
           { key: 'division', label: 'División' },
           'estatus',
           { key: 'opciones', label: ' ' }
          ];
        }

        var mostrar = false;
        var mensaje = "";
        var variante = "";

        if(response.data.proyectos.length === 0){
          mostrar = true;
          mensaje = "No hay proyectos por facturar";
          variante = "warning";
        }

        self.mostrarAlert(self.tabla.alert, mostrar, variante, mensaje, false, false, 0);

        self.tabla.registros = self.registroTabla(response.data.proyectos);

        //Le asignamos los valores a las variables
        self.comboDivisiones = response.data.divisiones;
        self.formFiltro.proyecto.disabled = false;
        self.formFiltro.cliente.disabled = false;
        self.formFiltro.estatus.disabled = false;
        self.formFiltro.divisiones.disabled = false;
        self.formFiltro.mostrar = true;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;

        self.permisos = response.data.permisos;

        response.data.estatus.forEach((item, i) => {
          self.comboEstatus.push({text : item.descripcion, value : item.valor});
        });



        self.paginador.numPaginas = response.data.numero_paginas;
        self.paginador.max = parseInt(response.data.numero_paginas);
        self.paginador.paginar = response.data.paginar;*/

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
  mounted: function () {},
  updated: function () {},
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
    limpiarMensajeError: function(refName){

      self.form.camposAtributos[refName].invalidFeedback = "";
      self.form.camposAtributos[refName].state = null;

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
          numero_factura: self.form.campos.numeroFactura,
          fecha_factura: self.form.campos.fechaFactura,
          numero_control: self.form.campos.numeroControl,
          observaciones: self.form.campos.observaciones
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

            self.form.campos.concepto = null;
            self.form.campos.numeroFactura = null;
            self.form.campos.fechaFactura = null;
            self.form.campos.numeroControl = null;
            self.form.campos.observaciones = null;

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
      }else{
        mensaje = "";
      }

      return {mensaje:mensaje, respuesta:respuesta};

    },









    registroTabla: function(datos){

      const registros = [];
      datos.forEach((item, i) => {

        var variante;

        switch (item.id_estatus) {
          case 1: variante = "success"; break;
          case 2: variante = "danger"; break;
          case 3: variante = "warning"; break;
          case 4: variante = "warning"; break;
          default: variante = "light";
        }

        const proyecto = {
          numero: (i + 1),
          proyecto: item.proyecto,
          fecha_contratacion: item.fecha_contratacion,
          monto_contratado: item.simbolo_moneda+''+item.monto_contratado,
          estatus: item.estatus,
          id: item.id,
          id_estatus: item.id_estatus,
          variante: variante
        };

        registros.push(proyecto);

      });

      return registros;

    },
    keyboard: function(e){

      if (e.keyCode === 13){
        e.preventDefault();
      }

    }
  }

});
