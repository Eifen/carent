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
                 empresas: response.data.empresas,
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
    comboEstatus: [],
    comboEmpresas: [],
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
        },
        horas_adicionales:{
          disabled: true,
          state: null
        },
      },
      mostrar: false
    },
    loading: true,
    modalAgregarHora: {
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
          html: "Si, quiero agregar el monto!",
          show: true
        },
        submit: {
          disabled: false,
          html: "",
          htmlInit: "Agregar horas",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        }
      },
      footer: {
        hide: true
      },
      form: {
        campos: {
          horaAdicional: {
            autonumeric: null,
            decPlace: 2,
            decString: ",",
            disabled: true,
            invalidFeedback: "",
            state: null,
            thouSep: ".",
            value: null
          }
        }
      },
      horasAdicionales: {
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
      }
    },
    modalAgregarMonto: {
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
          html: "Si, quiero agregar el monto!",
          show: true
        },
        submit: {
          disabled: false,
          html: "",
          htmlInit: "Agregar monto",
          htmlLoading: '<i class="fas fa-cog fa-spin"></i>',
          show: false
        }
      },
      division: "",
      footer: {
        hide: true
      },
      form: {
        campos: {
          montoAdicional: {
            autonumeric: null,
            decPlace: 2,
            decString: ",",
            disabled: true,
            invalidFeedback: "",
            state: null,
            thouSep: ".",
            value: null
          }
        }
      },
      idProyDiv: null,
      montosAdicionales: {
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
      }
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
        },
        horas_adicionales: {
          required,
          minValue: minValue(0)
        }
      }
    },
    modalAgregarHora: {
      form: {
        campos: {
          horaAdicional: {
            value: {
              required
            }
          }
        }
      }
    },
    modalAgregarMonto: {
      form: {
        campos: {
          montoAdicional: {
            value: {
              required
            }
          }
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

      dataInit.empresas.forEach((item, i) => {
        self.comboEmpresas.push({text:item.razon_social, value: item.id});
      });

      dataInit.monedas.forEach((item, i) => {
        self.comboMonedas.push({text:item.moneda, value: item.id, simbolo: item.simbolo});
      });

      self.idProyecto = dataInit.info.id;
      self.form.campos.descripcion = dataInit.info.descripcion;
      self.form.camposAtributos.cliente.valor = dataInit.info.razon_social;
      self.form.camposAtributos.cliente.valorFocus = dataInit.info.razon_social;
      self.form.camposAtributos.cliente.valorBlur = dataInit.info.razon_social;
      self.form.campos.cliente = dataInit.info.id_cliente,
      self.form.campos.estatus = dataInit.info.id_estatus;
      self.form.campos.empresa = dataInit.info.id_empresa;
      self.form.campos.fechaContratacion = dataInit.info.fecha_contratacion;
      self.form.camposAtributos.socio.valor = dataInit.info.nombre_socio;
      self.form.camposAtributos.socio.valorFocus = dataInit.info.nombre_socio;
      self.form.camposAtributos.socio.valorBlur = dataInit.info.nombre_socio;
      self.form.campos.socio = dataInit.info.id_socio;
      self.form.camposAtributos.socioCalidad.valor = dataInit.info.nombre_socio_calidad;
      self.form.camposAtributos.socioCalidad.valorFocus = dataInit.info.nombre_socio_calidad;
      self.form.camposAtributos.socioCalidad.valorBlur = dataInit.info.nombre_socio_calidad;
      self.form.campos.socioCalidad = dataInit.info.id_socio_calidad;
      self.form.camposAtributos.gerente.valor = dataInit.info.nombre_gerente;
      self.form.camposAtributos.gerente.valorFocus = dataInit.info.nombre_gerente;
      self.form.camposAtributos.gerente.valorBlur = dataInit.info.nombre_gerente;
      self.form.campos.gerente = dataInit.info.id_gerente;
      self.form.campos.montoEn = dataInit.info.id_moneda;
      self.form.camposAtributos.montoEn.simbolo = dataInit.info.simbolo;
      self.form.campos.monto = dataInit.info.monto;
      self.comboDivisiones = dataInit.divisiones;
      self.form.campos.horas = dataInit.info.horas_contratadas;
      self.form.campos.horas_adicionales = dataInit.info.horas_adicionales;

      self.asignarHoras(dataInit.infodivi);

      self.form.camposAtributos.descripcion.disabled = false;
      self.form.camposAtributos.cliente.disabled = false;
      self.form.camposAtributos.estatus.disabled = false;
      self.form.camposAtributos.empresa.disabled = false;
      self.form.camposAtributos.fechaContratacion.disabled = false;
      self.form.camposAtributos.socio.state = true;
      self.form.camposAtributos.socio.disabled = false;
      self.form.camposAtributos.socio.help = self.form.camposAtributos.socio.helpInit;
      self.form.camposAtributos.socioCalidad.state = true;
      self.form.camposAtributos.socioCalidad.disabled = false;
      self.form.camposAtributos.socioCalidad.help = self.form.camposAtributos.socioCalidad.helpInit;
      self.form.camposAtributos.gerente.state = true;
      self.form.camposAtributos.gerente.disabled = false;
      self.form.camposAtributos.gerente.help = self.form.camposAtributos.gerente.helpInit;
      self.form.camposAtributos.montoEn.disabled = false;
      self.form.camposAtributos.monto.disabled = false;
      self.form.camposAtributos.divisiones.disabled = false;

      self.modalAgregarMonto.montosAdicionales.encabezado = [
        { key: 'numero', label: '#' },
        { key: 'monto', label: 'Monto' },
        { key: 'fecha', label: 'De Fecha' },
        { key: 'opciones', label: ' ' }
      ];

      self.modalAgregarHora.horasAdicionales.encabezado = [
        { key: 'numero', label: '#' },
        { key: 'horas', label: 'Horas' },
        { key: 'fecha', label: 'De Fecha' },
        { key: 'opciones', label: ' ' }
      ];

      var data = [];
      for (var i = 0; i < dataInit.infodivi.length; i++) {
        for (var j = 0; j < self.comboDivisiones.length; j++) {
          if (dataInit.infodivi[i].id === self.comboDivisiones[j].id) {
            data[i] = self.comboDivisiones[j];
          }
        }
      }
      self.form.campos.divisiones = data;
      self.form.botones.submit.html = self.form.botones.submit.htmlInit;
      self.form.mostrar = true;
      self.loading = false;

    }else{

      Object.keys(self.form).forEach(function(indiceObjecto, indice) {

        if(self.form[indiceObjecto].hasOwnProperty('disabled') && indiceObjecto !== "horas"){
          self.form[indiceObjecto].disabled = true;
        }

      });

      self.mostrarAlertForm(self.alertGeneral, true, "warning", "Existe un error!, consulte con el administrador del sistema.", false, false, 0);
      self.loading = false;
      self.form.mostrar = false;

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

    self.$refs["modal-agregar-monto"].$on('shown', () => {

      self.enableDisabledForm(self.modalAgregarMonto.form.campos, false);

      self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
      self.modalAgregarMonto.botones.submit.show = true;
      self.modalAgregarMonto.botones.cancelar.show = false;
      self.modalAgregarMonto.botones.confirmar.show = false;

      if(self.modalAgregarMonto.form.campos.montoAdicional.autonumeric === null){

        let montoA = self.$refs["montoAdicional"].$el

        self.modalAgregarMonto.form.campos.montoAdicional.autonumeric = new AutoNumeric(montoA, {
          decimalPlaces: 2,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          emptyInputBehavior: 0,
          maximumValue: '99999999999999999999.99',
          minimumValue: 0,
          modifyValueOnWheel: false
        });

      }

      self.montosAdicionales();

    });

    self.$refs["modal-agregar-monto"].$on('hidden', () => {

      self.modalAgregarMonto.footer.hide = true;

      self.modalAgregarMonto.botones.confirmar.show = false;
      self.modalAgregarMonto.botones.cancelar.show = false;
      self.modalAgregarMonto.botones.submit.show = true;
      self.modalAgregarMonto.botones.confirmar.disabled = false;
      self.modalAgregarMonto.botones.cancelar.disabled = false;
      self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
      self.modalAgregarMonto.botones.submit.disabled = false;

      self.modalAgregarMonto.form.campos.montoAdicional.autonumeric = null;
      self.modalAgregarMonto.form.campos.montoAdicional.value = null;

      self.modalAgregarMonto.montosAdicionales.registros = [];
      self.modalAgregarMonto.montosAdicionales.total = 0;

      self.enableDisabledForm(self.modalAgregarMonto.form.campos, false);
      self.mostrarAlertForm(self.modalAgregarMonto.alert);

      self.modalAgregarMonto.montosAdicionales.cargando = true;

    });

    self.$refs["modal-agregar-hora"].$on('shown', () => {

      self.enableDisabledForm(self.modalAgregarHora.form.campos, false);

      self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
      self.modalAgregarHora.botones.submit.show = true;
      self.modalAgregarHora.botones.cancelar.show = false;
      self.modalAgregarHora.botones.confirmar.show = false;

      if(self.modalAgregarHora.form.campos.horaAdicional.autonumeric === null){

        let horaA = self.$refs["horaAdicional"].$el

        self.modalAgregarHora.form.campos.horaAdicional.autonumeric = new AutoNumeric(horaA, {
          decimalPlaces: 0,
          decimalCharacter: ',',
          digitGroupSeparator: '.',
          emptyInputBehavior: 0,
          maximumValue: '99999999999999999999',
          minimumValue: 0,
          modifyValueOnWheel: false
        });

      }

      self.horasAdicionales();

    });

    self.$refs["modal-agregar-hora"].$on('hidden', () => {

      self.modalAgregarHora.footer.hide = true;

      self.modalAgregarHora.botones.confirmar.show = false;
      self.modalAgregarHora.botones.cancelar.show = false;
      self.modalAgregarHora.botones.submit.show = true;
      self.modalAgregarHora.botones.confirmar.disabled = false;
      self.modalAgregarHora.botones.cancelar.disabled = false;
      self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
      self.modalAgregarHora.botones.submit.disabled = false;

      self.modalAgregarHora.form.campos.horaAdicional.autonumeric = null;
      self.modalAgregarHora.form.campos.horaAdicional.value = null;

      self.modalAgregarHora.horasAdicionales.registros = [];
      self.modalAgregarHora.horasAdicionales.total = 0;

      self.enableDisabledForm(self.modalAgregarHora.form.campos, false);
      self.mostrarAlertForm(self.modalAgregarHora.alert);

      self.modalAgregarHora.horasAdicionales.cargando = true;

      self.modalAgregarMonto.idProyDiv = null;
      self.modalAgregarHora.division = "";

    });

  },
  methods:{

    buscarCliente: function(){

      self.cleanFieldForm(self.form.camposAtributos.cliente);
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

      self.cleanFieldForm(self.form.camposAtributos[tipoSocio]);
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

      self.cleanFieldForm(self.form.camposAtributos.gerente);
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

      self.cleanFieldForm(self.form.camposAtributos.monto);

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

        self.mostrarAlertForm(self.form.alert, true, "warning", "¿Estas seguro de modificar este proyecto?", false, false, 0);

      }

    },
    cancelarModificarProyecto: function(){

      self.form.botones.confirmar.show = true;
      self.form.botones.submit.show = false;
      self.form.botones.cancelar.show = false;

      self.mostrarAlertForm(self.form.alert);

    },
    modificar: async function(){

      self.mostrarAlertForm(self.form.alert);

      const divisiones = [];
      self.form.camposAtributos.divisiones.divisiones.forEach((item, i) => {
        let hora = parseInt(item.horas.value);
        divisiones.push({id: item.id, horas: hora, id_gerente: item.gerente.id});
      });

      //Obtenemos valores
      let parametros = {
        id_proyecto: self.idProyecto,
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
      };

      self.form.botones.cancelar.disabled = true;
      self.form.botones.submit.disabled = true;
      self.form.botones.submit.html = self.form.botones.submit.htmlLoading;

      Object.keys(self.form.camposAtributos).forEach((indice, i) => {

        if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "horas"){
          self.form.camposAtributos[indice].disabled = true;
        }

      });

      axios.post('/modificarProyecto', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.form.botones.cancelar.show = false;
          self.form.botones.submit.show = false;
          self.form.botones.confirmar.show = true;

          self.mostrarAlertForm(self.form.alert, true, "success", response.data.message, true, true, 10);

          Object.keys(self.form.camposAtributos).forEach((indice, i) => {

            if(self.form.camposAtributos[indice].hasOwnProperty("disabled") && indice !== "horas"){
              self.form.camposAtributos[indice].disabled = false;
            }

          });

          self.form.botones.submit.disabled = false;
          self.form.botones.submit.html = self.form.botones.submit.htmlInit
          self.form.botones.cancelar.disabled = false;

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
            value: item.hasOwnProperty('horas_contratadas') ? item.horas_contratadas : 0,
          },
          id: item.id,
          idProyDiv: item.id_proy_div
        }

        let id_gerente = item.hasOwnProperty('id_gerente') ? item.id_gerente : null;

        self.$set(self.form.camposAtributos.divisiones.divisiones, index, division);
        self.gerenteDivision(index, item.id, id_gerente);

      });

      if(self.form.camposAtributos.divisiones.divisiones.length === 0){
        self.form.campos.horas = 0;
        self.form.camposAtributos.horas.invalidFeedback = "";
        self.form.camposAtributos.horas.state = null;
      }

      self.horasTotales();

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

      self.cleanFieldForm(self.form.camposAtributos.divisiones.divisiones[index].horas);
      self.horasTotales();

    },
    horasTotales: function(){

      var total = 0;

      for(var i = 0; i < self.form.camposAtributos.divisiones.divisiones.length; i++){
        total = total + parseInt(self.form.camposAtributos.divisiones.divisiones[i].horas.value);
      }

      total = (isNaN(total)) ? 0 : total;

      self.form.campos.horas = total;

      self.cleanFieldForm(self.form.camposAtributos.horas);

    },
    gerenteDivision: function(index, id_division, id_gerente = null){

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

        if(id_gerente !== null){
          self.form.camposAtributos.divisiones.divisiones[index].gerente.id = id_gerente;
        }

      })
      .catch(error => {

        self.form.camposAtributos.divisiones.divisiones[index].gerente.help = self.form.camposAtributos.divisiones.divisiones[index].gerente.helpInit;
        self.form.camposAtributos.divisiones.divisiones[index].gerente.disabled = false;
        self.form.camposAtributos.divisiones.divisiones[index].gerente.state = false;

        var mensaje = (error.message) ? error.message : "Existe un error!, consulte con el administrador del sistema.";
        self.form.camposAtributos.divisiones.divisiones[index].gerente.invalidFeedback = mensaje;

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
    keyboard: function(e){

      if (e.keyCode === 13){
        self.actualizar();
      }

    },
    agregar_monto_adicional: function(){

      var formValido = true;

      self.mostrarAlertForm(self.modalAgregarMonto.alert);

      Object.keys(self.modalAgregarMonto.form.campos).forEach((indice, i) => {

        if(self.modalAgregarMonto.form.campos[indice].hasOwnProperty("state")){
          self.modalAgregarMonto.form.campos[indice].state = (self.modalAgregarMonto.form.campos[indice].state === true) ? true : null;
        }

        if(self.modalAgregarMonto.form.campos[indice].hasOwnProperty("invalidFeedback")){
          self.modalAgregarMonto.form.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.modalAgregarMonto.form.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.modalAgregarMonto.form.campos[indice].value;
        campo.$touch();

        if(campo.$invalid){

          self.modalAgregarMonto.form.campos[indice].state = false;
          const valorCampo = self.$v.modalAgregarMonto.form.campos[indice].value.$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.modalAgregarMonto.form.campos[indice].invalidFeedback = mensajeError.mensaje;

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

        self.modalAgregarMonto.botones.submit.disabled = true;
        self.modalAgregarMonto.botones.confirmar.show = true;
        self.modalAgregarMonto.botones.cancelar.show = true;

        self.mostrarAlertForm(self.modalAgregarMonto.alert, true, "warning", "¿Está seguro de agregar este monto adicional?", false, false, 0);
        self.modalAgregarMonto.footer.hide = false;

      }

    },
    cleanFieldForm: function(field){

      field.state = null;
      field.disabled = false;

    },
    enableDisabledForm: function(form, disabled){

      Object.keys(form).forEach((indice, i) => {

        if(form[indice].hasOwnProperty("state")){
          form[indice].state =  null;
        }

        if(form[indice].hasOwnProperty("disabled")){
          form[indice].disabled = disabled;
        }

      });

    },
    cancelarAgregarMonto: function(){

      self.modalAgregarMonto.botones.confirmar.show = false;
      self.modalAgregarMonto.botones.cancelar.show = false;
      self.modalAgregarMonto.botones.submit.show = true;
      self.modalAgregarMonto.botones.submit.disabled = false;

      self.mostrarAlertForm(self.modalAgregarMonto.alert);
      self.modalAgregarMonto.footer.hide = true;

    },
    agregarMontoAdicional: function(){

      self.mostrarAlertForm(self.modalAgregarMonto.alert);

      //Obtenemos valores
      let parametros = {
        monto: self.modalAgregarMonto.form.campos.montoAdicional.autonumeric.get(),
        id_proyecto: self.idProyecto
      }

      self.modalAgregarMonto.botones.confirmar.disabled = true;
      self.modalAgregarMonto.botones.cancelar.disabled = true;
      self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlLoading;

      self.enableDisabledForm(self.modalAgregarMonto.form.campos, true);

      axios.post('/agregarMontoAdicionalProy', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.modalAgregarMonto.form.campos.montoAdicional.autonumeric.set(0);
          self.modalAgregarMonto.form.campos.montoAdicional.value = 0;

          self.enableDisabledForm(self.modalAgregarMonto.form.campos, false);

          self.$nextTick(() => {
            self.$v.$reset();
          });

          self.mostrarAlertForm(self.modalAgregarMonto.alert, true, "success", response.data.message, true, true, 3);

          self.modalAgregarMonto.botones.confirmar.show = false;
          self.modalAgregarMonto.botones.cancelar.show = false;
          self.modalAgregarMonto.botones.confirmar.disabled = false;
          self.modalAgregarMonto.botones.cancelar.disabled = false;

          let data = self.registroTablaMontos(response.data.montos);
          self.modalAgregarMonto.montosAdicionales.registros = data.registros;
          self.modalAgregarMonto.montosAdicionales.total = data.total;

          setTimeout(function(){

            self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
            self.modalAgregarMonto.botones.submit.disabled = false;
            self.modalAgregarMonto.footer.hide = true;

          }, 3000);

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.enableDisabledForm(self.modalAgregarMonto.form.campos, false);

        self.modalAgregarMonto.botones.confirmar.disabled = false;
        self.modalAgregarMonto.botones.cancelar.disabled = false;
        self.modalAgregarMonto.botones.confirmar.show = false;
        self.modalAgregarMonto.botones.cancelar.show = false;
        self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
        self.modalAgregarMonto.botones.submit.disabled = false;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlertForm(self.modalAgregarMonto.alert, true, variante, mensaje, false, false, 0);

      });

    },
    montosAdicionales: function(){

      axios.get('/montosAdicionesProy',{
        params: {
          id_proyecto: self.idProyecto
        }
      }).then(function (response) {

        if(response.data.montos.length === 0){

          let mensaje = "No hay montos adicionales cargados";
          self.mostrarAlertForm(self.modalAgregarMonto.montosAdicionales.alert, true, "warning", mensaje, false, false, 0);

        }

        let data = self.registroTablaMontos(response.data.montos);
        self.modalAgregarMonto.montosAdicionales.registros = data.registros;
        self.modalAgregarMonto.montosAdicionales.total = data.total;

        self.modalAgregarMonto.montosAdicionales.cargando = false;

      }).catch(error => {

        let mensaje = "Ocurrió un error!";
        self.mostrarAlertForm(self.modalAgregarMonto.montosAdicionales.alert, true, "warning", mensaje, false, false, 0);
        self.modalAgregarMonto.montosAdicionales.cargando = false;
        self.modalAgregarMonto.form.campos.montoAdicional.disabled = true;
        self.modalAgregarMonto.botones.submit.disabled = true;

      });

    },
    registroTablaMontos: function(datos){

      const registros = [];
      var total = 0;
      datos.forEach((item, i) => {

        const monto = {
          numero: (i + 1),
          monto: self.form.camposAtributos.montoEn.simbolo+item.monto_formatted,
          fecha: item.fecha,
          id: item.id
        };

        registros.push(monto);
        total = total + parseFloat(item.monto);

      });

      return {
        registros: registros,
        total: self.form.camposAtributos.montoEn.simbolo+total
      };

    },
    eliminar_monto: async function(id_monto){

      await self.mostrarAlertForm(self.modalAgregarMonto.alert);

      self.enableDisabledForm(self.modalAgregarMonto.form.campos, false);

      self.modalAgregarMonto.botones.confirmar.disabled = false;
      self.modalAgregarMonto.botones.cancelar.disabled = false;
      self.modalAgregarMonto.botones.confirmar.show = false;
      self.modalAgregarMonto.botones.cancelar.show = false;
      self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
      self.modalAgregarMonto.botones.submit.disabled = false;

      //Obtenemos valores
      let parametros = {
        id_monto: id_monto,
        id_proyecto: self.idProyecto
      }

      axios.post('/eliminarMontosAdicionesProy', parametros).then(async function (response) {

        if(response.data.response){

          if(response.data.montos.length === 0){

            let mensaje = "No hay montos adicionales cargados";
            await self.mostrarAlertForm(self.modalAgregarMonto.montosAdicionales.alert, true, "warning", mensaje, false, false, 0);

          }

          let data = self.registroTablaMontos(response.data.montos);
          self.modalAgregarMonto.montosAdicionales.registros = data.registros;
          self.modalAgregarMonto.montosAdicionales.total = data.total;

          self.modalAgregarMonto.footer.hide = false;
          await self.mostrarAlertForm(self.modalAgregarMonto.alert, true, "success", response.data.message, false, false, 0);

          setTimeout(function(){

            self.modalAgregarMonto.botones.submit.html = self.modalAgregarMonto.botones.submit.htmlInit;
            self.modalAgregarMonto.botones.submit.disabled = false;
            self.modalAgregarMonto.footer.hide = true;

          }, 3000);

        }else{
          throw response.data;
        }

      }).catch(error => {

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlertForm(self.modalAgregarMonto.alert, true, variante, mensaje, false, false, 0);
        self.modalAgregarMonto.footer.hide = false;

      });

    },
    modalAgregarHoraAdicional: function(id, division){

      self.modalAgregarMonto.idProyDiv = id;
      self.modalAgregarHora.division = division;

      self.$refs['modal-agregar-hora'].show();

    },
    horasAdicionales: function(){

      axios.get('/horasAdicionesProyDiv',{
        params: {
          id_proy_div: self.modalAgregarMonto.idProyDiv
        }
      }).then(function (response) {

        if(response.data.horas.length === 0){

          let mensaje = "No hay horas adicionales cargadas";
          self.mostrarAlertForm(self.modalAgregarHora.horasAdicionales.alert, true, "warning", mensaje, false, false, 0);

        }

        let data = self.registroTablaHoras(response.data.horas);
        self.modalAgregarHora.horasAdicionales.registros = data.registros;
        self.modalAgregarHora.horasAdicionales.total = data.total;

        self.modalAgregarHora.horasAdicionales.cargando = false;

      }).catch(error => {

        let mensaje = "Ocurrió un error!";
        self.mostrarAlertForm(self.modalAgregarHora.horasAdicionales.alert, true, "warning", mensaje, false, false, 0);
        self.modalAgregarHora.horasAdicionales.cargando = false;
        self.modalAgregarHora.form.campos.horaAdicional.disabled = true;
        self.modalAgregarHora.botones.submit.disabled = true;

      });

    },
    registroTablaHoras: function(datos){

      const registros = [];
      var total = 0;
      datos.forEach((item, i) => {

        const horas = {
          numero: (i + 1),
          horas: item.horas,
          fecha: item.fecha,
          id: item.id
        };

        registros.push(horas);
        total = total + parseFloat(item.horas);

      });

      return {
        registros: registros,
        total: total
      };

    },
    agregarHoraAdicional: function(){

      var formValido = true;

      self.mostrarAlertForm(self.modalAgregarHora.alert);

      Object.keys(self.modalAgregarHora.form.campos).forEach((indice, i) => {

        if(self.modalAgregarHora.form.campos[indice].hasOwnProperty("state")){
          self.modalAgregarHora.form.campos[indice].state = (self.modalAgregarHora.form.campos[indice].state === true) ? true : null;
        }

        if(self.modalAgregarHora.form.campos[indice].hasOwnProperty("invalidFeedback")){
          self.modalAgregarHora.form.campos[indice].invalidFeedback = "";
        }

      });

      const arrayCampos = Object.keys(self.modalAgregarHora.form.campos);
      for(var i = 0; i <= (arrayCampos.length - 1); i++){

        let indice = arrayCampos[i];
        const campo = self.$v.modalAgregarHora.form.campos[indice].value;
        campo.$touch();

        if(campo.$invalid){

          self.modalAgregarHora.form.campos[indice].state = false;
          const valorCampo = self.$v.modalAgregarHora.form.campos[indice].value.$model;

          const arrayParams = Object.keys(campo.$params);
          for(var j = 0; j <= (arrayParams.length - 1); j++){

            let mensajeError = self.validadorMensajes(arrayParams[j], campo);
            self.modalAgregarHora.form.campos[indice].invalidFeedback = mensajeError.mensaje;

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

        self.modalAgregarHora.botones.submit.disabled = true;
        self.modalAgregarHora.botones.confirmar.show = true;
        self.modalAgregarHora.botones.cancelar.show = true;

        self.mostrarAlertForm(self.modalAgregarHora.alert, true, "warning", "¿Está seguro de agregar estas horas adicionales?", false, false, 0);
        self.modalAgregarHora.footer.hide = false;

      }

    },
    cancelarAgregarHora: function(){

      self.modalAgregarHora.botones.confirmar.show = false;
      self.modalAgregarHora.botones.cancelar.show = false;
      self.modalAgregarHora.botones.submit.show = true;
      self.modalAgregarHora.botones.submit.disabled = false;

      self.mostrarAlertForm(self.modalAgregarHora.alert);
      self.modalAgregarHora.footer.hide = true;

    },
    confirmarAgregarHoraAdicional: function(){

      self.mostrarAlertForm(self.modalAgregarHora.alert);

      //Obtenemos valores
      let parametros = {
        horas: self.modalAgregarHora.form.campos.horaAdicional.autonumeric.get(),
        id_proy_div: self.modalAgregarMonto.idProyDiv
      }

      self.modalAgregarHora.botones.confirmar.disabled = true;
      self.modalAgregarHora.botones.cancelar.disabled = true;
      self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlLoading;

      self.enableDisabledForm(self.modalAgregarHora.form.campos, true);

      axios.post('/agregarHoraAdicionalProyDiv', parametros)
      .then(function (response) {

        if(response.status === 200 && response.data.response === true){

          self.modalAgregarHora.form.campos.horaAdicional.autonumeric.set(0);
          self.modalAgregarHora.form.campos.horaAdicional.value = 0;
          self.form.campos.horas_adicionales = response.data.info.horas_adicionales;

          self.enableDisabledForm(self.modalAgregarHora.form.campos, false);

          self.$nextTick(() => {
            self.$v.$reset();
          });

          self.mostrarAlertForm(self.modalAgregarHora.alert, true, "success", response.data.message, true, true, 3);

          self.modalAgregarHora.botones.confirmar.show = false;
          self.modalAgregarHora.botones.cancelar.show = false;
          self.modalAgregarHora.botones.confirmar.disabled = false;
          self.modalAgregarHora.botones.cancelar.disabled = false;

          let data = self.registroTablaHoras(response.data.horas);
          self.modalAgregarHora.horasAdicionales.registros = data.registros;
          self.modalAgregarHora.horasAdicionales.total = data.total;

          setTimeout(function(){

            self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
            self.modalAgregarHora.botones.submit.disabled = false;
            self.modalAgregarHora.footer.hide = true;

          }, 3000);

        }else{

          throw response.data;

        }

      })
      .catch(error => {

        self.enableDisabledForm(self.modalAgregarHora.form.campos, false);

        self.modalAgregarHora.botones.confirmar.disabled = false;
        self.modalAgregarHora.botones.cancelar.disabled = false;
        self.modalAgregarHora.botones.confirmar.show = false;
        self.modalAgregarHora.botones.cancelar.show = false;
        self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
        self.modalAgregarHora.botones.submit.disabled = false;

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlertForm(self.modalAgregarHora.alert, true, variante, mensaje, false, false, 0);

      });

    },
    eliminar_hora: async function(id){

      await self.mostrarAlertForm(self.modalAgregarHora.alert);

      self.enableDisabledForm(self.modalAgregarHora.form.campos, false);

      self.modalAgregarHora.botones.confirmar.disabled = false;
      self.modalAgregarHora.botones.cancelar.disabled = false;
      self.modalAgregarHora.botones.confirmar.show = false;
      self.modalAgregarHora.botones.cancelar.show = false;
      self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
      self.modalAgregarHora.botones.submit.disabled = false;

      //Obtenemos valores
      let parametros = {
        id: id,
        id_proy_div: self.modalAgregarMonto.idProyDiv
      }

      axios.post('/eliminarHoraAdicionalProyDiv', parametros).then(async function (response) {

        if(response.data.response){

          if(response.data.horas.length === 0){

            let mensaje = "No hay montos adicionales cargados";
            await self.mostrarAlertForm(self.modalAgregarHora.horasAdicionales.alert, true, "warning", mensaje, false, false, 0);

          }

          let data = self.registroTablaHoras(response.data.horas);
          self.modalAgregarHora.horasAdicionales.registros = data.registros;
          self.modalAgregarHora.horasAdicionales.total = data.total;
          self.form.campos.horas_adicionales = response.data.info.horas_adicionales;

          self.modalAgregarMonto.footer.hide = false;
          await self.mostrarAlertForm(self.modalAgregarHora.alert, true, "success", response.data.message, false, false, 0);

          setTimeout(function(){

            self.modalAgregarHora.botones.submit.html = self.modalAgregarHora.botones.submit.htmlInit;
            self.modalAgregarHora.botones.submit.disabled = false;
            self.modalAgregarHora.footer.hide = true;

          }, 3000);

        }else{
          throw response.data;
        }

      }).catch(error => {

        if(error.message){

          var mensaje = error.message;
          var variante = "warning";

        }else{

          var mensaje = "Existe un error!, consulte con el administrador del sistema.";
          var variante = "danger";

        }

        self.mostrarAlertForm(self.modalAgregarHora.alert, true, variante, mensaje, false, false, 0);
        self.modalAgregarHora.footer.hide = false;

      });

    }

  }// Fin methods

});
