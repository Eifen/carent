require('bootstrap');
window.Vue = require('vue');
window.$ = require('jquery');
window.zenscroll = require('zenscroll');
window.axios = require('axios');
window.AutoNumeric = require('autonumeric');
const CryptoJS = require("crypto-js");
const AES = require("crypto-js/aes");
var self;

var app = new Vue({

  el: '#inicio',
  data: {
    copyRight: `Sofguar © ${new Date().getFullYear()}`
  },
  beforeCreate: function(){

    self = this;

  },
  created: function () {},
  mounted: function () {

  },
  updated: function () {},
  methods:{

  }// Fin methods

});
