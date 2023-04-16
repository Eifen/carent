import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../../Components/FontAwesome/FontAwesome.vue';
import Loading from '../../Components/Loading.vue';
import FormUsers from '../../Components/Users/FormUsers.vue';
import Calendar from '../../Components/Calendar.vue';
import { UsersControl } from '../../Models/UserModel';
import axios from "axios";
import { Validate } from '../../Models/ValidateModel';
import { AXIOSINTERVAL, NOTIFYINTERVAL } from '../../app';

//Toastify
import { toast } from 'vue3-toastify';

const createUser = createApp({
    data(){
        return{
            isMounted: false,
            encryptKey: '', //Llave del encrypt
            encryptIv: '', //Vector del encrypt
            isCreateClick: false,
            paramDTOCreate:
            {
                "FirstName": '',
                "SecondName": '',
                "LastName": '',
                "SecondLastName": '',
                "Cedula": '',
                "Birthday": '',
                "Code": '',
                "IdParish": 0,
                "IdCargo": 0,
                "IdDivision": 0,
                "DateIngreso": ''
            }, //Objeto que prepara la data para crear el usuario
            paramDTONewContact:
            {
                "FirstEmail": '',
                "SecondEmail": '',
                "FirstPhone": '',
                "SecondPhone": ''
            }, //Objeto que prepara la data para insertar el contacto del nuevo Usuario
            paramDTONewDocument:
            {
                "TipoCedula": '',
                "Cedula": ''
            } //Objeto que prepara la data para insertar los datos del documento
        }
    },
    mounted(){
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    methods:{
        /**
         * Metodo que redirecciona a la pantalla anterior
         */
        redirectView(){ window.location.href = "/usuarios" },
        /**
         * Metodo que registra un nuevo usuario
         * @param {*} Data Almacena la data inicial del usuario
         */
        newUser(Data){
            const DTOCedula = Data.Cedula.split('-'); //[0] = Tipo, [1] = Cedula
            const DTONumeroSecundario = Data.SecondPhone;
            const DTOEmailSecundario = Data.SecondEmail;
            const DTOSecondName = Data.SecondName;
            const DTOSecondLastName = Data.SecondLastName;
            const DTOFechaIngreso = Data.DateIngreso;
            const DTOCode = UsersControl.EncriptarDatos(
                {
                    "encryptKey": this.encryptKey,
                    "encryptIv": this.encryptIv,
                    "codigo": Data.Code,
                    "password": '000000'
                })
            //Segundo nivel de validaciones
            Data.IdParish == 0 ? this.paramDTOCreate.IdParish = null : this.paramDTOCreate.IdParish = Data.IdParish
            Data.IdCargo == 0 ? this.paramDTOCreate.IdCargo = null : this.paramDTOCreate.IdCargo = Data.IdCargo
            Data.IdDivision == 0 ? this.paramDTOCreate.IdDivision = null : this.paramDTOCreate.IdDivision = Data.IdDivision

            //Descomponer cedula
            this.paramDTOCreate.Cedula = DTOCedula[1].replace(/,/g,'');
            this.paramDTONewDocument.Cedula = this.paramDTOCreate.Cedula;
            this.paramDTONewDocument.TipoCedula = DTOCedula[0];

            //Descomponer numero
            !Validate.Phone(DTONumeroSecundario).response
            ? this.paramDTONewContact.SecondPhone = ''
            : this.paramDTONewContact.SecondPhone = this.decryptPhone(DTONumeroSecundario);

            this.paramDTONewContact.FirstPhone = this.decryptPhone(Data.FirstPhone);

            //Descomponer Email
            !Validate.Email(DTOEmailSecundario).response
            ? this.paramDTONewContact.SecondEmail = ''
            : this.paramDTONewContact.SecondEmail = DTOEmailSecundario;

            this.paramDTONewContact.FirstEmail = Data.FirstEmail;

            //Descomponer Nombres
            !Validate.String(DTOSecondName,20).response
            ? this.paramDTOCreate.SecondName = ''
            : this.paramDTOCreate.SecondName = DTOSecondName;

            !Validate.String(DTOSecondLastName,20).response
            ? this.paramDTOCreate.SecondLastName = ''
            : this.paramDTOCreate.SecondLastName = DTOSecondLastName;

            this.paramDTOCreate.FirstName = Data.FirstName;
            this.paramDTOCreate.LastName = Data.LastName;

            //Descomponer Fechas
            !Validate.Date(DTOFechaIngreso).response
            ? this.paramDTOCreate.DateIngreso = ''
            : this.paramDTOCreate.DateIngreso = Data.DateIngreso;

            this.paramDTOCreate.Birthday = Data.Birthday

            //Descomponer Codigo
            this.paramDTOCreate.Code = `${DTOCode.Codigo}`

            //Solicitamos al controlador
            axios.post('/usuarios/create/newUser',{
                "user": JSON.parse(JSON.stringify(this.paramDTOCreate)),
                "contact": JSON.parse(JSON.stringify(this.paramDTONewContact)),
                "document": JSON.parse(JSON.stringify(this.paramDTONewDocument))})
            .then(request => {
                if(request.status === 200 && !request.data.response) throw request.data.message;
                toast.success(request.data.message, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose: false
                });

                setTimeout(() => {
                    window.location.href = "/usuarios";
                }, AXIOSINTERVAL + 200);

            }).catch(error => {
                toast.error(error, {
                    position: toast.POSITION.TOP_LEFT,
                    autoClose:NOTIFYINTERVAL
                });
            })
        },
        /**
         * Reformatea el telefono quitando los caracteres "()" y "-"
         * @param {*} phoneValue Captura el valor actual del telefono
         * @return String que contiene el nuevo formato del telefono
         */
        decryptPhone(phoneValue){ return phoneValue.replace('(','').replace(')','').replace(/-/g,''); },
        /**
         * Recibe la data de encriptacion de la base de datos y lo asigna  a dos variables en Data
         * @param {*} encryptKey Llave del encrypt
         * @param {*} encryptIv Vector del encrypt
         */
        getEncrypt(encryptKey, encryptIv)
        {
            this.encryptKey = encryptKey;
            this.encryptIv = encryptIv;
        }
    },
    components: { FontAwesome, Loading, FormUsers, Calendar }
});

if(document.getElementById('create-users') !== null)
{
    createUser.mount('#create-users');
    window.location.hash = "#01";
};
