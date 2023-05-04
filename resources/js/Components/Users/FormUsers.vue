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
            @click="DTOEmit()">
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
import { createdMixin } from "@/Components/Users/LifecycleUsers/created.js";
import { mountedMixin } from "@/Components/Users/LifecycleUsers/mounted.js";
import { userMethods } from "@/Components/Users/LifecycleUsers/methods.js";
import { userWatchers } from "@/Components/Users/LifecycleUsers/watchers.js";
//Templates
import DataPrincipal from "@/Components/Users/TemplatesUsers/DataPrincipal.vue";
import DataContact from "@/Components/Users/TemplatesUsers/DataContact.vue";
import DataEmpleado from "@/Components/Users/TemplatesUsers/DataEmpleado.vue";


export default {
    props:{
        isEdit: Boolean, //Define si el formulario es de edición o de creación
        isClick: Boolean, //Controla el estado del boton
        dataEdit: Object, //Almacena la data para update
    },
    data(){
        return{
            formClass:
            {
                container: 'dashboard-form-container',
                form:'',
                legend: '',
                fieldset: '',
                button: '',
                disableButton: '',
                successValidation: 'form-SuccessInput',
                failureValidation: 'form-ErrorInput',
                requiredTitle: '',
                requiredField: '',
                select: 'form-select-container',
                empleadoFieldset: '',
            },
            messages:{
                titleCreate: 'Estas creando a un nuevo usuario',
                titleEdit: 'Estas modificando al usuario',
                contacto: 'Datos de contacto',
                empleado: 'Datos para el empleado',
                buttonEdit: 'Actualizar usuario',
                buttonCreate: 'Crear usuario',
                form: {
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
            inputCheckedCrowe: this.isEdit ? true : false, //Confirmacion si el usuario es empleado
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
            municipalityData:[], //Data para los municipios
            parishData:[], //Data para la parroquia
            divisionData: [], //Data para la division
            cargoData: [], //Data para los cargos
            statusData: [], //Data para el status del empleado

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
            }
        }
    },
    components: { Calendar, FontAwesome, DataPrincipal, DataContact, DataEmpleado },
    created() { createdMixin(this) },
    mounted() { mountedMixin(this) },
    //Propiedad computada encarga de pasar toda la data como parametro,
    computed: { DTOData(){ return this.$data } },
    //Insertamos los methods y los watchers
    mixins: [userMethods, userWatchers],
}
</script>