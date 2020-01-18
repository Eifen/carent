require('bootstrap');
window.Vue = require('vue');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
var self;

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);

var app = new Vue({

  el: '#buscarUsuario',
  data: {
  },
  beforeCreate: function(){

    self = this;

  },
  created: function () {

  },
  mounted: function () {

    /*new AutoNumeric('#codigoUsuario', {
      decimalPlaces: 0,
      decimalCharacter: ',',
      digitGroupSeparator: '',
      leadingZero: 'keep'
    });
*/
  },
  updated: function () {},
  methods:{

  }// Fin methods

});
