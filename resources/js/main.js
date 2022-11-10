import Vue from 'vue'
import VueRouter from 'vue-router'
import App from './App.vue'
import router from './routes'
import 'bootstrap-icons/font/bootstrap-icons.css'
import 'bootstrap/dist/css/bootstrap.min.css'
import * as bootstrap from 'bootstrap'

Vue.use(VueRouter)

const app = new Vue({
    el: '#app',
    components: {
        App
    },
    router
}).$mount('#app')
