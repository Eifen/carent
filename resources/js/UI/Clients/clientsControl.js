import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import Loading from '../../Components/Loading.vue';
import FormClients from '../../Components/Clients/FormClients.vue';
import { Exceptions } from '../../Excepciones/Excepciones';
import axios from 'axios';

const clientsControl = createApp ({
    data(){
        return {
            isMounted: false, //Controla el estado del componente
            isClick: false, //Controla el estado del boton
        }
    },
    methods:{
        /** Metodo que retorna a la página principal de clientes */
        returnClients(){ window.location.href = "/clientes" }
    },
    computed:{},
    watch:{},
    mounted(){
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    components: { FontAwesome, Loading, FormClients }
});

if(document.getElementById('create-client') !== null)
{
    if(document.getElementById('create-client') !== null) clientsControl.mount('#create-client');
    window.location.hash = "#02";
}
