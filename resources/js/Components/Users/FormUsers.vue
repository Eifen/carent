<template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">Regresar</div>
            <!-- Data Principal -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit: messages.titleCreate"></legend>
            <div :class="formClass.requiredTitle">Campos Obligatorios (<span :class="formClass.requiredField">*</span>)</div>
            <fieldset :class="formClass.fieldset">
                <!-- First Name -->
                <div class="mb-3">
                    <label for="firstName">Primer Nombre <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: Pepe"
                        id="firstName"
                        aria-describedby="basic-addon1"
                        v-model="inputFirstname">
                    </div>
                    <!-- Mensajes de error en Nombre-->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.firstnameError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.firstnameError}}
                    </div>
                </div>

                <!-- Second Name -->
                <div class="mb-3">
                    <label for="secondName">Segundo Nombre</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: Eduardo"
                        id="secondName"
                        aria-describedby="basic-addon1"
                        v-model="inputSecondname">
                    </div>
                    <!-- Mensajes de error en Segundo Nombre -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.secondnameError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.secondnameError}}
                    </div>
                </div>

                <!-- Last Name -->
                <div class="mb-3">
                    <label for="lastName">Primer Apellido <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: Salazar"
                        id="lastName"
                        aria-describedby="basic-addon1"
                        v-model="inputLastname">
                    </div>
                    <!-- Mensajes de error en Apellido -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.lastnameError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.lastnameError}}
                    </div>
                </div>

                <!-- Last Second Name -->
                <div class="mb-3">
                    <label for="LastSecondName">Segundo Apellido</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: Marquéz"
                        id="LastSecondName"
                        aria-describedby="basic-addon1"
                        v-model="inputLastSecondname">
                    </div>
                    <!-- Mensajes de error en Segundo Apellido -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.lastsecondnameError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.lastsecondnameError}}
                    </div>
                </div>

                <!-- Documento identidad -->
                <div class="mb-3">
                    <label for="DocumentoIdentidad">Documento de Identidad <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group" :class="formClass.select">
                        <span class="input-group-text" id="basic-addon1">
                            <select class="form-select" @change="enableInput" v-model="inputDocumentoSelect">
                            <option value=0 selected disabled>Tipo</option>
                            <option v-for="(select, cursor) in typeDocument" :key="cursor"
                            :value="select.AbreviaturaTipo">
                                {{ select.DescripcionTipo }}
                            </option>
                            </select>
                        </span>
                        <input type="text" class="form-control" aria-describedby="basic-addon1"
                            :disabled="inputSelect === ''"
                            placeholder="Ejemplo: 15,365,987"
                            id="DocumentoIdentidad"
                            v-model="inputSelect"/>
                    </div>
                    <!-- Mensajes de error en Documento Identidad -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.documentError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.documentError}}
                    </div>
                </div>

                <!-- Birthday -->
                <div class="mb-3">
                    <label for="birthday">Fecha de Nacimiento <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: 1990-02-18"
                        id="birthday"
                        aria-describedby="basic-addon2"
                        v-model="inputBirthday">
                        <span class="input-group-text" id="basic-addon2">
                            <calendar @to-input="insertDate"></calendar>
                        </span>
                    </div>
                    <!-- Mensajes de error en fecha -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.birthdayError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.birthdayError}}
                    </div>
                </div>

                <!-- Código -->
                <div class="mb-3">
                    <label for="Codigo">Código <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: 0001"
                        id="Codigo"
                        aria-describedby="basic-addon1"
                        v-model="inputCode"/>
                    </div>
                </div>
            </fieldset>
            <!-- Datos de contacto -->
            <legend :class="formClass.legend" v-text="messages.contacto"></legend>
            <fieldset :class="formClass.fieldset">
                <!-- First Email -->
                <div class="mb-3">
                    <label for="FirstEmail">Correo Principal <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-at"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: usuario@empresa.dominio"
                        id="FirstEmail"
                        aria-describedby="basic-addon1"
                        v-model="inputFirstEmail">
                    </div>
                    <!-- Mensajes de error en Email -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.firstemailError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.firstemailError}}
                    </div>
                </div>

                <!-- Second Email -->
                <div class="mb-3">
                    <label for="SecondEmail">Correo Secundario</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-at"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: usuario2@empresa2.dominio2"
                        id="SecondEmail"
                        aria-describedby="basic-addon1"
                        v-model="inputSecondEmail">
                    </div>
                    <!-- Mensajes de error en Email -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.secondemailError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.secondemailError}}
                    </div>
                </div>

                <!-- First Phone -->
                <div class="mb-3">
                    <label for="FirstPhone">Telefono Principal <span :class="formClass.requiredField">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ingrese el número de TLF"
                        id="FirstPhone"
                        aria-describedby="basic-addon1"
                        v-model="inputFirstPhone">
                    </div>
                </div>

                <!-- Second Phone -->
                <div class="mb-3">
                    <label for="SecondPhone">Telefono Secundario</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <font-awesome string-icon="fa-solid fa-hashtag"></font-awesome>
                        </span>
                        <input type="text" class="form-control"
                        placeholder="Ingrese el número de TLF"
                        id="SecondPhone"
                        aria-describedby="basic-addon1"
                        v-model="inputSecondPhone">
                    </div>
                </div>
                <!-- Estado. Solo para Edit -->
                <div class="mb-3" v-if="isEdit">
                    <label for="Status">Status del empleado</label>
                    <div class="input-group">
                        <select class="form-select" v-model="inputStatusSelect">
                            <option value=0 selected disabled>Seleccione el status</option>
                            <option v-for="(select, cursor) in statusData" :key="cursor" :value="select.Id">{{ select.Descripcion }}</option>
                        </select>
                    </div>
                </div>
            </fieldset>
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
            <fieldset :class="formClass.fieldset" v-if="inputCheckedCrowe">
                <!-- Estado -->
                <div class="mb-3">
                    <label for="Estado">Estado</label>
                    <div class="input-group">
                        <select class="form-select" v-model="inputEstadoSelect">
                            <option value=0 selected disabled>Seleccione el estado</option>
                            <option v-for="(select, cursor) in stateData" :key="cursor" :value="select.Id">{{ select.NombreEstado }}</option>
                        </select>
                    </div>
                </div>
                <!-- Municipio -->
                <div class="mb-3">
                    <label for="Municipio">Municipio</label>
                    <div class="input-group">
                        <select class="form-select" :disabled="inputEstadoSelect == 0" v-model="inputMunicipioSelect">
                            <option value=0 selected disabled>Seleccione el Municipio</option>
                            <option v-for="(select, cursor) in municipalityData" :key="cursor" :value="select.Id">{{ select.NombreMunicipio }}</option>
                        </select>
                    </div>
                </div>
                <!-- Parroquia -->
                <div class="mb-3">
                    <label for="Parroquia">Parroquia</label>
                    <div class="input-group">
                        <select class="form-select" :disabled="inputMunicipioSelect == 0" v-model="inputParroquiaSelect">
                            <option value=0 selected disabled>Seleccione la Parroquia</option>
                            <option v-for="(select, cursor) in parishData" :key="cursor" :value="select.Id">{{ select.NombreParroquia }}</option>
                        </select>
                    </div>
                </div>
                <!-- División -->
                <div class="mb-3">
                    <label for="Division">División</label>
                    <div class="input-group">
                        <select class="form-select" v-model="inputDivisionSelect">
                            <option value=0 selected disabled>Seleccione la División</option>
                            <option v-for="(select, cursor) in divisionData" :key="cursor" :value="select.Id">{{ select.NombreDivision }}</option>
                        </select>
                    </div>
                </div>
                <!-- Cargo -->
                <div class="mb-3">
                    <label for="Cargo">Cargo</label>
                    <div class="input-group">
                        <select class="form-select" :disabled="inputDivisionSelect == 0" v-model="inputCargoSelect">
                            <option value=0 selected disabled>Seleccione el Cargo</option>
                            <option v-for="(select, cursor) in cargoData" :key="cursor" :value="select.Id">{{ select.NombreCargo }}</option>
                        </select>
                    </div>
                </div>
                <!-- Fecha de ingreso -->
                <div class="mb-3">
                    <label for="ingreso">Fecha de Ingreso</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: 1990-02-18"
                        id="ingreso"
                        aria-describedby="basic-addon2"
                        v-model="inputIngreso">
                        <span class="input-group-text" id="basic-addon2">
                            <calendar @to-input="insertIngreso"></calendar>
                        </span>
                    </div>
                    <!-- Mensajes de error en fecha -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.ingresoError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.ingresoError}}
                    </div>
                </div>
                <!-- Fecha de egreso. Solo para el edit -->
                <div class="mb-3" v-if="isEdit">
                    <label for="egreso">Fecha de Egreso</label>
                    <div class="input-group">
                        <input type="text" class="form-control"
                        placeholder="Ejemplo: 1990-02-18"
                        id="egreso"
                        aria-describedby="basic-addon2"
                        v-model="inputEgreso">
                        <span class="input-group-text" id="basic-addon2">
                            <calendar @to-input="insertEgreso"></calendar>
                        </span>
                    </div>
                    <!-- Mensajes de error en fecha -->
                    <div :class="formClass.failureValidation"
                        v-if="messages.form.egresoError != ''">
                            <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
                            {{messages.form.egresoError}}
                    </div>
                </div>
            </fieldset>
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
import Calendar from "../../Components/Calendar.vue";
import { Validate } from "../../Models/ValidateModel";
import { Exceptions } from "../../Excepciones/Excepciones";
import FontAwesome from "../../Components/FontAwesome/FontAwesome.vue";

