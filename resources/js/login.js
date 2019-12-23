require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
var self;

var app = new Vue({

  el: '#login',
  data: {
    banner: [],
    copyRight: `Sofguar © ${new Date().getFullYear()}`
  },
  beforeCreate: function(){

  },
  created: function () {



  },
  mounted: function () {

    new AutoNumeric('#codigoUsuario', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: ''
    });

    new AutoNumeric('#codigoRecuperacion', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: ''
    });

  },
  updated: function () {

    $('.aliado').tooltip();

  },
  methods:{

    modalRecuperarClave: function(){

      $("#modal-recuperar-clave").modal("show");

    },
    recuperarClave: function(){
      alert("recuperar");
    },
    login: function(){
      alert("validar");
    }

  }// Fin methods

});
