require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import zenscroll from 'zenscroll';
import axios from 'axios';
import Vuelidate from 'vuelidate';
import AutoNumeric from 'autonumeric';
import VueNumeric from 'vue-numeric';
import { required, requiredIf, maxLength, minLength } from 'vuelidate/lib/validators';
import vSelect from "vue-select";
import "vue-select/dist/vue-select.css";
import VueTheMask from 'vue-the-mask';
var self;

Vue.component('loading',require('../components/loading.vue').default);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('alert',require('../components/alert.vue').default);
Vue.component('confirm',require('../components/confirm.vue').default);
Vue.component("v-select", vSelect);
Vue.use(VueTheMask);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(VueNumeric);
Vue.use(Vuelidate);


new Vue({

  el: '#nuevoCliente',
  data: {
    hexTokens: {
      F: {
        pattern: /[cegjpvCEGJPV]/,
        transform: v => v.toLocaleUpperCase()
      },
      M:{
        pattern: /[0-9]/,
        transform: v => v.toLocaleUpperCase()
      }
    },  
    alert:{
      message: "",
      mostrar: false
    },
    alertGeneral: {
      contador: false,
      iconCerrar: false,
      mensaje: "",
      mostrar: false,
      ocultarSeg: 0,
      variante: ""
    },
    formFiltro: {
      btn: {
        filtrar: {
          disabled: false,
          html: "",
          htmlInit: "Buscar Socio",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        },
        limpiarFiltro: {
          disabled: false,
          html: "",
          htmlInit: "Limpiar Selección",
          htmlLoading: "<i class='fas fa-cog fa-spin'></i>"
        }
      },
      nombre: {
        disabled: true,
        value: ""
      },
      id_usuario:{
        value: ""
      },
      mostrar: false,
      modal: false,
    },
    form: {
      campos:{
        codigoCliente: null,
        codigoUsuario: null,
        nombre: null,
        rif: null,
        razon_social: null,
        direccion: null,
        pais: null,
        telefono_fiscal: null,
        pagina_web: null,
        email_fiscal: null,
      },
      camposAtributos:{
        codigoCliente:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        codigoUsuario:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        nombre:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        rif:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        razon_social:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        direccion:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        pais: {
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        telefono_fiscal:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        pagina_web:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
        email_fiscal:{
          disabled: true,
          invalidFeedback: "",
          state: null
        },
      },
      mostrar: false,
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
          html: "Crear Cliente",
          show: true
        },
        submit: {
          disabled: false,
          html: "",
          htmlInit: "Si, estoy seguro de crear este cliente",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        },
        refresh: {
          html: "Quiero crear un nuevo Cliente!",
          show: false
        },
      },
    },
    comboPaises: [],
    paises: [], 
    loading: true,
    modalDetalleUsuario: {
      alert: {
        contador: false,
        iconCerrar: false,
        mensaje: "",
        mostrar: false,
        ocultarSeg: 0,
        variante: ""
      },
      footer: {
        hide: true
      },
      agregarSocio: {
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
        registros: [],
        total: 0
      },
    },
  },
  validations: {
    form:{
      campos:{
        codigoCliente: {
          required
        },
        codigoUsuario: {
          required
        },
        nombre: {
          required
        },
        rif: {
          required
        },
        razon_social: {
          required
        },
        direccion: {
          required
        },
        pais: {
          required
        },
        telefono_fiscal: {
          required
        },
        pagina_web: {
        },
        email_fiscal: {
          required
        },
      },
    }
  },
  beforeCreate: function(){

    self = this;

    axios.get('/dataInicialCliente')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        response.data.paises.forEach((item, i) => {
          self.comboPaises.push({text:item.nombre, value: item.id, codigo_telf: item.codigo_telf});
        });
        self.paises = response.data.paises

        self.form.campos.codigoCliente = response.data.codigo;
        self.formFiltro.nombre.disabled = false;
        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.form.botones.submit.html = self.form.botones.submit.htmlInit;

        self.form.camposAtributos.rif.disabled = false;
        self.form.camposAtributos.razon_social.disabled = false;
        self.form.camposAtributos.pais.disabled = false;
        self.form.camposAtributos.direccion.disabled = false;
        self.form.camposAtributos.pagina_web.disabled = false;
        self.form.camposAtributos.email_fiscal.disabled = false;


        self.formFiltro.mostrar = true;
        self.form.mostrar = true;
        self.loading = false;

        self.modalDetalleUsuario.agregarSocio.encabezado = [
          { key: 'numero', label: '#' },
          { key: 'codigo', label: 'Codigo' },
          { key: 'nombre', label: 'Nombre' },
          { key: 'opciones', label: ' ' }
        ];

      }else{

        self.mostrarAlertForm(self.alertGeneral, true, "warning", "Existe un error!, consulte con el administrador del sistema.", false, false, 0);
        throw "error";

      }

    })
    .catch(error => {

      Object.keys(self.form).forEach(function(indiceObjecto, indice) {

        self.form[indiceObjecto].disabled = true;

      });

      self.alert.mostrar = true;
      self.alert.message = (error.data.message) ? error.data.message : "Ocurrió un error!, por favor intente recargando la página.";
      self.loading = false;

    });

    

  },
  created: function () {},
  mounted: function () {
    self.$refs["modal-detalle-usuario"].$on('shown', () => {
      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.formFiltro.nombre.disabled = true;
      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.disabled = true;
      self.formFiltro.modal = false;
      self.modalDetalleUsuario.agregarSocio.registros = [];
      self.modalDetalleUsuario.agregarSocio.cargando = true;
      self.buscar();
    });
    self.$refs["modal-detalle-usuario"].$on('hidden', () => {
      self.modalDetalleUsuario.footer.hide = true;
      self.modalDetalleUsuario.agregarSocio.registros = [];
      self.modalDetalleUsuario.agregarSocio.cargando = true;
      self.mostrarAlertForm(self.modalDetalleUsuario.alert);
    });
  },

  updated: function () {},
  methods:{

    buscar: function(e){

      self.mostrarAlertForm(self.modalDetalleUsuario.agregarSocio.alert);
      // Obtenemos lo valores
      let parametros = {
        nombre: self.formFiltro.nombre.value
      };


      axios.get('/buscarUsuariosS', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
          self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
          self.formFiltro.nombre.disabled = false;
          self.formFiltro.btn.filtrar.disabled = false;
          self.formFiltro.btn.limpiarFiltro.disabled = false;
          self.formFiltro.modal = true;

          if(response.data.usuarios.length === 0){

            let mensaje = "No se encontraron coincidencias";
            self.mostrarAlertForm(self.modalDetalleUsuario.agregarSocio.alert, true, "warning", mensaje, false, false, 0);

          }
          let data = self.registroTablaSocios(response.data.usuarios);
          self.modalDetalleUsuario.agregarSocio.registros = data.registros;

          self.$refs['modal-detalle-usuario'].show();
          self.modalDetalleUsuario.agregarSocio.cargando = false;

        }else{

          let mensaje = "Ocurrió un error!";
          self.mostrarAlertForm(self.modalDetalleUsuario.agregarSocio.alert, true, "warning", mensaje, false, false, 0);
          self.modalDetalleUsuario.agregarSocio.cargando = false;
          throw response.data;

        }

      })
      .catch(error => {

        self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
        self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;
        self.formFiltro.nombre.disabled = false;
        self.formFiltro.btn.filtrar.disabled = false;
        self.formFiltro.btn.limpiarFiltro.disabled = false;
        self.formFiltro.modal = false;
        let mensaje = "Ocurrió un error!!";
        self.mostrarAlertForm(self.modalDetalleUsuario.agregarSocio.alert, true, "warning", mensaje, false, false, 0);
        self.modalDetalleUsuario.agregarSocio.cargando = false;

      });

    },

    SelecionarUsuario: function(idUsuario,e){

      self.$refs['modal-detalle-usuario'].hide();
      for (var i = self.modalDetalleUsuario.agregarSocio.registros.length - 1; i >= 0; i--) {
        if(self.modalDetalleUsuario.agregarSocio.registros[i].id === idUsuario){
          self.formFiltro.id_usuario.value = self.modalDetalleUsuario.agregarSocio.registros[i].id ;
          self.form.campos.nombre = self.modalDetalleUsuario.agregarSocio.registros[i].nombre;
          self.form.campos.codigoUsuario = self.modalDetalleUsuario.agregarSocio.registros[i].codigo;
        }
      }

    },


    registroTablaSocios: function(datos){

      const registros = [];
      datos.forEach((item, i) => {

        const usuarios = {
          numero: (i + 1),
          codigo: item.codigo,
          nombre: item.nombre,
          id: item.id
        };

        registros.push(usuarios);

      });

      return {
        registros: registros
      };

    },

    pais: function(){

      self.form.camposAtributos.telefono_fiscal.disabled = false;
      self.form.campos.telefono_fiscal = null;
      for (var i = 0; i < self.paises.length; i++) {
        if(self.paises[i].id === self.form.campos.pais){
          self.form.campos.telefono_fiscal = self.paises[i].codigo_telf;
        }
      }
    },

    confirmarCrearCliente: function(){

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

        self.form.botones.confirmar.show = false;
        self.form.botones.submit.show = true;
        self.form.botones.cancelar.show = true;

        self.mostrarAlertForm(self.form.alert, true, "warning", "¿Estas seguro de crear este cliente?", false, false, 0);

      }

    },

    cancelarCrearCliente: function(){

      self.form.botones.confirmar.show = true;
      self.form.botones.submit.show = false;
      self.form.botones.cancelar.show = false;

      self.mostrarAlertForm(self.form.alert);

    },

    crear: async function(){

      self.mostrarAlertForm(self.form.alert);

      //Obtenemos valores
      let parametros = {
        idUsuario: self.formFiltro.id_usuario.value,
        codigoCliente: self.form.campos.codigoCliente,
        rif: self.form.campos.rif,
        razon_social:  self.form.campos.razon_social,
        pais: self.form.campos.pais,
        direccion: self.form.campos.direccion,
        telefono_fiscal: self.form.campos.telefono_fiscal,
        pagina_web: self.form.campos.pagina_web,
        email_fiscal: self.form.campos.email_fiscal
      };

      self.form.botones.cancelar.disabled = true;
      self.form.botones.submit.disabled = true;
      self.form.botones.submit.html = self.form.botones.submit.htmlLoading;

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {
        self.form.camposAtributos[indice].disabled = true;
      });

      axios.post('/crearCliente', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.form.botones.cancelar.show = false;
          self.form.botones.submit.show = false;
          self.form.botones.refresh.show = true;

          self.mostrarAlertForm(self.form.alert, true, "success", response.data.message, true, true, 10);


        }else{

          throw response.data;

        }

      })
      .catch(error => {

        Object.keys(self.form.camposAtributos).forEach((indice, i) => {
          self.form.camposAtributos[indice].disabled = false;
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

    cleanFieldForm: function(field){

      field.state = null;
      field.disabled = false;

    },

    limpiarFiltro: function(){

      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlLoading;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlLoading;
      self.form.campos.nombre = null;
      self.form.campos.codigoUsuario = null;
      self.formFiltro.id_usuario.value = "";
        

      self.formFiltro.btn.filtrar.disabled = true;
      self.formFiltro.btn.limpiarFiltro.disabled = true;
      self.formFiltro.nombre.disabled = true;

      
      self.formFiltro.btn.filtrar.html = self.formFiltro.btn.filtrar.htmlInit;
      self.formFiltro.btn.limpiarFiltro.html = self.formFiltro.btn.limpiarFiltro.htmlInit;

      self.formFiltro.btn.filtrar.disabled = false;
      self.formFiltro.btn.limpiarFiltro.disabled = false;
      self.formFiltro.nombre.disabled = false;
    },

    refreshView: function(){
      window.location.href = "/formNuevoCliente";
    },

    keyboard: function(e){

      if (e.keyCode === 13){
        e.preventDefault();
      }

    },
  }// Fin methods

});
