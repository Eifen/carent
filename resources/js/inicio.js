require('bootstrap');
window.Vue = require('vue');
var self;

Vue.component('menu-principal', require('./components/menuPrincipal.vue').default);
Vue.component('loading',require('./components/loading.vue').default);

var app = new Vue({

  el: '#inicio',
  data: {
    loading: true
  },
  beforeCreate: function(){
    self = this;
  },
  created: function () {},
  mounted: function () {
    self.loading = false;
  },
  updated: function () {},
  methods:{
  }// Fin methods

});