const LIMITSTRING = { NAME: 20 }

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
    created()
    {
        //Asignamos las clases
        this.formClass.form = this.formClass.container + '-form'
        this.formClass.legend = this.formClass.form + "-legends"
        this.formClass.fieldset = this.formClass.form + "-fieldset"
        this.formClass.button = this.formClass.form + "-button"
        this.formClass.disableButton = this.formClass.button + "-disable"
        this.formClass.requiredTitle = this.formClass.form + "-title"
        this.formClass.requiredField = this.formClass.requiredTitle + "-field"

        //Definimos los watchers
        this.inputWatchers =
        [{
            //Validaciones de STRING con longitud menor o igual que LIMITSTRING.NAME
            propiedades: ['inputFirstname','inputSecondname','inputLastname','inputLastSecondname'],
            watch: (newString) =>
            {
                try{
                    const validateString = Validate.String(newString,LIMITSTRING.NAME);
                    //First Name se activó pero no cumple con el formato
                    if(this.inputFirstname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "firstnameError", "input":"inputFirstname"};

                    //Second Name se activó pero no cumple con el formato
                    if(this.inputSecondname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "secondnameError", "input":"inputSecondname"};

                    //Last Name se activó pero no cumple con el formato
                    if(this.inputLastname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "lastnameError", "input":"inputLastname"};

                    //Last Second Name se activó pero no cumple con el formato
                    if(this.inputLastSecondname == newString && !validateString.response)
                    throw {"message": validateString.message, "errorInput": "lastsecondnameError", "input":"inputLastSecondname"};

                    //Si paso las validaciones
                    if(this.inputFirstname.length > 0) this.messages.form.firstnameError = '';
                    if(this.inputSecondname.length > 0) this.messages.form.secondnameError = '';
                    if(this.inputLastname.length > 0) this.messages.form.lastnameError = '';
                    if(this.inputLastSecondname.length > 0) this.messages.form.lastsecondnameError = '';

                    //Desactivamos las banderas si el valor es vacio
                    if(this.inputFirstname.length == 0) this.submitButton.firstnameValid = false;
                    if(this.inputLastname.length == 0) this.submitButton.lastnameValid = false;

                    //Activamos la bandera del primer nombre y primer apellido
                    if(this.inputFirstname != '' && validateString.response) this.submitButton.firstnameValid = true;
                    if(this.inputLastname != '' && validateString.response) this.submitButton.lastnameValid = true;

                }catch(errorJSON)
                {
                    this[errorJSON.input] = '';
                    //Desactivamos las banderas
                    if(errorJSON.input == 'inputFirstname') this.submitButton.firstnameValid = false;
                    if(errorJSON.input == 'inputLastname') this.submitButton.lastnameValid = false;
                    //Pasamos el error
                    this.messages.form[errorJSON.errorInput] = Exceptions.CatchWarning(errorJSON.message) + LIMITSTRING.NAME;
                }
            }
        },
        {
            //Validaciones de fechas
            propiedades: ['inputBirthday','inputIngreso','inputEgreso'],
            watch: (newDate) =>
            {
                try{
                    const validateDate = Validate.Date(newDate)
                    //Fecha de nacimiento
                    if(this.inputBirthday == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"birthdayError","input":"inputBirthday"};
                    //Fecha de ingreso
                    if(this.inputIngreso == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"ingresoError","input":"inputIngreso"};
                    //Fecha de egreso
                    if(this.inputEgreso == newDate && !validateDate.response && newDate.length >= 10)
                    throw {"message":validateDate.message,"errorInput":"egresoError","input":"inputEgreso"};
                    //Si no presenta ningún error, es que cumple con el formato

                    //Desactivamos las banderas y el error si la fecha es vacia
                    if(this.inputBirthday == '' || this.inputBirthday.length < 10) this.submitButton.birthdayValid = false;

                    //Deshabilitamos los errores
                     if(this.inputBirthday == '' || this.inputBirthday.length <= 10) this.messages.form.birthdayError = '';
                    if(this.inputIngreso == '' || this.inputIngreso.length <= 10) this.messages.form.ingresoError = '';
                    if(this.inputEgreso == '' || this.inputEgreso.length <= 10) this.messages.form.egresoError = '';

                    //Activamos la bandera de la fecha de Nacimiento
                    if(this.inputBirthday == newDate && validateDate.response && this.inputBirthday.length == 10)
                    this.submitButton.birthdayValid = true;

                }catch(errorJSON)
                {
                    //Desactivamos la bandera
                    if(errorJSON.input == 'inputBirthday') this.submitButton.birthdayValid = false;
                    //Pasamos el error
                    this.messages.form[errorJSON.errorInput] = Exceptions.CatchWarning(errorJSON.message);
                }
            }
        },
        {
            //Validaciones de Correo
            propiedades: ['inputFirstEmail','inputSecondEmail'],
            watch: (newEmail) =>
            {
                try{
                    const validateEmail = Validate.Email(newEmail)
                    //Correo principal
                    if(this.inputFirstEmail == newEmail && !validateEmail.response && newEmail != '')
                    throw {"message":validateEmail.message,"errorInput":"firstemailError"};

                    //Correo Secundario
                    if(this.inputSecondEmail == newEmail && !validateEmail.response && newEmail != '')
                    throw {"message":validateEmail.message,"errorInput":"secondemailError"};

                    //Borramos el error
                    if(this.inputFirstEmail == newEmail && validateEmail.response) this.messages.form.firstemailError = '';
                    if(this.inputSecondEmail == newEmail && validateEmail.response) this.messages.form.secondemailError = '';

                    //Desactivamos la bandera si el input esta vacio
                    if(this.inputFirstEmail == '') this.submitButton.firstemailValid = false;

                    //Activamos la bandera del correo principal
                    if(this.inputFirstEmail == newEmail && validateEmail.response) this.submitButton.firstemailValid = true;
                }catch(errorJSON)
                {
                    //Desactivamos la bandera
                    if(errorJSON.errorInput == 'firstemailError') this.submitButton.firstemailValid = false;
                    //Pasamos el error
                    this.messages.form[errorJSON.errorInput] = Exceptions.CatchWarning(errorJSON.message);
                }
            }
        },
        {
            //Validaciones de telefono
            propiedades:['inputFirstPhone','inputSecondPhone'],
            watch: (newPhone,oldPhone) =>
            {
                try{
                    //00001112222. Colocamos ese formato antes de validar
                    const phoneDTO = newPhone.replace('(','').replace(')','').replace(/-/g,'');
                    const validatePhone = Validate.Phone(phoneDTO);

                    //Se activo telefono principal
                    if(this.inputFirstPhone == newPhone && !validatePhone.response)
                    throw "inputFirstPhone";

                    //Se activo telefono secundario
                    if(this.inputSecondPhone == newPhone && !validatePhone.response)
                    throw "inputSecondPhone";

                    //Desactivamos la bandera si esta vacio o no tiene la longitud adecuada
                    if(this.inputFirstPhone == '' || this.inputFirstPhone.length != 15) this.submitButton.firstphoneValid = false;

                    //Creamos el formato
                    if(this.inputFirstPhone == newPhone && validatePhone.response)
                    {
                        this.inputFirstPhone = validatePhone.message;
                        //Activamos la bandera del telefono principal
                        if(newPhone.length == 15) this.submitButton.firstphoneValid = true;
                    }
                    if(this.inputSecondPhone == newPhone && validatePhone.response) this.inputSecondPhone = validatePhone.message;

                }catch(error)
                {
                    //Desactivamos la bandera
                    if(error == 'inputFirstPhone') this.submitButton.firstphoneValid = false;
                    //Colocamos el valor anterior
                    this[error] = oldPhone;
                }
            }
        }]

        //Axios Request
        axios.post('/usuarios/getParamsInit')
        .then(request => {
            if(request.status !== 200) throw request;

            //Si sincroniza con cada una de las listas
            this.typeDocument = request.data.TiposDocumento
            this.stateData = request.data.StatesUsuario
            this.divisionData = request.data.Divisiones
            this.cargoData = request.data.Cargos
            this.statusData = request.data.StatusUsuario
            //Si la bandera de edit esta activa, pasamos la data almacenada en el cliente
            if(this.isEdit) this.$emit('init-user');
        })
        .catch(error => {
            console.error(error)
        })
    },
    mounted()
    {
        this.$emit('encrypt');

        //Registramos los watch
        for (let cursorWatch = 0; cursorWatch < this.inputWatchers.length; cursorWatch++) {
            const propiedadesWatchers = this.inputWatchers[cursorWatch].propiedades;

            //Activamos los watch
            for(let cursorPropiedad = 0; cursorPropiedad < propiedadesWatchers.length; cursorPropiedad++)
            {
                const propiedad = propiedadesWatchers[cursorPropiedad]
                this.$watch(propiedad,this.inputWatchers[cursorWatch].watch);
            }
        }
    },
    methods:{
        /**
         * Metodo que recibe la data del componente Calendar
         * @param {*} dateDTO Captura el objeto de transferencia que viene desde el emit del componente
         */
        insertDate(dateDTO){ this.inputBirthday = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        /**
         * Metodo que recibe la data del componente Calendar
         * @param {*} dateDTO Captura el objeto de transferencia que viene desde el emit del componente
         */
        insertIngreso(dateDTO){ this.inputIngreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        insertEgreso(dateDTO){ this.inputEgreso = `${dateDTO.year}-${dateDTO.month}-${dateDTO.day}`; },
        /**
         * Metodo que habilita el input del documento de identidad
         * @param {*} inputEvent Objeto de tipo Input event que captura el valor seleccionado
         */
        enableInput(inputEvent){
            this.getTargetTypeDocument = inputEvent.target.value
            this.inputSelect = `${this.getTargetTypeDocument}-`
        },
        /**
         * Metodo que envia la data del formulario  al componente padre
         */
        DTOEmit()
        {
            //Pasamos los parametros a analizar
            let paramsToEmit =
            {
                "FirstName": this.inputFirstname,
                "SecondName": this.inputSecondname,
                "LastName": this.inputLastname,
                "SecondLastName": this.inputLastSecondname,
                "Cedula": this.inputSelect,
                "Birthday": this.inputBirthday,
                "Code": this.inputCode,
                "FirstEmail": this.inputFirstEmail,
                "SecondEmail": this.inputSecondEmail,
                "FirstPhone": this.inputFirstPhone,
                "SecondPhone": this.inputSecondPhone,
                "IdParish": this.inputParroquiaSelect,
                "IdCargo": this.inputCargoSelect,
                "IdDivision": this.inputDivisionSelect,
                "DateIngreso": this.inputIngreso,
            }

            //Preparamos los parametros de update en caso de actualizar
            let paramsToUpdate =
            {
                "DateEgreso": this.inputEgreso,
                "Status": this.inputStatusSelect
            }

            //Si estamos en edición, unimos los dos objetos
            if(this.isEdit) paramsToEmit = { ...paramsToEmit, ...paramsToUpdate }
            this.$emit('submit-form',paramsToEmit);
        }
    },
    watch:{
        //Watch para el update
        dataEdit(newEdit){
            this.inputFirstname = newEdit.Primer_nombre
            this.inputSecondname = newEdit.Segundo_nombre
            this.inputLastname = newEdit.Primer_Apellido
            this.inputLastSecondname = newEdit.Segundo_apellido
            this.inputSelect = newEdit.AbreviaturaTipo + "-" + newEdit.Cedula
            this.inputDocumentoSelect = newEdit.AbreviaturaTipo
            this.inputBirthday = newEdit.Fecha_nacimiento
            this.inputCode = newEdit.Codigo
            this.inputFirstEmail = newEdit.Correo_principal
            this.inputSecondEmail = newEdit.Correo_secundario
            this.inputFirstPhone = newEdit.Telefono_principal
            this.inputSecondPhone = newEdit.Telefono_secundario
            this.inputStatusSelect = newEdit.StatusId
            //Activamos la casilla de empleado
            this.inputEstadoSelect = newEdit.EstadoId
            this.inputMunicipioSelect = newEdit.MunicipioId
            this.inputParroquiaSelect = newEdit.ParroquiaId
            this.inputDivisionSelect = newEdit.DivisionId
            this.inputCargoSelect = newEdit.CargoId
            this.inputIngreso = newEdit.Fecha_ingreso
         },

        //Watch del documento de identidad
        inputSelect(newSelect)
        {
            try{
                const valueDTO = newSelect.replace(/,/g,'');
                const verifyFormat = Validate.FormatDocument(valueDTO);
                //Luego de validar transformamos de nuevo la data
                if(!verifyFormat.response) throw verifyFormat.message;
                if(this.inputSelect.length > 2) this.messages.form.documentError = '';

                this.inputSelect = verifyFormat.message
                //Activamos la bandera del documento
                this.submitButton.documentValid = true;
            }catch (errorMessage){
                //Reestructuramos el formato en un funcion se activo el evento change o no
                this.inputSelect = this.getTargetTypeDocument != ''
                                    ? `${this.getTargetTypeDocument}-`
                                    : `${this.dataEdit.AbreviaturaTipo}-`;
                //Desactivamos la bandera
                this.submitButton.documentValid = false;
                //Pasamos el error
                this.messages.form.documentError = Exceptions.CatchWarning(errorMessage);
            }
        },
        inputCode(newCode,oldCode)
        {
            const codeFormat = new RegExp('^([0-9]{0,6})$');
            if(!codeFormat.test(newCode))this.inputCode = oldCode;

            //Desactivamos la bandera si el input está vacio
            if(this.inputCode.length == 0) this.submitButton.codeValid = false;

            //Activamos la bandera si cumple con la longitud
            if(this.inputCode.length > 0 && this.inputCode.length <= 6) this.submitButton.codeValid = true
        },
        //Activamos de Inputs Watchers
        inputEstadoSelect(getEstado){
            const paramsToPost = {"IdState": getEstado}
            //Consultamos los municipios
            axios.post('/usuarios/getMunicipality',paramsToPost)
            .then(request => {
                if(request.status !== 200) throw request;

                //Sincronizamos
                this.municipalityData = request.data
            }).catch(error => {
                console.error(error);
            })
        },
        inputMunicipioSelect(getMunicipio){
            const paramsToPost = {"IdMunicipio": getMunicipio}
            //Consultamos las parroquias
            axios.post('/usuarios/getParish',paramsToPost)
            .then(request => {
                if(request.status !== 200) throw request;

                //Sincronizamos
                this.parishData = request.data;
            }).catch(error => {
                console.error(error);
            })
        },
        //Watch del submitButton
        submitButton:{
            deep:true,
            handler(checkValid)
            {
                let contValid = 0; //Contador que define cuantos valores estan validos
                for (const field in checkValid) {
                    if(field.toString() != 'isValid' && checkValid[field] === true) contValid++;
                }

                //Activamos o desactivamos el estilado del boton
                if(contValid == (Object.keys(checkValid).length - 1)){
                    this.submitButton.isValid = true;
                }else{ this.submitButton.isValid = false }
            }
        }
    },
    components: { Calendar, FontAwesome }
}
</script>
