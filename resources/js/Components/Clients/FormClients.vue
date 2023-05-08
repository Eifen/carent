<template inline-template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">Regresar</div>
            <!-- Data Socio -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit: messages.titleCreate"></legend>
            <div :class="formClass.requiredTitle">Campos Obligatorios (<span :class="formClass.requiredField">*</span>)</div>
            <data-socio :scope="DTOData"></data-socio>
            <!-- Datos del Cliente -->
            <legend :class="formClass.legend" v-text="messages.cliente" v-if="inputSocioSelect != 0"></legend>
            <!-- Datos de contacto -->
            <legend :class="formClass.legend" v-text="messages.contacto" v-if="inputSocioSelect != 0"></legend>
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
//Espacio de importaciones
//Hooks
import { createdMixin } from '@/Components/Clients/LifecycleClients/created.js'

//Templates
import DataSocio from '@/Components/Clients/TemplatesClients/DataSocio.vue';

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
                error: {} //Objeto que controla los mensajes de error
            }, //Controla los mensajes del sistema. Tanto de error como de layouts
            submitButton:
            {
                isValid: false, //Controla el estado del botón de crear cliente
            }, //Control las validaciones
            dataSelect:
            {
                socio: [] //Data de todos los socios activos
            },
            inputWatchers: [], //Array que inicializa los Watchers
            inputSocioSelect: 0, //Select de Socios
        }
    },
    created() { createdMixin(this) },
    computed: { DTOData(){ return this.$data }}, //Metodo computado que envia la data a sus hijos a través de propiedades
    components: { FontAwesome, DataSocio }
}
</script>
