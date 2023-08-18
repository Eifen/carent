<template>
    <div class="control-password">
        <h2 class="control-password-title">{{ title }}</h2>
        <!-- code de usuario -->
        <div v-if="forgotPassword" class="control-password-code"
            :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.code }">
            <span class="input-group-text" id="basic-addon1">
                <font-awesome string-icon="fa-solid fa-user"></font-awesome>
            </span>
            <input type="text" class="form-control" placeholder="Código de Usuario" aria-label="code"
                aria-describedby="basic-addon1" id="codeUser" v-model="codeUser.value" @input="verifyCode" />
        </div>
        <!-- Control de errores de code de User -->
        <span class="control-password-error-code form-ErrorInput" v-if="codeUser.IsEmpty">
            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
            {{ ErrorMessage.codeError }}
        </span>
        <!-- Clave actual del User -->
        <div v-if="!forgotPassword" :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.oldPassword }">
            <span class="input-group-text" id="basic-addon3">
                <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
            </span>
            <input :type="TypeInputOldPassword" class="form-control" placeholder="Contraseña actual" aria-label="Clave"
                aria-describedby="basic-addon2" id="oldPasswordUser" v-model="oldPasswordUser.value"
                @input="verifyPassword()" />
            <span class="input-group-text form-eye" id="basic-addon4" @click="changeInput('old')">
                <!-- Control del ojo -->
                <font-awesome :string-icon="controlOldEye"></font-awesome>
            </span>
        </div>
        <!-- Control de errores de Contraseña de usuario -->
        <span class="form-ErrorInput" v-if="oldPasswordUser.IsEmpty">
            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
            {{ ErrorMessage.oldPasswordError }}
        </span>
        <!-- Clave nueva del User -->
        <div v-if="!forgotPassword" :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.newPassword }">
            <span class="input-group-text" id="basic-addon6">
                <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
            </span>
            <input :type="TypeInputNewPassword" class="form-control" placeholder="Contraseña nueva" aria-label="Clave"
                aria-describedby="basic-addon5" id="newPasswordUser" v-model="newPasswordUser.value"
                @input="verifyPassword()" />
            <span class="input-group-text form-eye" id="basic-addon7" @click="changeInput('new')">
                <!-- Control del ojo -->
                <font-awesome :string-icon="controlNewEye"></font-awesome>
            </span>
            <!-- Control de errores de Contraseña de usuario -->
        </div>
        <span class="form-ErrorInput" v-if="newPasswordUser.IsEmpty">
            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
            {{ ErrorMessage.newPasswordError }}
        </span>
        <!-- Clave confirmar nueva del User -->
        <div v-if="!forgotPassword"
            :class="{ 'input-group': ErrorStyle.base, 'mb-3': !ErrorStyle.HasError.confirmNewPassword }">
            <span class="input-group-text" id="basic-addon9">
                <font-awesome string-icon="fa-solid fa-lock"></font-awesome>
            </span>
            <input :type="TypeInputConfirmPassword" class="form-control" placeholder="Confirmar contraseña nueva"
                aria-label="Clave" aria-describedby="basic-addon8" id="confirmNewPassword"
                v-model="confirmNewPassword.value" @input="verifyPassword()" />
            <span class="input-group-text form-eye" id="basic-addon10" @click="changeInput('confirm')">
                <!-- Control del ojo -->
                <font-awesome :string-icon="controlConfirmEye"></font-awesome>
            </span>
        </div>
        <!-- Control de errores de Contraseña de usuario -->
        <span class="form-ErrorInput" v-if="confirmNewPassword.IsEmpty">
            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
            {{ ErrorMessage.confirmNewPasswordError }}
        </span>
        <!-- {{-- Envio de datos del Formulario --}} -->
        <button type="button" class="control-password-submit" :class="isClick ? 'disable' : null" @click="verifyValue()">
            <span v-if="!isClick">{{ buttonTitle }}</span>
            <span v-if="isClick">
                <font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome>
            </span>
        </button>
    </div>
