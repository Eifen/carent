import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import Loading from '../../Components/Loading.vue';
import FormClients from '../../Components/Clients/FormClients.vue';
import { Validate } from '../../Models/ValidateModel';
import axios from 'axios';
import { AXIOSINTERVAL, NOTIFYINTERVAL } from '../../app';

//Toastify
import { toast } from 'vue3-toastify';

const clientsControl = createApp ({
    data(){
        return {
            isMounted: false, //Controla el estado del componente
            isClick: false, //Controla el estado del boton
            updateModel: {}, //Objeto encargado de inicializar la data del edit
            paramsDTOClients:
            {
                "IdSocio": 0,
                "IdSector": 0,
                "IdServicio": 0,
                "IdPais": 0,
                "Nit": 0,
                "Rif": '',
                "Telefono": '',
                "RazonSocial": '',
                "Direccion":'',
                "EmailFiscal": '',
                "PaginaWeb": '',
            }, //Objeto para el create
            paramsDTOEdit:
            {
                "IdClient":0,
                "IdStatus":0
            } //Objeto para el edit
        }
    },
    methods:{
        /** Metodo que retorna a la página principal de clientes */
        returnClients(){ window.location.href = "/clientes" },
        /**
         * Metodo que prepara la data a actualizar de clientes
         * @param {*} clientDTO Obtiene los parametros de la sesion de clientes temporal antes de eliminarla
         */
        prepareUpdate(clientDTO){
            this.updateModel = clientDTO
            //Una vez asignado, eliminamos la sesion
            axios.put('/clientes/deleteUpdateData')
            .then(request =>{})
            .catch(error => { console.error(error) });
        },
        /**
         * Metodo que crea un nuevo cliente
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        newClient(dataParams){
            this.isClick = true;
            this.paramsDTOClients = dataParams
            //Validacion de campos opcionales
            this.validateNivel2(dataParams)
            axios.post('/clientes/create/newClient',{
                "client": JSON.parse(JSON.stringify(this.paramsDTOClients)),
                "isEdit": false})
            .then(request => {
                if(request.status === 200 && !request.data.response) throw request.data.message;
                //Pasamos las validaciones
                toast.success(request.data.message, {
                    position:toast.POSITION.TOP_LEFT,
                    autoClose:true
                })

                //Redirigimos
                setTimeout(() => {
                    window.location.href = "/clientes";
                }, AXIOSINTERVAL + 200);
            })
            .catch(error => {
                toast.error(error, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose: NOTIFYINTERVAL
                })

                //Desactivamos el boton
                this.isClick = false;
            })
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

            this.paramsDTOEdit.IdClient = this.updateModel.Id

            //Una vez haya terminado de verificar validateNivel2
            this.paramsDTOEdit = { ...this.paramsDTOEdit, ...this.paramsDTOClients }

            //Conexion axios post update
            axios.post('/clientes/update/updateClient',{
                "client": JSON.parse(JSON.stringify(this.paramsDTOEdit)),
                "isEdit": true})
            .then(request => {
                if(request.status === 200 && !request.data.response) throw request.data.message;
                //Pasamos las validaciones
                toast.success(request.data.message, {
                    position:toast.POSITION.TOP_LEFT,
                    autoClose:true
                })

                //Redirigimos
                setTimeout(() => {
                    window.location.href = "/clientes";
                }, AXIOSINTERVAL + 200);
            })
            .catch(error => {
                toast.error(error, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose: NOTIFYINTERVAL
                })

                console.error(error)
                //Desactivamos el boton
                this.isClick = false;
            })
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
    computed:{},
    watch:{},
    mounted(){
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    components: { FontAwesome, Loading, FormClients }
});

if(document.getElementById('create-client') !== null || document.getElementById('update-client') !== null)
{
    if(document.getElementById('create-client') !== null) clientsControl.mount('#create-client');
    if(document.getElementById('update-client') !== null) clientsControl.mount('#update-client');
    window.location.hash = "#02";
}
