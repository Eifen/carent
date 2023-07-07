<template inline-template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">Regresar</div>
            <!-- Data Principal -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit: messages.titleCreate"></legend>
            <div :class="formClass.requiredTitle">Campos Obligatorios (<span :class="formClass.requiredField">*</span>)</div>
            <data-principal :scope="DTOData"
            @active-document="enableInput"
            @active-birthday="insertDate"
            :enable-edit="isEdit"></data-principal>
            <!-- Datos de contacto -->
            <legend :class="formClass.legend" v-text="messages.contacto"></legend>
            <data-contact :scope="DTOData" :enableEdit="isEdit"></data-contact>
            <!-- Datos para el empleado -->
            <legend :class="formClass.legend" v-text="messages.empleado"></legend>
            <!-- Check Empleado Crowe -->
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="" id="isCroweCheck"
                v-model="inputCheckedCrowe">
                <label class="form-check-label" for="isCroweCheck">
                    <span :class="formClass.requiredField">Este usuario es un empleado de <b>CROWE?</b></span>
                </label>
            </div>
            <!-- Se mostrará unicamente si se hace click en el check -->
            <data-empleado :scope="DTOData"
            @active-ingreso="insertIngreso"
            @active-egreso="insertEgreso"
            :enableEdit="isEdit"></data-empleado>
            <!-- Submit Button. Que permanece inactivo mientras que no se hayan llegano todos los datos requeridos -->
            <div :class="formClass.button"
            :id="[!submitButton.isValid ? formClass.disableButton : isClick ? formClass.disableButton : null]"
            @click="userEmit()"
            v-if="submitButton.isValid">
                <span v-if="isEdit & !isClick">{{ messages.buttonEdit }}</span>
                <span v-else-if="!isEdit & !isClick">{{ messages.buttonCreate }}</span>
                <span v-else-if="isClick"><font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome></span>
            </div>
        </form>
    </div>
</template>

<script>
import Calendar from "@/Components/Calendar.vue";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import { createdMixin } from "@/Components/UiComponents/Users/LifecycleUsers/createdUser.js";
import { mountedMixin } from "@/Components/UiComponents/Users/LifecycleUsers/mountedUser.js";
import { userMethods } from "@/Components/UiComponents/Users/LifecycleUsers/methodsUser.js";
import { userWatchers } from "@/Components/UiComponents/Users/LifecycleUsers/watchersUser.js";
//Templates
import DataPrincipal from "@/Components/UiComponents/Users/TemplatesUsers/UserPrincipal.vue";
import DataContact from "@/Components/UiComponents/Users/TemplatesUsers/UserContact.vue";
import DataEmpleado from "@/Components/UiComponents/Users/TemplatesUsers/UserEmpleado.vue";
//Config Global
import { classConfig, dataMixin, methodsGlobalMixin, watchersGlobalMixin } from "../UiComponentsConfig";

export default {
    props:{
        isEdit: Boolean, //Define si el formulario es de edición o de creación
        isClick: Boolean, //Controla el estado del boton
        dataEdit: Object, //Almacena la data para update
    },
    data(){
        return{
            messages:{
                titleCreate: 'Estas creando a un nuevo usuario',
                titleEdit: 'Estas modificando al usuario',
                contacto: 'Datos de contacto',
                empleado: 'Datos para el empleado',
                buttonEdit: 'Actualizar usuario',
                buttonCreate: 'Crear usuario',
                error: {
                    birthdayError: '',
                    firstnameError: '',
                    secondnameError: '',
                    lastnameError: '',
                    lastsecondnameError: '',
                    documentError: '',
                    firstemailError: '',
                    secondemailError: '',
                    ingresoError:'',
                    egresoError:'',
                }
            },
            inputBirthday: '', //Value del input Birthday
            inputFirstname: '', //Value del input First Name
            inputSecondname: '', //Value del input Second Name
            inputLastname: '', //Value del input para Last Name
            inputLastSecondname: '', //Value del input para Last Second Name
            inputSelect: '', //Value del input para el Documento de Identidad
            inputDocumentoSelect: 0, //Value del input para la selección del tipo de documento
            inputCode: '', //Value del input para el Código
            inputFirstEmail: '', //Value para el primer correo (Principal)
            inputSecondEmail:'', //Value para el segundo correo (Secundario)
            inputFirstPhone:'', //Value para el Telefono Principal
            inputSecondPhone:'', //Value para el Telefono Secundario
            inputCheckedCrowe: false, //Confirmacion si el usuario es empleado
            inputEstadoSelect: 0, //Almacena el value del estado
            inputMunicipioSelect: 0, //Almacena el value del municipio
            inputParroquiaSelect: 0, //Almacena el value de la parroquia
            inputDivisionSelect: 0, //Almacena el value de la división
            inputCargoSelect: 0, //Almacena el value del cargo
            inputStatusSelect: 0, //Almacena el value del status
            inputIngreso: '', //Almacena la fecha de ingreso
            inputEgreso: '', //Almacena la fecha de egreso
            getTargetTypeDocument: '', //Almacena el tipo de select en el documento de identidad
            inputWatchers: [], //Array que almacena todos los watcher

            //Espacio dedicado a variables de data en la base de datos
            typeDocument: [],
            stateData: [], //Data para estados
            divisionData: [], //Data para la division
            cargoData: [], //Data para los cargos
            statusData: [], //Data para el status del empleado
            municipality: {
                init: [], //Data inicial
                select: [] //Data que se mostrara
            }, //Control para los municipios
            parish:{
                init:[], //Data inicial
                select:[] //Data que se mostrara
            }, //Contro para las parroquias

            //Espacio reservado para el control del Create / Edit
            submitButton: {
                birthdayValid: false, //fecha de nacimiento
                firstnameValid: false, //primer nombre
                lastnameValid: false, //primer apellido
                documentValid: false, //cedula de identidad
                codeValid: false, //codigo de usuario
                firstphoneValid: false, // telefono principal
                firstemailValid:false, // correo principal
                isValid: false
            },
            //Constantes
            limitString: { NAME: 20 }
        }
    },
    components: { Calendar, FontAwesome, DataPrincipal, DataContact, DataEmpleado },
    created() { classConfig(this); createdMixin(this) },
    mounted() { mountedMixin(this) },
    //Propiedad computada encarga de pasar toda la data como parametro,
    computed: { DTOData(){ return this.$data } },
    //Insertamos los methods y los watchers
    mixins: [userMethods, userWatchers, dataMixin, methodsGlobalMixin,watchersGlobalMixin],
}
</script>
