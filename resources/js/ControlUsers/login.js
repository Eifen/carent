import { createApp } from 'vue/dist/vue.esm-bundler';
import FontAwesome from '../Components/FontAwesome/FontAwesome.vue';
import { Exceptions } from '../Excepciones/Excepciones';
import { UsersControl } from '../Models/UserModel';

const loginApp = createApp ({
    data(){
        return {
            controlEye: 'fa-solid fa-eye',
            TypeInputPassword: 'password',
            state: false, //Estado de la vista. Por defecto esta en false,
            codigoUsuario: { value: "", IsEmpty: false }, //Codigo del usuario
            passwordUsuario: { value: "", IsEmpty: false }, //Contraseña del usuario
            ErrorMessage: { codigoError: "", passwordError: "" }, //Manejo de errores
            ErrorStyle: {
                base: true, //Definimos la propiedad que colocara input-group
                HasError: { codigo: false, password: false } //Objeto que controla en que formulario colocara el mb-3
            }
        }
    },
    methods:{
        /**
         * Metodo que controla el cambio en la información de la contraseña
         * @param {*} nuevoEstado Estado actual del boton
         */
        changeInput(nuevoEstado){
            this.state === true
            ? (this.controlEye = 'fa-solid fa-eye-slash', this.TypeInputPassword = 'text')
            : (this.controlEye = 'fa-solid fa-eye', this.TypeInputPassword = 'password')
            this.state = nuevoEstado;
        },
        /**
         * MEtodo que controla el inicio de sesion
         * @param {*} $EncryptKey Contexto o llave del encrypt. String
         * @param {*} $EncryptIv Vector inicializador del encrypt. String
         */
        iniciarSesion($EncryptKey, $EncryptIv){
            console.log($EncryptKey,$EncryptIv)
            const InitControlLogin = new UsersControl(); //Iniciamos la clase

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

                        //Despues de reiniciar el estado del Empty, llamamos al metodo Login
                        InitControlLogin.login(this.passwordUsuario.value,this.codigoUsuario.value);
                        break;
                }
            } catch (error) {
                //Muestra el error
                this.ErrorMessage = Exceptions.CatchException(error);
            }
        }
    },
    computed:{},
    watch:{
        //Hacemos un watch a los objetos
        codigoUsuario:{
            deep:true,
            handler(EstadoCodigo){
                //Condicion para activar los estilos del mensaje de error
                EstadoCodigo.IsEmpty === true ? this.ErrorStyle.HasError.codigo = true : this.ErrorStyle.HasError.codigo = false;
            }
        },
        //Password
        passwordUsuario:{
            deep:true,
            handler(EstadoPassword){
                EstadoPassword.IsEmpty === true ? this.ErrorStyle.HasError.password = true : this.ErrorStyle.HasError.password = false;
            }
        }
    },
    components: { FontAwesome }
});
loginApp.mount('#app-login');
