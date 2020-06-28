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

var app = new Vue({
  // se declaran las variables
  el: '#detalleFactcliente',
  data: {
    alertForm: {
      class: "",
      message: "",
      show: false
    },
    alertFormP: {
      class: "",
      message: "",
      show: false
    },
    comboEstadosfa: [],
    comboMunicipiosfa: [],
    comboParroquiasfa: [],
    refreshForm: false,
    form: {
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
        disabled: true,
        value: ""
      },
      avenida_calle_factura: {
        disabled: true,
        value: ""
      },
      edificio_quinta_factura: {
        disabled: true,
        value: ""
      },
      piso_factura: {
        disabled: true,
        value: ""
      },
      numero_factura: {
        disabled: true,
        value: ""
      },
      telefono_factura: {
        disabled: true,
        value: ""
      },
      fax_factura: {
        disabled: true,
        value: ""
      },
      correo_factura: {
        disabled: true,
        value: ""
      },
      empleado: {
        checked: false
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
    formSearchP: {
      submit: {
        disabled: true,
        html: "Selecionar Proyecto"
      }
    },
    clientes: {
      mostrar: false,
      registros: []
    },
    detalleCliente: {
      error: false,
      data: []
    },
    clienteProy: {
      mostrar: false,
      registros: []
    },
    detalleClienteProy: {
      error: false,
      data: []
    },
    detalleFactcliente: {
      error: false,
      data: []
    },
    submitActualizar: {
      content: "Actualizar Datos",
      disabled: false,
      show:true
    },
    submitCrear: {
      content: "Crear Detalle de Factura",
      disabled: false,
      show:true
    },
    permisoActualizar: false,
    permisoCrear: false
  },
  beforeCreate: function(){

    self = this;

  },
  created: function () {

  },
   mounted: function () {

    $('#modal-detalle-cliente').on('hidden.bs.modal', function () {

      self.clientes.registros = [];

    });
    $('#modal-detalle-clienteProy').on('hidden.bs.modal', function () {

      self.clienteProy.registros = [];

    });

  },
  updated: function () {},
  methods:{

    buscar: function(e){

      self.permisoActualizar = false;
      self.permisoCrear = false;
      self.formSearchP.submit.disabled = true;
      self.detalleClienteProy.error = false;
      self.clienteProy.mostrar = false;
      self.detalleClienteProy.data = [];
      self.alert.mostrar = false;
      self.clientes.mostrar = false;
      self.form.ciudad_factura.disabled = true;
      self.form.avenida_calle_factura.disabled = true;
      self.form.edificio_quinta_factura.disabled = true;
      self.form.piso_factura.disabled = true;
      self.form.numero_factura.disabled = true;
      self.form.telefono_factura.disabled = true;
      self.form.fax_factura.disabled = true;
      self.form.correo_factura.disabled = true;
      self.form.estadofa.disabled = true;
      self.form.municipiofa.disabled = true;
      self.form.parroquiafa.disabled = true;
      self.form.ciudad_factura.value = "";
      self.form.avenida_calle_factura.value = "";
      self.form.edificio_quinta_factura.value = "";
      self.form.piso_factura.value = "";
      self.form.numero_factura.value = "";
      self.form.telefono_factura.value = "";
      self.form.fax_factura.value = "";
      self.form.correo_factura.value = "";
      self.form.estadofa.value = "";
      self.form.municipiofa.value = "";
      self.form.parroquiafa.value = "";
      if(self.formSearch.inputSearch.value.trim() !== ""){

        self.formSearch.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearch.submit.disabled = true;
        // Obtenemos los valores
        let parametros = {
          buscarPor: self.formSearch.select.value,
          dato: self.formSearch.inputSearch.value
        };
        //Se utiliza el metodo get y se envian los parametros
        axios.get('/buscarCliente', {params: parametros})
        .then(function (response) {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.clientes.registros = response.data.clientes;
            $('#modal-detalle-cliente').modal("show");

          }else{

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearch.submit.html = 'Buscar';
          self.formSearch.submit.disabled = false;

          self.alert.mostrar = true;

          self.clientes.registros = [];
          self.clientes.mostrar = false;

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

    tipoFiltro: function(e){

      let opcion = parseInt(e.target.value);
      let valoresPermitidos = [1,2,3];

      self.clientes.mostrar = false;
      self.clientes.registros = [];

      if(valoresPermitidos.includes(opcion)){
        self.formSearch.inputSearch.disabled = false;
        self.formSearch.submit.disabled = false;
      }else{
        self.formSearch.inputSearch.disabled = true;
        self.formSearch.submit.disabled = true;
      }

    },

    evaluarCampo: function(id, e){

      if(e.target.type === 'text'){
        self.formSearch[id].value = (e.target.value.trim() === "") ? "" : $(e.target).val();
      }

      if(id === "inputSearch" && self.formSearch["inputSearch"].value.trim() === ""){
        self.clientes.registros = [];
        self.clientes.mostrar = false;
      }

      self.limpiarMensajeError(e);

    },

    SelecionarCliente: function(idCliente,e){

      self.detalleCliente.error = false;
      $(e.target).removeClass("fa-check-square").addClass("fa-cog fa-spin");
      // Obtenemos los valores
      let parametros = {
        idCliente: idCliente
      };
      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/detalleCliente', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.clientes.mostrar = true;
          self.detalleCliente.data = response.data.info;
          self.formSearchP.submit.disabled = false;
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-check-square");
          $('#modal-detalle-cliente').modal("hide");

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.detalleCliente.error = true;
        $('#modal-detalle-cliente').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-check-square");

      });

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

     Selecionar: function(e){

      self.alert.mostrar = false;
      self.permisoActualizar = false;
      self.permisoCrear = false;
      self.clienteProy.mostrar = false;
      self.detalleClienteProy.data = [];

      self.form.ciudad_factura.disabled = true;
      self.form.avenida_calle_factura.disabled = true;
      self.form.edificio_quinta_factura.disabled = true;
      self.form.piso_factura.disabled = true;
      self.form.numero_factura.disabled = true;
      self.form.telefono_factura.disabled = true;
      self.form.fax_factura.disabled = true;
      self.form.correo_factura.disabled = true;
      self.form.estadofa.disabled = true;
      self.form.municipiofa.disabled = true;
      self.form.parroquiafa.disabled = true;
      self.form.ciudad_factura.value = "";
      self.form.avenida_calle_factura.value = "";
      self.form.edificio_quinta_factura.value = "";
      self.form.piso_factura.value = "";
      self.form.numero_factura.value = "";
      self.form.telefono_factura.value = "";
      self.form.fax_factura.value = "";
      self.form.correo_factura.value = "";
      self.form.estadofa.value = "";
      self.form.municipiofa.value = "";
      self.form.parroquiafa.value = "";

        self.formSearchP.submit.html = '<i class="fas fa-cog fa-spin"></i>';
        self.formSearchP.submit.disabled = true;
        // Obtenemos los valores
        let parametros = {
          idCliente: self.detalleCliente.data.id
        };
        //Se utiliza el metodo get para su busqueda y se envian con los parametros
        axios.get('/buscarClieProyec', {params: parametros})
        .then(function (response) {

          self.formSearchP.submit.html = 'Selecionar Proyecto';
          self.formSearchP.submit.disabled = false;

          if(response.status === 200 && response.data.response === true){

            self.clienteProy.registros = response.data.clienteProy;
            $('#modal-detalle-clienteProy').modal("show");

          }else{

            self.alertFormP = {
              class : "alert alert-warning",
              message : "Este cliente no posee proyectos asignados.",
              show: true
            };
            setTimeout(function(){
              self.alertFormP = {
              class: "",
              message: "",
              show: false
              };
            }, 3000);

            throw response.data;

          }

        })
        .catch(error => {

          self.formSearchP.submit.html = 'Selecionar Proyecto';
          self.formSearchP.submit.disabled = false;

          self.alert.mostrar = true;

          self.clienteProy.registros = [];
          self.clienteProy.mostrar = false;

          if(error.response){

            var message = "Existe un error!, consulte con el administrador del sistema.";

          }else{

            var message = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";

          }

          self.alert.message = message;

        });
    },

    SelecionarClienteProy: function(clienteProy,e){

      self.detalleClienteProy.error = false;
      self.clienteProy.mostrar = false;
      $(e.target).removeClass("fa-check-square").addClass("fa-cog fa-spin");
      // Obtenemos los valores
      let parametros = {
        idclienteProy: clienteProy
      };
      //Se utiliza el metodo get para su busqueda y se envian con los parametros
      axios.get('/detalleClienteProy', {params: parametros})
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.clienteProy.mostrar = true;
          self.permisoCrear = response.data.permisoCrear;
          self.detalleClienteProy.data = response.data.infoproy;
          self.detalleFactcliente.data = response.data.infoFactCliente;
          self.form.ciudad_factura.value =  self.detalleFactcliente.data.ciudad_factura;
          self.form.avenida_calle_factura.value = self.detalleFactcliente.data.avenida_calle_factura;
          self.form.edificio_quinta_factura.value = self.detalleFactcliente.data.edificio_quinta_factura;        
          self.form.piso_factura.value = self.detalleFactcliente.data.piso_factura;
          self.form.numero_factura.value = self.detalleFactcliente.data.numero_factura;
          self.form.telefono_factura.value = self.detalleFactcliente.data.telefono_factura;
          self.form.fax_factura.value = self.detalleFactcliente.data.fax_factura;
          self.form.correo_factura.value = self.detalleFactcliente.data.email_factura;
          self.comboEstadosfa = response.data.estadosfa;
          self.comboMunicipiosfa = response.data.municipiosfa;
          self.comboParroquiasfa = response.data.parroquiasfa;

          var indices = ["ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura","estadofa","municipiofa","parroquiafa"];
  
            indices.forEach(function(indiceObjecto, indice) {
              self.form[indiceObjecto].disabled = false;
            });
          $('#modal-detalle-clienteProy').modal("hide");
          self.form.estadofa.validar = true;
          self.form.municipiofa.validar = true;
          self.form.parroquiafa.validar = true;

          if (self.detalleFactcliente.data.length !== 0) {
            self.form.estadofa.value = self.detalleFactcliente.data.id_estado_factura;
            self.form.municipiofa.value = self.detalleFactcliente.data.id_municipio_factura;
            self.form.parroquiafa.value = self.detalleFactcliente.data.id_parroquia_factura;
            self.permisoActualizar = response.data.permisoActualizar;
            self.permisoCrear = false;
          }

          if (self.permisoCrear === false && self.permisoActualizar === false) {
            var indices = ["ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura","estadofa","municipiofa","parroquiafa"];
  
            indices.forEach(function(indiceObjecto, indice) {
              self.form[indiceObjecto].disabled = true;
            });
          }
          $(e.target).removeClass("fa-cog fa-spin").addClass("fa-check-square");
          if (respose.data.permisoCrear) {
            var message = "No Puedes Crear Detalles de Facturacion";
          }
        

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.actualizar.mostrar = false;
        self.detalleClienteProy.error = true;
        $('#modal-detalle-clienteProy').modal("show");
        $(e.target).removeClass("fa-cog fa-spin").addClass("fa-check-square");

      });

    },

     municipiosfa: function(){

      if (self.form.estadofa.value !== null) {
      self.form.municipiofa.value = ""
      self.form.municipiofa.disabled = true;
      self.form.parroquiafa.value = ""
      self.form.parroquiafa.disabled = true;
      self.form.parroquiafa.help = '<i class="fas fa-cog fa-spin"></i> buscando';

      let parametros = {
          id_estado: self.form.estadofa.value
        };

      axios.get('/municipios',  {params: parametros})
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

        self.submitActualizar.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });
    }
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

        self.submitActualizar.disabled = true;

        self.alertForm = {
          class : "alert alert-warning",
          message : "Existe un error!, consulte con el administrador del sistema.",
          show: true
        };

      });

    },

    esEmpleado: function(e){

      if(self.form.empleado.checked){

        self.form.estadofa.disabled = false;

        self.form.estadofa.validar = true;
        self.form.municipiofa.validar = true;
        self.form.parroquiafa.validar = true;

        self.form.estadofa.value = "";

      }else{

        $(e.target).parents("form").find(".form-group .mensaje").html("").removeClass("invalid-feedback");
        $(e.target).parents("form").find(".form-group .form-control").removeClass("error");

        self.form.estadofa.disabled = true;
        self.form.municipiofa.disabled = true;
        self.form.parroquiafa.disabled = true;

        self.form.estadofa.validar = false;
        self.form.municipiofa.validar = false;
        self.form.parroquiafa.validar = false;

        self.form.estadofa.value = "";
        self.form.municipiofa.value = "";
        self.form.parroquiafa.value = "";
      }

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
          id_cliente: self.detalleClienteProy.data.id_cliente,
          id_proyecto: self.detalleClienteProy.data.id,
          id_fact_cliente: self.detalleFactcliente.data.id,
          parroquiafa: self.form.parroquiafa.value,
          ciudad_factura: self.form.ciudad_factura.value,
          avenida_calle_factura: self.form.avenida_calle_factura.value,
          edificio_quinta_factura: self.form.edificio_quinta_factura.value,
          piso_factura: self.form.piso_factura.value,
          numero_factura: self.form.numero_factura.value,
          telefono_factura: self.form.telefono_factura.value,
          fax_factura: self.form.fax_factura.value,
          correo_factura: self.form.correo_factura.value
        }

        self.submitActualizar.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitActualizar.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });
        //Se utiliza el metodo post para la actualizacion y se envian con los parametros
        axios.post('/actualizarFactCliente', parametros)
        .then(function (response) {

          if(response.status === 200 && response.data.response === true){
             self.refreshForm = true;
             self.permisoActualizar = false;
            var indices = ["ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
            if(self.form.empleado.checked){
            indices.push("estadofa","municipiofa","parroquiafa");
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

          var indices = ["ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
           if(self.form.empleado.checked){
            indices.push("estadofa","municipiofa","parroquiafa");
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

        self.alertForm = {
          class : "",
          message : "",
          show: false
        };

        //Obtenemos valores
        let parametros = {
          id_cliente: self.detalleClienteProy.data.id_cliente,
          id_proyecto: self.detalleClienteProy.data.id,
          id_fact_cliente: self.detalleFactcliente.data.id,
          parroquiafa: self.form.parroquiafa.value,
          ciudad_factura: self.form.ciudad_factura.value,
          avenida_calle_factura: self.form.avenida_calle_factura.value,
          edificio_quinta_factura: self.form.edificio_quinta_factura.value,
          piso_factura: self.form.piso_factura.value,
          numero_factura: self.form.numero_factura.value,
          telefono_factura: self.form.telefono_factura.value,
          fax_factura: self.form.fax_factura.value,
          correo_factura: self.form.correo_factura.value
        }

        self.submitCrear.content = '<i class="fas fa-cog fa-spin"></i>';
        self.submitCrear.disabled = true;

        Object.keys(self.form).forEach(function(indiceObjecto, indice) {
          self.form[indiceObjecto].disabled = true;
        });
        //Se utiliza el metodo post la creacion y se envian con los parametros
        axios.post('/crearFactCliente', parametros)
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

         var indices = ["ciudad_factura","avenida_calle_factura","edificio_quinta_factura","piso_factura","numero_factura","telefono_factura","fax_factura","correo_factura"];
  
          if(self.form.empleado.checked){
            indices.push("estadofa","municipiofa","parroquiafa");
          }

          indices.forEach(function(indiceObjecto, indice) {
            self.form[indiceObjecto].disabled = false;
          });
          self.submitCrear.content = 'Crear Detalle de Factura';
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
        self.crear();
      }

    },
    refreshView: function(){
      window.location.href = "/formDetalleFactCliente";
    }

  }// Fin methods

});