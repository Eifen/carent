import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import Loading from '../../Components/Loading.vue';
import FormUsers from '../../Components/Users/FormUsers.vue';
import axios from 'axios';
import { AXIOSINTERVAL } from '../../app';

const createUser = createApp({
    data(){
        return{
            isMounted: false
        }
    },
    mounted(){
        this.isMounted = true;
    },
    components: { FontAwesome, Loading, FormUsers }
});

if(document.getElementById('create-users') !== null)
{
    createUser.mount('#create-users');
    window.location.hash = "#01";
};