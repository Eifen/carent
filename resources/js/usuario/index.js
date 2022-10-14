import Vue from 'vue'
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
//window.$ = require('jquery');

Vue.component('menu-principal', require('../components/menuPrincipal.vue').default);
Vue.component('loading',require('../components/loading.vue').default);
Vue.component('listing-users',require('./listingUsers.vue').default);

new Vue({

    el: '#app',
    data: {
        loading: true
    },
    beforeCreate: function() {},
    created: function () {},
    mounted: function () {
        this.loading = false
    },
    updated: function () {},
    methods: {}

});
