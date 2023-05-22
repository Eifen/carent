import { createApp } from 'vue/dist/vue.esm-bundler';
import { componentsUI, methodsUI, watchUI, CrudUi } from '../UIConfig';

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
            listData: [] //Object que almacena la data de los usuarios a mostrar en la lista
        }
    },
    //Ante de montar, consultamos el tamaño máximo de la páginación
    created(){
        const paginationDTO = { "table": this.tableTarget, "lengthPage": this.lengthColumns }
        const routeDTO = { "route": '/usuarios/limitPag', "self": this }
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(routeDTO,paginationDTO)
    },
    mounted(){ CrudUi.getTable('/usuarios/allUsers',this) },
    methods:{
        //Metodo dedicados a las configuraciones de la tabla
        editUsuarios(idUsuario){
            //Seccionamos el ID y luego lo pasamos al controlador
            const paramsDTO = { "codigoSQL": this.listData[idUsuario].codigo };
            const routesDTO = { "post": '/usuarios/update/loadingUser', "redirect": "/usuarios/update"}
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
        permisosUsuarios(idUsuario){ console.log(this.listData[idUsuario]) },
        crearUsuario(){ window.location.href = "/usuarios/create" }

    },
    watch:{
        //Si carga los usuarios desactivamos el login
        listData(){ this.isMounted = true; } //Desactivamos el loading
    },
    mixins: [ componentsUI, methodsUI, watchUI ]
});

if(document.getElementById('section-users') !== null)
{
    usersApp.mount('#section-users');
    window.location.hash = "#01";
};
