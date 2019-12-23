require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.axios = require('axios');
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

  },
  updated: function () {

    $('.aliado').tooltip();

  },
  methods:{

    modalRecuperarClave: function(){
      alert(1)
    }

  }// Fin methods

});
