import { createApp } from "vue/dist/vue.esm-bundler";
import FontAwesome from "../Components/FontAwesome/FontAwesome.vue";
import PasswordControl from "../Components/PasswordControl.vue";
import { UsersControl } from "../Models/UserModel";
import { CrudUi } from "./UIConfig";

const changeApp = createApp({
    data() {
        return {
            isClick: false, //Controla el estado del boton
        };
    },
    methods: {
        /**
         * Metodo que cambia la contrasena
         * @param {Object} fields Recibe los parametros de oldPassword y newPassword respectivamente en un json
         * @param {*} key llave de la encriptacion
         * @param {*} iv vector de la encriptacion
         */
        changePassword(fields, key, iv) {
            this.isClick = true;
            const ObjectToEncript = {
                codigo: fields.oldPassword,
                password: fields.newPassword,
                encryptKey: key,
                encryptIv: iv,
            };
            //Encriptamos la data antes de llamarla por Axios
            const ParamsLogin = UsersControl.EncriptarDatos(ObjectToEncript);
            console.log(ParamsLogin);
            //Preparamos la informacion para axios
            CrudUi.controlCrud(
                { post: "/account/update-password", redirect: "/", self: this },
                ParamsLogin
            );
        },
    },
    computed: {},
    components: { FontAwesome, PasswordControl },
    watch: {},
});

if (document.getElementById("config-password") !== null) {
    changeApp.mount("#config-password");
    window.location.hash = "#06";
}
