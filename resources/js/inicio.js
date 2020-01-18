require('bootstrap');
window.Vue = require('vue');
var self;

Vue.component('menu-principal', require('./components/menuPrincipal.vue').default);

var app = new Vue({

  el: '#inicio',
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
