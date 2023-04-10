import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';
import { Exceptions } from '../Excepciones/Excepciones';
import { UsersControl } from '../Models/UserModel';
import { AXIOSINTERVAL } from '../app';

const loginApp = createApp ({
    data(){
        return {
            controlEye: 'fa-solid fa-eye',
            TypeInputPassword: 'password',
            showPassword: false, //Controla el view del password,
            isDisable: false, //Controla el estado del boton
            isClick: false, //Detecta si se hizo click
            codigoUsuario: { value: "", IsEmpty: false }, //Codigo del usuario
            passwordUsuario: { value: "", IsEmpty: false }, //Contraseña del usuario
            ErrorMessage: { codigoError: "", passwordError: "" }, //Manejo de errores
            ErrorStyle: {
                base: true, //Definimos la propiedad que colocara input-group
                HasError: { codigo: false, password: false } //Objeto que controla en que formulario colocara el mb-3
            },
            checkCodigo: { regex: new RegExp('^([0-9]{0,6})$'),oldValue:""}, //Verifica si el valor cumple con el siguiente formato
            controlLogin:
            {
                status: false,
                message: "",
                classMessage: "" //Estilo del mensaje
            }
        }
    },
    methods:{
        /**
         * Metodo que controla el cambio en la información de la contraseña
         * @param {*} nuevoEstado Estado actual del boton
         */
        changeInput(nuevoEstado){
            this.showPassword === true
            ? (this.controlEye = 'fa-solid fa-eye-slash', this.TypeInputPassword = 'text')
            : (this.controlEye = 'fa-solid fa-eye', this.TypeInputPassword = 'password')
            this.showPassword = nuevoEstado;
        },
        /**
         * MEtodo que controla el inicio de sesion
         * @param {*} $EncryptKey Contexto o llave del encrypt. String
         * @param {*} $EncryptIv Vector inicializador del encrypt. String
         */
        iniciarSesion($EncryptKey, $EncryptIv){

            //Validamos que los campos no estén vacios
            try {
                switch (true) {
                    case this.codigoUsuario.value == "" && this.passwordUsuario.value != "": //Codigo
                        //Cambiamos el estado de los campos
                        this.codigoUsuario.IsEmpty = true;
                        this.passwordUsuario.IsEmpty = false;
                        throw 'NoCodigo';
                    case this.codigoUsuario.value != "" && this.passwordUsuario.value == "": //Password Vacia
                        this.passwordUsuario.IsEmpty = true;
                        this.codigoUsuario.IsEmpty = false;
                        throw 'NoPassword';
                    case this.passwordUsuario.value == "" && this.codigoUsuario.value == "": //Ambos Vacios
                        this.codigoUsuario.IsEmpty = true;
                        this.passwordUsuario.IsEmpty = true;
                        throw 'EmptyData';
                    default:
                        this.codigoUsuario.IsEmpty = false;
                        this.passwordUsuario.IsEmpty = false;
                        this.isDisable = true //Apagamos el boton
                        this.isClick = true //Cambiamos la información
                        //Despues de reiniciar el estado del Empty, llamamos al metodo Login
                        this.login(this.passwordUsuario.value,this.codigoUsuario.value,$EncryptKey,$EncryptIv)
                        break;
                }
            } catch (error) {
                //Muestra el error
                this.ErrorMessage = Exceptions.CatchWarning(error);
            }
        },
        /**
         * Metodo enfocado en iniciar sesión
         * @param {*} $Password Almacena la password del input
         * @param {*} $User Almacena el Usuario del input
         * @param {*} $KeyEncriptada Contexto del encript
         * @param {*} $IvEncriptada Vector del encript
         */
        login($Password, $User, $KeyEncriptada, $IvEncriptada){
            const ObjectToEncript =
            {
                "codigo": $User,
                "password": $Password,
                "encryptKey": $KeyEncriptada,
                "encryptIv": $IvEncriptada
            }
            //Encriptamos la data antes de llamarla por Axios
            const ParamsLogin = UsersControl.EncriptarDatos(ObjectToEncript);
            axios.post('/login',ParamsLogin)
            .then(request => {
                if(!request.data.response && request.status === 200) throw request.data.message;
                //Si no cumple la condición, iniciamos sesión
                this.controlLogin =
                {
                    status: true,
                    message: request.data.message,
                    classMessage: "form-SuccessInput"
                }
                //Redireccionamos
                setTimeout(() => {
                    window.location.href = '/'
                }, AXIOSINTERVAL);
            })
            .catch(error => {
                this.controlLogin =
                {
                    status: true,
                    message: "El usuario o la contraseña son incorrectos",
                    classMessage: "form-ErrorInput"
                }
                console.error(error);
                this.isDisable = false;
                this.isClick = false;
                //Capturamos el error y volvemos a activar el boton
            })
        },
        /**
         * Metodo que captura el valor del codigo
         * @param {*} inputEvent Evento de input Object
         * @returns vacio. Solo retorna si falla el regex
         */
        verifyCode(inputEvent)
        {
            const newValue = inputEvent.target.value;
            //Si existe el error lo capturamos
            if(!this.checkCodigo.regex.test(newValue))
            {
                //Devolvemos el valor anterior
                this.codigoUsuario.value = this.checkCodigo.oldValue;
                return;
            }
            //Caso contrario lo almacenamos como viejo valor y quitamos el estilado del error si está activo
            this.checkCodigo.oldValue = newValue;
            //En caso de que este activado el error. Lo desactivamos
            this.codigoUsuario.IsEmpty = false;
            this.controlLogin.status = false;
        },
        /**
         * Metodo que desactiva los errores del password y Login
         */
        verifyPassword(){this.passwordUsuario.IsEmpty = false; this.controlLogin.status = false;}
    },
    computed:{},
    watch:{
        //Hacemos un watch a los objetos
        codigoUsuario:{
            deep:true,
            handler(estadoCodigo){
                //Condicion para activar los estilos del mensaje de error
                estadoCodigo.IsEmpty === true ? this.ErrorStyle.HasError.codigo = true : this.ErrorStyle.HasError.codigo = false;
            }
        },
        //Password
        passwordUsuario:{
            deep:true,
            handler(estadoPassword){
                estadoPassword.IsEmpty === true ? this.ErrorStyle.HasError.password = true : this.ErrorStyle.HasError.password = false;
            }
        }
    },
    components: { FontAwesome }
});
if(document.getElementById("app-login") !== null) loginApp.mount('#app-login');
