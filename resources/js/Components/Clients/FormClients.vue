<template inline-template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">Regresar</div>
            <!-- Data Socio -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit: messages.titleCreate"></legend>
            <div :class="formClass.requiredTitle">Campos Obligatorios (<span :class="formClass.requiredField">*</span>)</div>
            <data-socio :scope="DTOData" :is-edit="isEdit"></data-socio>
            <!-- Datos del Cliente -->
            <legend :class="formClass.legend" v-text="messages.cliente" v-if="inputSocioSelect != 0"></legend>
            <data-cliente :scope="DTOData" :is-edit="isEdit" v-if="inputSocioSelect != 0"></data-cliente>
            <!-- Datos de contacto -->
            <legend :class="formClass.legend" v-text="messages.contacto" v-if="inputSocioSelect != 0"></legend>
            <data-contact-cliente :scope="DTOData" :is-edit="isEdit" v-if="inputSocioSelect != 0"></data-contact-cliente>
            <!-- Submit Button. Que permanece inactivo mientras que no se hayan llegano todos los datos requeridos -->
            <div :class="formClass.button"
            :id="[!submitButton.isValid ? formClass.disableButton : isClick ? formClass.disableButton : null]"
            v-if="inputSocioSelect != 0"
            @click="ClientEmit()">
                <span v-if="isEdit & !isClick">{{ messages.buttonEdit }}</span>
                <span v-else-if="!isEdit & !isClick">{{ messages.buttonCreate }}</span>
                <span v-else-if="isClick"><font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome></span>
            </div>
        </form>
    </div>
</template>

<script>
//Espacio de importaciones
//Hooks
import { createdMixin } from '@/Components/Clients/LifecycleClients/created.js'
import { mountedMixin } from '@/Components/Clients/LifecycleClients/mounted.js'
import { clientWatchers } from '@/Components/Clients/LifecycleClients/watchers.js'
import { clientMethods } from '@/Components/Clients/LifecycleClients/methods.js'

//Templates
import DataSocio from '@/Components/Clients/TemplatesClients/DataSocio.vue';
import DataCliente from '@/Components/Clients/TemplatesClients/DataCliente.vue';
import DataContactCliente from '@/Components/Clients/TemplatesClients/DataContactCliente.vue';

//librerias
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
    props:{
        isEdit: Boolean, //Define si el formulario es de edición o de creación
        isClick: Boolean, //Controla el estado del boton
        dataEdit: Object, //Almacena la data para update
    },
    data()
    {
        return {
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
            }, //Controla los estilos del formulario
            messages:
            {
                titleEdit: "Estas modificando un cliente",
                titleCreate: "Estas creando un cliente",
                cliente: "Datos del cliente",
                contacto: "Datos de contacto del cliente",
                buttonCreate: "Crear cliente",
                buttonEdit: "Actualizar cliente",
                error: {
                    rifError: '',
                    razonSocialError: '',
                    telefonoError: '',
                    direccionError: '',
                    firstemailError: '',
                    webError: '',
                } //Objeto que controla los mensajes de error
            }, //Controla los mensajes del sistema. Tanto de error como de layouts
            submitButton:
            {
                selectSocio: false,
                rifValid: false,
                razonSocialValid: false,
                sectorValid:false,
                servicioValid: false,
                paisesValid: false,
                telefonoValid: false,
                direccionValid: false,
                firstemailValid: false,
                isValid: false, //Controla el estado del botón de crear cliente
            }, //Controla las validaciones
            dataSelect:
            {
                socio: [], //Data de todos los socios activos
                servicios: [], //Data de todos los servicios
                sectores: [], //Data de todos los sectores
                paises: [], //Data de todos los paises
                status: [] //Data de los status para cliente
            },
            inputWatchers: [], //Array que inicializa los Watchers
            inputSocioSelect: 0, //Select de Socios
            inputSectorSelect: 0, //Select de sectores
            inputServicioSelect: 0, //Select de servicios
            inputPaisSelect: 0, //Select de paises
            inputStatusSelect: 0, //Select del Status
            inputNit: '', //Value del NIT
            inputRif: '', //Value del RIF
            inputTelefono: '', //Telefono principal
            inputRazonSocial: '', //Value de la razon social
            inputDireccion: '', //Value de la direccion fiscal
            inputFirstEmail: '', //Value del correo electronico principal de la empresa
            inputWeb: '', //Value de la pagina web
            //Constantes
            LimitString: { NAME: 50, DIR: 200, WEB: 100, RIF: 15, TLF: 20} //Máximo de caracteres para los campos de clientes
        }
    },
    //Ciclo de Vida
    created() { createdMixin(this) },
    mounted() { mountedMixin(this) },
    computed: { DTOData(){ return this.$data }}, //Metodo computado que envia la data a sus hijos a través de propiedades
    components: { FontAwesome, DataSocio, DataCliente, DataContactCliente },
    mixins: [ clientWatchers, clientMethods ]
}
</script>
