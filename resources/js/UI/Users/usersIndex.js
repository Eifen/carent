import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import Loading from '../../Components/Loading.vue';
import ListingCrud from '../../Components/ListingCrud.vue';
import axios from 'axios';
import { AXIOSINTERVAL } from '../../app';

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
            selectSearch: {
                "select1": "Codigo",
                "select2": "Nombre",
                "select3": "Cedula",
                "select4": "Correo"
            },
            lengthColumns: 50,
            maxLengthPagination: 0, //Controlan la páginación
            tableTarget: "users",
            usersData: [] //Proxy que almacena los datos del select
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
                setTimeout(() => {this.maxLengthPagination = Math.ceil(request.data);}, AXIOSINTERVAL);
            })
        .catch(error =>
            {
                console.error(error)
            })
    },
    mounted()
    {
        //Cargamos toda la data
        axios.post('/usuarios/allUsers')
        .then(request => {

            if(request.status === 200 && !request.data.response) throw request.data.message;
            //Si no se activa la exceptión, asignamos el objeto
            setTimeout(() => {
                this.usersData = request.data.message;
            }, AXIOSINTERVAL);
        })
        .catch(error => {
            console.error(error);
        })
    },
    methods:{
        //Metodos encargados de convertir el proxy a formato JSON
        dataParse(){ if(this.isMounted) return JSON.parse(JSON.stringify(this.usersData)); },
        titleParse(){ if(this.isMounted) return JSON.parse(JSON.stringify(this.usersColumn)); },
        searchParse(){ if(this.isMounted) return JSON.parse(JSON.stringify(this.selectSearch)); },
        //Metodo dedicados a las configuraciones de la tabla
        editUsuarios(idUsuario){
            //Seccionamos el ID y luego lo pasamos al controlador
            const paramsDTO = { "codigoSQL": this.usersData[idUsuario].codigo };

            axios.post('/usuarios/update/loadingUser',paramsDTO)
            .then(request => {
                //Verificamos que la data de respuesta no este vacia
                if(request.status === 200 && request.data === '') throw request;
                //Redireccionamos
                window.location.href = "/usuarios/update"
            })
            .catch(error => {
                console.error(error);
            })
         },
        permisosUsuarios(idUsuario){ console.log(this.usersData[idUsuario]) },
        crearUsuario(){ window.location.href = "/usuarios/create" }

    },
    computed:{},
    watch:{
        //Si carga los usuarios desactivamos el login
        usersData(){ this.isMounted = true; } //Desactivamos el loading
    },
    components: { FontAwesome, Loading, ListingCrud }
});

if(document.getElementById('section-users') !== null)
{
    usersApp.mount('#section-users');
    window.location.hash = "#01";
};