</template>
<script>
//Fontawesome
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import { Exceptions } from "@/Excepciones/Excepciones";
export default {
    props: {
        title: String, //Define el titulo
        forgotPassword: Boolean, //Muestra el code de usuario unicamente
        buttonTitle: String, //Titulo del boton
        isClick: Boolean, //Control de click del boton
    },
    emits: ["changePassword", "recoveryPassword"],
    data() {
        return {
            controlOldEye: 'fa-solid fa-eye',
            controlNewEye: 'fa-solid fa-eye',
            controlConfirmEye: 'fa-solid fa-eye',
            TypeInputOldPassword: 'password',
            TypeInputNewPassword: 'password',
            TypeInputConfirmPassword: 'password',
            showOldPassword: false, //Controla el view del old password,
            showNewPassword: false, //Controla el view del new password,
            showConfirmPassword: false, //Controla el view del confirm password,
            codeUser: { value: "", IsEmpty: false }, //code del usuario
            oldPasswordUser: { value: "", IsEmpty: false }, //Contraseña del usuario
            newPasswordUser: { value: "", IsEmpty: false }, //Contraseña nueva del usuario
            confirmNewPassword: { value: "", IsEmpty: false }, //Confirmar contrasena nueva
            ErrorMessage: { codeError: "", oldPasswordError: "", newPasswordError: "", confirmNewPasswordError: "" }, //Manejo de errores
            ErrorStyle: {
                base: true, //Definimos la propiedad que colocara input-group
                HasError: { code: false, oldPassword: false, newPassword: false, confirmNewPassword: false } //Objeto que controla en que formulario colocara el mb-3
            },
            checkcode: { regex: new RegExp('^([0-9]{0,6})$'), oldValue: "" }, //Verifica si el valor cumple con el siguiente formato
        }
    },
    methods: {
        changeInput(type) {
            switch (type) {
                case 'old':
                    this.showOldPassword = !this.showOldPassword;
                    break;
                case 'new':
                    this.showNewPassword = !this.showNewPassword;
                    break;
                case 'confirm':
                    this.showConfirmPassword = !this.showConfirmPassword;
                    break;
            }
        },
        /**
         * Metodo que verifica que los campos tengan la informacion correcta
         */
        verifyValue() {
            //Validamos que los campos no estén vacios
            if (this.forgotPassword) {
                this.verifyForgot();
            } else {
                this.verifyChange();
            }
        },
        //Verificacion para olvidar contraseña
        verifyForgot() {
            if (this.codeUser.value == "") {
                this.codeUser.IsEmpty = true;
                this.ErrorMessage = Exceptions.CatchWarning("NoRecovery");
            } else {
                this.codeUser.IsEmpty = false;
                this.$emit("recoveryPassword", this.codeUser.value)
            }
        },
        //Verificacion para cambiar el password
        verifyChange() {
            try {
                switch (true) {
                    case this.oldPasswordUser.value == "" && this.newPasswordUser.value != "": //Password actual
                        //Cambiamos el estado de los campos
                        this.oldPasswordUser.IsEmpty = true;
                        this.newPasswordUser.IsEmpty = false;
                        this.confirmNewPassword.IsEmpty = false;
                        throw 'NoActualPassword';
                    case this.oldPasswordUser.value != "" && this.newPasswordUser.value == "": //Password nueva
                        this.oldPasswordUser.IsEmpty = false;
                        this.newPasswordUser.IsEmpty = true;
                        this.confirmNewPassword.IsEmpty = false;
                        throw 'NoNewPassword';
                    case this.newPasswordUser.value != this.confirmNewPassword.value: //Password no coincide
                        this.oldPasswordUser.IsEmpty = false;
                        this.newPasswordUser.IsEmpty = false;
                        this.confirmNewPassword.IsEmpty = true;
                        throw 'NoMatchPassword';
                    case this.oldPasswordUser.value == "" && this.newPasswordUser.value == "": //Todos Vacios
                        this.oldPasswordUser.IsEmpty = true;
                        this.newPasswordUser.IsEmpty = true;
                        this.confirmNewPassword.IsEmpty = false;
                        throw 'EmptyPassword';
                    default:
                        this.oldPasswordUser.IsEmpty = false;
                        this.newPasswordUser.IsEmpty = false;
                        this.confirmNewPassword.IsEmpty = false;
                        //Si no tiene validaciones producimos el emit
                        this.$emit("changePassword", { "oldPassword": this.oldPasswordUser.value, "newPassword": this.newPasswordUser.value })
                        break;
                }
            } catch (error) {
                //Muestra el error
                this.ErrorMessage = Exceptions.CatchWarning(error);
            }
        },
        /**
         * Metodo que captura el valor del code
         * @param {*} inputEvent Evento de input Object
         * @returns vacio. Solo retorna si falla el regex
         */
        verifyCode(inputEvent) {
            const newValue = inputEvent.target.value;
            //Si existe el error lo capturamos
            if (!this.checkcode.regex.test(newValue)) {
                //Devolvemos el valor anterior
                this.codeUser.value = this.checkcode.oldValue;
                return;
            }
            //Caso contrario lo almacenamos como viejo valor y quitamos el estilado del error si está activo
            this.checkcode.oldValue = newValue;
            //En caso de que este activado el error. Lo desactivamos
            this.codeUser.IsEmpty = false;
        },
        /**
         * Metodo que desactiva los errores del password y Login
         */
        verifyPassword() {
            this.oldPasswordUser.IsEmpty = false;
            this.newPasswordUser.IsEmpty = false;
            this.confirmNewPassword.IsEmpty = false;
        }
    },
    computed: {},
    watch: {
        //Hacemos un watch a los objetos
        codeUser: {
            deep: true,
            handler(estadocode) {
                //Condicion para activar los estilos del mensaje de error
                estadocode.IsEmpty === true ? this.ErrorStyle.HasError.code = true : this.ErrorStyle.HasError.code = false;
            }
        },
        //Password
        oldPasswordUser: {
            deep: true,
            handler(estadoPassword) {
                estadoPassword.IsEmpty === true ? this.ErrorStyle.HasError.oldPassword = true : this.ErrorStyle.HasError.oldPassword = false;
            }
        },
        newPasswordUser: {
            deep: true,
            handler(estadoPassword) {
                estadoPassword.IsEmpty === true ? this.ErrorStyle.HasError.newPassword = true : this.ErrorStyle.HasError.newPassword = false;
            }
        },
        confirmNewPassword: {
            deep: true,
            handler(estadoPassword) {
                estadoPassword.IsEmpty === true ? this.ErrorStyle.HasError.confirmNewPassword = true : this.ErrorStyle.HasError.confirmNewPassword = false;
            }
        },
        showOldPassword(newValue) {
            newValue === true
                ? (this.controlOldEye = 'fa-solid fa-eye-slash', this.TypeInputOldPassword = 'text')
                : (this.controlOldEye = 'fa-solid fa-eye', this.TypeInputOldPassword = 'password')
        },
        showNewPassword(newValue) {
            newValue === true
                ? (this.controlNewEye = 'fa-solid fa-eye-slash', this.TypeInputNewPassword = 'text')
                : (this.controlNewEye = 'fa-solid fa-eye', this.TypeInputNewPassword = 'password')
        },
        showConfirmPassword(newValue) {
            newValue === true
                ? (this.controlConfirmEye = 'fa-solid fa-eye-slash', this.TypeInputConfirmPassword = 'text')
                : (this.controlConfirmEye = 'fa-solid fa-eye', this.TypeInputConfirmPassword = 'password')
        }
    },
    components: { FontAwesome }
}
</script>
