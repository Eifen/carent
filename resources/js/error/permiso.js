import 'bootstrap';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
var self;

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

var app = new Vue({

  el: '#error',
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
