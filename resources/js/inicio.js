require('bootstrap');
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
var self;

Vue.component('menu-principal', require('./components/menuPrincipal.vue').default);
Vue.component('loading',require('./components/loading.vue').default);
Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

new Vue({

  el: '#app',
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
