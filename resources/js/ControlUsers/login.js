import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';

const loginApp = createApp ({
    data(){
        return {
            controlEye: 'fa-solid fa-eye',
            TypeInputPassword: 'password',
            state: false //Estado de la vista. Por defecto esta en false
        }
    },
    methods:{
        changeInput(nuevoEstado){
            this.state === true
            ? (this.controlEye = 'fa-solid fa-eye-slash', this.TypeInputPassword = 'text')
            : (this.controlEye = 'fa-solid fa-eye', this.TypeInputPassword = 'password')

            this.state = nuevoEstado;
        }
    },
    computed:{},
    watch:{},
    components: { FontAwesome }
});
loginApp.mount('#app-login');
