import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import { Exceptions } from '../../Excepciones/Excepciones';
import Loading from '../../Components/Loading.vue';
import ListingCrud from '../../Components/ListingCrud.vue';
import axios from 'axios';
import { AXIOSINTERVAL } from '../../app';

const clientsIndex = createApp ({
    data(){
        return {
            isMounted: false, //Controla el estado del loading y del listingCrud
            clientsColumns: {
                "column1":"Código",
                "column2":"Socio encargado",
                "column3":"Razon social",
                "column4":"Correo electrónico",
                "column5":"Estatus",
                "settings":{"columnS1":"Editar"}
            },
            selectSearch:{
                "select1":"Codigo",
                "select2":"Socio encargado",
                "select3":"Razon social",
                "select4":"Correo"
            },
            clientsData: [], //Array de Objetos clientes
            paginationLength: 50, //Tamaño máximo de registros por página
            maxPagination: 0, //Define el tamaño total de páginas
            tableTarget: 'tbl_clientes',
        }
    },
    created()
    {
        const paginationDTO = { "table": this.tableTarget, "lengthPage": this.paginationLength }

        axios.post('/clientes/limitPag',paginationDTO)
        .then(request => {
            if(request.status !== 200) throw request.data;
            //Si pasa la data, asignamos a maxPagination
            setTimeout( () => { this.maxPagination = Math.ceil(request.data); }, AXIOSINTERVAL)

        })
        .catch(error => {
            console.error(error);
        })
    },
    mounted()
    {
        //Cargamos la data de usuarios
        axios.post('/clientes/allClients')
        .then(request => {
            if(request.status === 200 && !request.data.response) throw request.data.message;
            //Si la respuesta es verdadera pasamos la data
            setTimeout(() => {
                this.clientsData = request.data.message;
            }, AXIOSINTERVAL);
        })
        .catch(error => {
            console.error(error);
        })
    },
    methods:{
        /**
         * Metodo que convierte los objetos de la data en formato JSON
         * @param {*} objectToConvert Almacena el formato del objeto a convertir
         */
        clientParse(objectToConvert){ if(this.isMounted) return JSON.parse(JSON.stringify(objectToConvert)) },
        /**
         * Metodo que crea una instancia en Sessión para pasarla temporalmente a un formulario de actualización
         * @param {*} idClient Almacena la ID seleccionada de la lista de clientes
         */
        editClient(idClient){
            const paramsDTO = { "codigoSQL": this.clientsData[idClient].codigo }

            axios.post('/clientes/update/loadingClient',paramsDTO)
            .then(request => {
                if (request.status === 200 && request.data === '') throw request;
                //Pasamos las validaciones
                window.location.href = "/clientes/update"
            })
            .catch(error =>{
                console.error(error)
            })
        },
        createClient(){ window.location.href = "/clientes/create" }
    },
    computed:{},
    watch:{
        clientsData() { this.isMounted = true }
    },
    components: { FontAwesome, Loading, ListingCrud }
});

if(document.getElementById('section-clients') !== null)
{
    clientsIndex.mount('#section-clients');
    window.location.hash = "#02";
}
