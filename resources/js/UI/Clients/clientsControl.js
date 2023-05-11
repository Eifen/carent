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
                "Status":0
            } //Objeto para el edit
        }
    },
    methods:{
        /** Metodo que retorna a la página principal de clientes */
        returnClients(){ window.location.href = "/clientes" },
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
            : this.paramsDTOClients.Nit = dataToValidate.Nit;

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

if(document.getElementById('create-client') !== null)
{
    if(document.getElementById('create-client') !== null) clientsControl.mount('#create-client');
    window.location.hash = "#02";
}
