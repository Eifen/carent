import { createApp } from 'vue/dist/vue.esm-bundler';
import { componentsUI, methodsUI, watchUI, CrudUi, dataUI } from '../UIConfig';

const clientsIndex = createApp ({
    data(){
        return {
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
            tableTarget: 'clients',
        }
    },
    created()
    {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this,this.tableTarget,this.lengthColumns)
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
    mixins: [ componentsUI, methodsUI, watchUI, dataUI ]
});

if(document.getElementById('section-clients') !== null)
{
    clientsIndex.mount('#section-clients');
    window.location.hash = "#02";
}
