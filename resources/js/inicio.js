import 'bootstrap';
import Vue from 'vue';
import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap-vue/node_modules/bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import '@fortawesome/fontawesome-free/js/all.js';
var self;

Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);

import loading from './components/loading.vue'
import menuPrincipal from './components/menuPrincipal.vue'
Vue.component('loading', loading)
Vue.component('menu-principal', menuPrincipal)

new Vue({

  el: '#app',
  data: {
    loading: true
  },
  beforeCreate: function(){
    self = this;
  },
  mounted: function () {
    self.loading = false;
  }

});
