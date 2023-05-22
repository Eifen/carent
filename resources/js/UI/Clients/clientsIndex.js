import { createApp } from 'vue/dist/vue.esm-bundler';
import { componentsUI, methodsUI, watchUI, CrudUi } from '../UIConfig';

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
            listData: [], //Object que almacena la data de los clientes a mostrar en la lista
            lengthColumns: 50, //Tamaño máximo de registros por página
            maxLengthPagination: 0, //Define el tamaño total de páginas
            tableTarget: 'clients',
        }
    },
    created()
    {
        const paginationDTO = { "table": this.tableTarget, "lengthPage": this.lengthColumns }
        const routeDTO = { "route": '/clientes/limitPag', "self": this }
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(routeDTO,paginationDTO)
    },
    mounted(){ CrudUi.getTable('/clientes/allClients',this) },
    methods:{
        /**
         * Metodo que crea una instancia en Sessión para pasarla temporalmente a un formulario de actualización
         * @param {*} idClient Almacena la ID seleccionada de la lista de clientes
         */
        editClient(idClient){
            const paramsDTO = { "codigoSQL": this.listData[idClient].codigo }
            const routesDTO = { "post": '/clientes/update/loadingClient', "redirect": "/clientes/update"}
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
        createClient(){ window.location.href = "/clientes/create" }
    },
    mixins: [ componentsUI, methodsUI, watchUI ]
});

if(document.getElementById('section-clients') !== null)
{
    clientsIndex.mount('#section-clients');
    window.location.hash = "#02";
}
