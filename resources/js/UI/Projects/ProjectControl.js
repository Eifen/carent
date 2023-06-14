import { createApp } from 'vue/dist/vue.esm-bundler';
import FormProjects from '../../Components/UiComponents/Projects/FormProjects.vue'
import { componentsUI, methodsUI,CrudUi } from '../UIConfig';
import { Validate } from '../../Models/ValidateModel';

const clientsControl = createApp ({
    data(){
        return {
            isMounted: false, //Controla el estado del componente
            isClick: false, //Controla el estado del boton
            updateModel: {}, //Objeto encargado de inicializar la data del edit
            paramsDTOProjects:
            {
                "projectDescription": '',
                "clientId": 0,
                "statusId": 0,
                "managerId": 0,
                "partnerId": 0,
                "qualityPartnerId": 0,
                "currencyId": 0,
                "companyId": 0,
                "hiringDate": '',
                "departments": [],
                "projectValue": 0
            }, //Objeto para el create
            paramsDTOEdit:
            {
                "IdClient":0,
                "IdStatus":0
            } //Objeto para el edit
        }
    },
    methods:{
        /**
         * Metodo que crea un nuevo cliente
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        newProject(dataParams){
            // this.isClick = true;
            this.paramsDTOProjects = dataParams
            // //AXIOS Create Project
            const paramsToPost = {
                "project": JSON.parse(JSON.stringify(this.paramsDTOProjects)),
                "isEdit": false
            }
            const routesSelfDTO = { "post": "/projects/create/newProject", "redirect": "/projects", "self":this }
            CrudUi.controlCrud(routesSelfDTO,paramsToPost)
        },
        /**
         * Actualiza un cliente
         * @param {Object} dataParams Objeto con la información a actualizar
         */
        updateClient(dataParams){
            this.isClick = true;
            //Pasamos la data clientes
            this.paramsDTOClients =
            {
                "IdSocio": dataParams.IdSocio,
                "IdSector": dataParams.IdSector,
                "IdServicio": dataParams.IdServicio,
                "IdPais": dataParams.IdPais,
                "Nit": dataParams.Nit,
                "Rif": dataParams.Rif,
                "Telefono": dataParams.Telefono,
                "RazonSocial": dataParams.RazonSocial,
                "Direccion":dataParams.Direccion,
                "EmailFiscal": dataParams.EmailFiscal,
                "PaginaWeb": dataParams.PaginaWeb
            }
            this.validateNivel2(dataParams);

            //Verificamos el status
            dataParams.Status == 0
            ? this.paramsDTOEdit.IdStatus = null
            : this.paramsDTOEdit.IdStatus = dataParams.Status

            this.paramsDTOEdit.IdClient = this.updateModel.client_id

            //Una vez haya terminado de verificar validateNivel2
            this.paramsDTOEdit = { ...this.paramsDTOEdit, ...this.paramsDTOClients }

            //AXIOS Update Client
            const paramsToPost = {
                "client": JSON.parse(JSON.stringify(this.paramsDTOEdit)),
                "isEdit": true
            }
            const routesSelfDTO = { "post": "/clientes/update/updateClient", "redirect": "/clientes", "self":this }
            CrudUi.controlCrud(routesSelfDTO,paramsToPost)
        },
        /**
         * Espacio de validaciones para campos no obligatorios
         * @param {*} dataToValidate Se importa todo el array de datos
         */
        validateNivel2(dataToValidate)
        {
            const DTONit = Validate.Number(dataToValidate.Nit);
            const DTOWeb = Validate.String(dataToValidate.PaginaWeb,100)

            //Descomponemos el Nit
            !DTONit.response || (DTONit.response && dataToValidate.Nit.length > 11)
            ? this.paramsDTOClients.Nit = 0
            : this.paramsDTOClients.Nit = parseInt(dataToValidate.Nit);

            //Descomponemos la página Web
            !DTOWeb.response
            ? this.paramsDTOClients.PaginaWeb = ''
            : this.paramsDTOClients.PaginaWeb = dataToValidate.PaginaWeb;
        }
    },
    components: { FormProjects },
    mounted(){
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    mixins: [ componentsUI,methodsUI ]
});

if(document.getElementById('create-project') !== null || document.getElementById('update-project') !== null)
{
    if(document.getElementById('create-project') !== null) clientsControl.mount('#create-project');
    if(document.getElementById('update-project') !== null) clientsControl.mount('#update-project');
    window.location.hash = "#03";
}