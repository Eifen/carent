require('bootstrap');
window.Vue = require('vue');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
import VueTheMask from 'vue-the-mask';
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

Vue.use(VueTheMask);
Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);

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

}

const datosIniciales = () => {

  return new Promise((resolve, reject) => {

    axios.get('/detalleClienteModificar')
    .then(function (response) {

      if(response.status === 200 && response.data.response === true){

        resolve({                 
                 infoClie: response.data.info,
                 detalleUsuario: response.data.info,
                 detalleUsuarioG: response.data.info,
                 estadosfi: response.data.estadosfi,
                 municipiosfi: response.data.municipiosfi,
                 parroquiasfi: response.data.parroquiasfi,
                 estadosfa: response.data.estadosfa,
                 municipiosfa: response.data.municipiosfa,
                 parroquiasfa: response.data.parroquiasfa,
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

  el: '#modificarCliente',
  data: {
    idCliente: null,
    alertForm: {
      class: "",
      message: "",
      show: false
    },
     comboEstadosfi: [],
    comboMunicipiosfi: [],
    comboParroquiasfi: [],
    comboEstadosfa: [],
    comboMunicipiosfa: [],
    comboParroquiasfa: [],

    refreshForm: false,
    form: {
      codigoCliente:{
        disabled: false,
        value: ""
      },
      rif:{
        disabled: false,
        value: ""
      },
      nit:{
        disabled: false,
        value: ""
      },
      razon_social:{
        disabled: false,
        value: ""
      },
      estadofi: {
        disabled: true,
        validar: false,
        value: ""
      },
      municipiofi: {
        disabled: true,
        help: "Municipio de la oficina fiscal",
        validar: false,
        value: ""
      },
      parroquiafi: {
        disabled: true,
        help: "Parroquia de la oficina fiscal",
        validar: false,
        value: ""
      },
      ciudad_fiscal: {
        disabled: false,
        value: ""
      },
      avenida_calle_fiscal: {
        disabled: false,
        value: ""
      },
      edificio_quinta_fiscal: {
        disabled: false,
        value: ""
      },      
      piso_fiscal: {
        disabled: false,
        value: ""
      },
      numero_fiscal:{
        disabled: false,
        value: ""
      },
      telefono_fiscal: {
        disabled: false,
        value: ""
      },
      fax_fiscal: {
        disabled: false,
        value: ""
      },
      email_fiscal: {
        disabled: false,
        value: ""
      },
      descripcion_factura:{
        disabled: false,
        value: ""
      },
      estadofa: {
        disabled: true,
        validar: false,
        value: ""
      },
      municipiofa: {
        disabled: true,
        help: "Municipio de la oficina fiscal",
        validar: false,
        value: ""
      },
      parroquiafa: {
        disabled: true,
        help: "Parroquia de la oficina fiscal",
        validar: false,
        value: ""
      },
      ciudad_factura: {
        disabled: false,
        value: ""
      },
      avenida_calle_factura: {
        disabled: false,
        value: ""
      },
      edificio_quinta_factura: {
        disabled: false,
        value: ""
      },
      piso_factura: {
        disabled: false,
        value: ""
      },
      numero_factura: {
        disabled: false,
        value: ""
      },
      telefono_factura: {
        disabled: false,
        value: ""
      },
      fax_factura: {
        disabled: false,
        value: ""
      },
      correo_factura: {
        disabled: false,
        value: ""
      },
      empleado: {
        checked: true
      }
    },
      alert:{
      message: "",
      mostrar: false
    },
      formSearch: {
      submit: {
        disabled: true,
        html: "Buscar"
      },
      inputSearch: {
        disabled: true,
        value: ""
      },
      select: {
        disabled:false,
        value: ""
      }
    },
    formSearchG: {
      submitG: {
        disabled: true,
        html: "BuscaRr"
      },
      inputSearchG: {
        disabled: true,
        value: ""
      },
      selectG: {
        disabled:false,
        value: ""
      }
    },
      usuarios: {
      mostrar: false,
      registros: []
    },
    detalleUsuario: {
      error: false,
      data: []
    },
    usuariosG: {
      mostrar: false,
      registros: []
    },
    detalleUsuarioG: {
      error: false,
      data: []
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

        self.idCliente = dataInit.infoClie.id;
        self.detalleUsuario.data.id=dataInit.infoClie.id_usuario;
        self.detalleUsuario.data.codigo=dataInit.infoClie.codigoU;
        self.detalleUsuario.data.nombre=dataInit.infoClie.nombre;
        self.detalleUsuarioG.data.codigo=dataInit.infoClie.codigoG;
        self.detalleUsuarioG.data.nombre=dataInit.infoClie.nombreG;
        self.formSearch.inputSearch.value= dataInit.infoClie.codigoU;
        self.formSearchG.inputSearchG.value= dataInit.infoClie.codigoG;
        self.form.codigoCliente.value = dataInit.infoClie.codigo;
        self.form.rif.value = dataInit.infoClie.rif;
        self.form.nit.value = dataInit.infoClie.nit;
        self.form.razon_social.value = dataInit.infoClie.razon_social;
        self.form.ciudad_fiscal.value = dataInit.infoClie.ciudad_fiscal;
        self.form.avenida_calle_fiscal.value = dataInit.infoClie.avenida_calle_fiscal;
        self.form.edificio_quinta_fiscal.value = dataInit.infoClie.edificio_quinta_fiscal;        
        self.form.piso_fiscal.value = dataInit.infoClie.piso_fiscal;
        self.form.numero_fiscal.value = dataInit.infoClie.numero_fiscal;
        self.form.telefono_fiscal.value = dataInit.infoClie.telefono_fiscal;
        self.form.fax_fiscal.value = dataInit.infoClie.fax_fiscal;
        self.form.email_fiscal.value = dataInit.infoClie.email_fiscal;
        self.form.descripcion_factura.value = dataInit.infoClie.descripcion_factura;
        self.form.ciudad_factura.value = dataInit.infoClie.ciudad_factura;
        self.form.avenida_calle_factura.value = dataInit.infoClie.avenida_calle_factura;
        self.form.edificio_quinta_factura.value = dataInit.infoClie.edificio_quinta_factura;        
        self.form.piso_factura.value = dataInit.infoClie.piso_factura;
        self.form.numero_factura.value = dataInit.infoClie.numero_factura;
        self.form.telefono_factura.value = dataInit.infoClie.telefono_factura;
        self.form.fax_factura.value = dataInit.infoClie.fax_factura;
        self.form.correo_factura.value = dataInit.infoClie.correo_factura;

        self.comboEstadosfi = dataInit.estadosfi;

          self.comboMunicipiosfi = dataInit.municipiosfi;
          self.comboParroquiasfi = dataInit.parroquiasfi;

          self.form.estadofi.disabled = false;
          self.form.municipiofi.disabled = false;
          self.form.parroquiafi.disabled = false;

          self.form.estadofi.validar = true;
          self.form.municipiofi.validar = true;
          self.form.parroquiafi.validar = true;

          self.form.estadofi.value = dataInit.infoClie.id_estado_fiscal;
          self.form.municipiofi.value = dataInit.infoClie.id_municipio_fiscal;
          self.form.parroquiafi.value = dataInit.infoClie.id_parroquia_fiscal;

        self.comboEstadosfa = dataInit.estadosfa;

          self.comboMunicipiosfa = dataInit.municipiosfa;
          self.comboParroquiasfa = dataInit.parroquiasfa;

          self.form.estadofa.disabled = false;
          self.form.municipiofa.disabled = false;
          self.form.parroquiafa.disabled = false;

          self.form.estadofa.validar = true;
          self.form.municipiofa.validar = true;
          self.form.parroquiafa.validar = true;

          self.form.estadofa.value = dataInit.infoClie.id_estado_factura;
          self.form.municipiofa.value = dataInit.infoClie.id_municipio_factura;
          self.form.parroquiafa.value = dataInit.infoClie.id_parroquia_factura;

      }else{
        errorInit();
      }

  },
  created: async function () {

    let checkDataInitReady = setInterval(() => {
      if (self.form.codigoCliente.value  !== '') {
        clearInterval(checkDataInitReady);

        new AutoNumeric('#codigoCliente', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '',
          leadingZero: 'keep',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#codigoCliente").set(self.form.codigoCliente.value);

        new AutoNumeric('#rif', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#rif").set(self.form.rif.value);

        new AutoNumeric('#nit', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#nit").set(self.form.nit.value);

        new AutoNumeric('#numero_fiscal', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#numero_fiscal").set(self.form.numero_fiscal.value);

        new AutoNumeric('#numero_factura', {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          modifyValueOnWheel: false
        });

        AutoNumeric.getAutoNumericElement("#numero_factura").set(self.form.numero_factura.value);

        var indices = ["rif","nit","razon_social","ciudad_fiscal","avenida_calle_fiscal","edificio_quinta_fiscal","piso_fiscal","numero_fiscal","telefono_fiscal","fax_fiscal","email_fiscal","descripcion_factura","ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
        indices.forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = false;
        });

      }
    }, 1000);

  },
  mounted: async function () {
  },
  updated: function () {},
  methods:{

    buscar: function(e){

      self.alert.mostrar = false;

      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;

        let parametros = {
          buscarPor: self.formSearch.select.value,
          dato: self.formSearch.inputSearch.value
        };

        axios.get('/buscarUsuariosS', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.usuarios.mostrar = true;
            self.usuarios.registros = response.data.usuarios;
            $('#modal-detalle-usuario').modal("show");
            $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");


          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.usuarios.registros = [];
          self.usuarios.mostrar = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert.message = message;

        });

      }else{

        $(".inputSearch").parent().find(".mensaje").html("Campo requerido").addClass("invalid-feedback");
        $(".inputSearch").addClass("error");
        zenscroll.toY($(".inputSearch").offset().top - 100);

      }

    },
    buscarG: function(e){

      self.alert.mostrar = false;

      if(self.formSearchG.inputSearchG.value.trim() !== ""){

        self.formSearchG.submitG.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearchG.submitG.disabled = true;

        let parametros = {
          buscarPor: self.formSearchG.selectG.value,
          dato: self.formSearchG.inputSearchG.value
        };

        axios.get('/buscarUsuariosG', {params: parametros})
        .then(function (response) {

          self.formSearchG.submitG.html = 'Buscar';
          self.formSearchG.submitG.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.usuariosG.mostrar = true;
            self.usuariosG.registros = response.data.usuariosG;
            $('#modal-detalle-usuarioG').modal("show");
            $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");


          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearchG.submitG.html = 'Buscar';
          self.formSearchG.submitG.disabled = false;

          self.alert.mostrar = true;

          self.usuariosG.registros = [];
          self.usuariosG.mostrar = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert.message = message;

        });

      }else{

        $(".inputSearchG").parent().find(".mensaje").html("Campo requerido").addClass("invalid-feedback");
        $(".inputSearchG").addClass("error");
        zenscroll.toY($(".inputSearchG").offset().top - 100);

      }

    },
    tipoFiltro: function(e){

      let opcion = parseInt(e.target.value);
      let valoresPermitidos = [1,2,3,4];

      self.usuarios.mostrar = false;
      self.usuarios.registros = [];

      if(valoresPermitidos.includes(opcion)){
        self.formSearch.inputSearch.disabled = false;
        self.formSearch.submit.disabled = false;
      }else{
        self.formSearch.inputSearch.disabled = true;
        self.formSearch.submit.disabled = true;
      }

    },
    tipoFiltroG: function(e){

      let opcion = parseInt(e.target.value);
      let valoresPermitidos = [1,2,3,4];

      self.usuariosG.mostrar = false;
      self.usuariosG.registros = [];

      if(valoresPermitidos.includes(opcion)){
        self.formSearchG.inputSearchG.disabled = false;
        self.formSearchG.submitG.disabled = false;
      }else{
        self.formSearchG.inputSearchG.disabled = true;
        self.formSearchG.submitG.disabled = true;
      }

    },
    evaluarCampo: function(id, e){

      if(e.target.type === 'text'){
        self.formSearch[id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      if(id === "inputSearch" && self.formSearch["inputSearch"].value.trim() === ""){
        self.usuarios.registros = [];
        self.usuarios.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },
    evaluarCampoG: function(id, e){

      if(e.target.type === 'text'){
        self.formSearchG[id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      if(id === "inputSearchG" && self.formSearchG["inputSearchG"].value.trim() === ""){
        self.usuariosG.registros = [];
        self.usuariosG.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },

    SelecionarUsuario: function(idUsuario,e){

      self.detalleUsuario.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleUsuarios', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleUsuario.data = response.data.info;

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleUsuario.error = true;
        $('#modal-detalle-usuario').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    },
    SelecionarUsuarioG: function(idUsuario,e){

      self.detalleUsuarioG.error = false;
      $(e.target).removeClass("fa-search-plus").addClass("fa-cog fa-spin");

      let parametros = {
        idUsuario: idUsuario
      };

      axios.get('/detalleUsuarios', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.detalleUsuarioG.data = response.data.info;

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleUsuarioG.error = true;
        $('#modal-detalle-usuarioG').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-search-plus");

      });

    },


    municipiosfi: function(){

      self.form.municipiofi.value = ""
      self.form.municipiofi.disabled = true;
      self.form.parroquiafi.value = ""
      self.form.parroquiafi.disabled = true;
      self.form.parroquiafi.help = '<i class="fas fa-cog fa-spin"></i> buscando';

      axios.get('/municipios', { params: {
         id_estado: self.form.estadofi.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.form.parroquiafi.help = 'Parroquia de la oficina en donde se desempeña';
          self.comboMunicipiosfi = response.data;
          self.form.municipiofi.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        self.form.parroquiafi.help = 'Parroquia de la oficina en donde se desempeña';

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },
    parroquiasfi: function(){

      self.form.parroquiafi.disabled = true;

      axios.get('/parroquias', {params: {
         id_municipio: self.form.municipiofi.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.comboParroquiasfi = response.data;
          self.form.parroquiafi.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },

    municipiosfa: function(){

      self.form.municipiofa.value = ""
      self.form.municipiofa.disabled = true;
      self.form.parroquiafa.value = ""
      self.form.parroquiafa.disabled = true;
      self.form.parroquiafa.help = '<i class="fas fa-cog fa-spin"></i> buscando';

      axios.get('/municipios', { params: {
         id_estado: self.form.estadofa.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.form.parroquiafa.help = 'Parroquia de la oficina en donde se desempeña';
          self.comboMunicipiosfa = response.data;
          self.form.municipiofa.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        self.form.parroquiafa.help = 'Parroquia de la oficina en donde se desempeña';

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },
    parroquiasfa: function(){

      self.form.parroquiafa.disabled = true;

      axios.get('/parroquias', {params: {
         id_municipio: self.form.municipiofa.value
      }})
      .then(function (response) {

        if(response.status === 200 && response.data.length > 0){

          self.comboParroquiasfa = response.data;
          self.form.parroquiafa.disabled = false;

        }else{

          throw "error";

        }

      })
      .catch(error => {

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {

          self.form[indiceObjecto].disabled = true;

        });

        self.submitCrear.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },

    esEmpleado: function(e){

      if(self.form.empleado.checked){

        self.form.estadofi.disabled = false;
        self.form.estadofa.disabled = false;

        self.form.estadofi.validar = true;
        self.form.municipiofi.validar = true;
        self.form.parroquiafi.validar = true;
        self.form.estadofa.validar = true;
        self.form.municipiofa.validar = true;
        self.form.parroquiafa.validar = true;

        self.form.estadofi.value = "";
        self.form.estadofa.value = "";

      }else{

        $(e.target).parents("form").find(".form-group .mensaje").html("").removeClass("invalid-feedback");
        $(e.target).parents("form").find(".form-group .form-control").removeClass("error");

        self.form.estadofi.disabled = true;
        self.form.municipiofi.disabled = true;
        self.form.parroquiafi.disabled = true;
        self.form.estadofa.disabled = true;
        self.form.municipiofa.disabled = true;
        self.form.parroquiafa.disabled = true;

        self.form.estadofi.validar = false;
        self.form.municipiofi.validar = false;
        self.form.parroquiafi.validar = false;
        self.form.estadofa.validar = false;
        self.form.municipiofa.validar = false;
        self.form.parroquiafa.validar = false;

        self.form.estadofi.value = "";
        self.form.municipiofi.value = "";
        self.form.parroquiafi.value = "";
        self.form.estadofa.value = "";
        self.form.municipiofa.value = "";
        self.form.parroquiafa.value = "";
      }

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
          idCliente: self.idCliente,
          idUsuario: self.detalleUsuario.data.id,
          idUsuario2: self.detalleUsuarioG.data.id,
          codigoCliente: AutoNumeric.getAutoNumericElement("#codigoCliente").getNumber(),
          rif: AutoNumeric.getAutoNumericElement("#rif").getNumber(),
          nit: AutoNumeric.getAutoNumericElement("#nit").getNumber(),
          razon_social:  self.form.razon_social.value,
          parroquiafi: self.form.parroquiafi.value,
          ciudad_fiscal: self.form.ciudad_fiscal.value,
          avenida_calle_fiscal: self.form.avenida_calle_fiscal.value,
          edificio_quinta_fiscal: self.form.edificio_quinta_fiscal.value,
          piso_fiscal: self.form.piso_fiscal.value,
          numero_fiscal: AutoNumeric.getAutoNumericElement("#numero_fiscal").getNumber(),
          telefono_fiscal: self.form.telefono_fiscal.value,
          fax_fiscal: self.form.fax_fiscal.value,
          email_fiscal: self.form.email_fiscal.value,
          descripcion_factura: self.form.descripcion_factura.value,
          parroquiafa: self.form.parroquiafa.value,
          ciudad_factura: self.form.ciudad_factura.value,
          avenida_calle_factura: self.form.avenida_calle_factura.value,
          edificio_quinta_factura: self.form.edificio_quinta_factura.value,
          piso_factura: self.form.piso_factura.value,
          numero_factura: AutoNumeric.getAutoNumericElement("#numero_factura").getNumber(),
          telefono_factura: self.form.telefono_factura.value,
          fax_factura: self.form.fax_factura.value,
          correo_factura: self.form.correo_factura.value
        }

        self.submitActualizar.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitActualizar.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });

        axios.post('/modificarCliente', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){

            var indices = ["rif","nit","razon_social","ciudad_fiscal","avenida_calle_fiscal","edificio_quinta_fiscal","piso_fiscal","numero_fiscal","telefono_fiscal","fax_fiscal","email_fiscal","descripcion_factura","ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
            if(self.form.empleado.checked){
            indices.push("estadofi","municipiofi","parroquiafi","estadofa","municipiofa","parroquiafa");
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

          var indices = ["rif","nit","razon_social","ciudad_fiscal","avenida_calle_fiscal","edificio_quinta_fiscal","piso_fiscal","numero_fiscal","telefono_fiscal","fax_fiscal","email_fiscal","descripcion_factura","ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
           if(self.form.empleado.checked){
            indices.push("estadofi","municipiofi","parroquiafi","estadofa","municipiofa","parroquiafa");
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

          }else if(input.type === 'text' || input.type === 'textarea'){

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
    refreshView: function(){
      window.location.href = "/formBuscarCliente";
    }

  }// Fin methods

});
