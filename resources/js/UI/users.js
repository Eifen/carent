import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';
import Loading from '../Components/Loading.vue';
import ListingCrud from '../Components/ListingCrud.vue';
import { Exceptions } from '../Excepciones/Excepciones';
import { UsersControl } from '../Models/UserModel';
import axios from 'axios';
import { AXIOSINTERVAL } from '../app';

const usersApp = createApp ({
    data(){
        return{
            isMounted: false, //Desactiva el loading cuando carga el componente
            usersColumn: {
                "column1": "Código",
                "column2": "Cédula",
                "column3": "Nombre",
                "column4": "Correo",
                "column5": "Estatus",
                "settings": { "columnS1": "Editar", "columnS2": "Permisos del Sistema" }
            },
            lengthColumns: 50,
            maxLengthPagination: 0, //Controlan la páginación
            tableTarget: "tbl_usuarios"
        }
    },
    //Ante de montar, consultamos el tamaño máximo de la páginación
    created(){
        const limitData =
        {
            "table": this.tableTarget,
            "lengthPage": this.lengthColumns
        }

        axios.post('/usuarios/limitPag',limitData)
        .then(request =>
            {
                if(request.status !== 200) throw request.data;
                //Si no  inicializa el error, procedemos a asignar las variables
                setTimeout(() => {this.maxLengthPagination = Math.round(request.data);}, AXIOSINTERVAL);
            })
        .catch(error =>
            {
                console.error(error)
            })
    },
    mounted()
    {

    },
    methods:{},
    computed:{},
    watch:{
        maxLengthPagination(){ this.isMounted = true; } //Desactivamos el loading
    },
    components: { FontAwesome, Loading, ListingCrud }
});

if(document.getElementById('section-users') !== null) usersApp.mount('#section-users');
