<template inline-template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">Regresar</div>
            <!-- Estas Creando un Periodo  -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit : messages.titleCreate"></legend>
            <!-- Campos Obligatorios  -->
            <div :class="formClass.requiredTitle">Campos Obligatorios (<span :class="formClass.requiredField">*</span>)
            </div>
            <!-- Datos del periodo -->
            <legend :class="formClass.legend" v-text="messages.evaluacion"></legend>
            <data-evaluacion :scope="DTOData" :is-edit="isEdit"></data-evaluacion>
            <!-- Submit Button. Que permanece inactivo mientras que no se hayan llegano todos los datos requeridos -->

            <div :class="formClass.button"
                :id="[!submitButton.isValid ? formClass.disableButton : isClick ? formClass.disableButton : null]"
                v-if="submitButton.isValid" @click="evaluationEmit()">
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

import { createdMixin } from '@/Components/UiComponents/Evaluations/LifeCyclePeriods/createdPeriod.js'
import { mountedMixin } from '@/Components/UiComponents/Evaluations/LifeCyclePeriods/mountedPeriod.js'
import { evaluationWatchers } from '@/Components/UiComponents/Evaluations/LifeCyclePeriods/watchersPeriod.js'
import { evaluationMethods } from '@/Components/UiComponents/Evaluations/LifeCyclePeriods/methodsPeriod.js'

//Templates
import DataEvaluacion from '@/Components/UiComponents/Evaluations/TemplatesEvaluations/EvaluationPrincipal.vue';

//librerias
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

//Config Global
import { classConfig, dataMixin, methodsGlobalMixin, watchersGlobalMixin } from "../UiComponentsConfig";

export default {
    props: {
        isEdit: Boolean, //Define si el formulario es de edición o de creación
        isClick: Boolean, //Controla el estado del boton
        dataEdit: Object, //Almacena la data para update
    },
    data() {
        return {
            messages:
            {
                titleEdit: "Estas modificando una Evaluación",
                titleCreate: "Estas creando un periodo de evaluación",
                evaluacion: "Datos de la evaluación",
                contacto: "Datos de contacto del cliente",
                buttonCreate: "Crear periodo",
                buttonEdit: "Actualizar evaluación",
                error: {
                    periodoError: '',
                    descripcionError: '',
                    tipoError: '',
                    metodoError: '',
                    observacionError: '',
                    estatusError: '',
                } //Objeto que controla los mensajes de error
            }, //Controla los mensajes del sistema. Tanto de error como de layouts
            submitButton:
            {
                periodoValid: false,
                fechaDesdeValid: false,
                fechaHastaValid: false,
                selectTipo: false,
                selectMetodo: false,
                selectStatus: false,
                isValid: false, //Controla el estado del botón de crear cliente
            }, //Controla las validaciones
            dataSelect:
            {
                tipos: [], //Data de todos los servicios
                metodos: [], //Data de todos los metodos
                periodos: [], //Data de todos los periodos
                status: [] //Data de los status para cliente
            },
            inputWatchers: [], //Array que inicializa los Watchers
            inputPeriodo: '', //Select de sectores
            inputPeriodoId: '', //id period
            inputFechaDesde: '', //Select de fecha
            inputFechaHasta: '', //Select de fecha
            inputTipoSelect: 0,
            inputMetodoSelect: 0, //Select de Socios
            inputStatusSelect: 0, //Select del Status
            inputPeriodoDescripcion: '', //Value de la descripcion
            inputObservacion: '', //Value de la descripcion
        }

    },

    //Ciclo de Vida
    created() { classConfig(this); createdMixin(this) },
    mounted() { mountedMixin(this); },
    computed: { DTOData() { return this.$data } }, //Metodo computado que envia la data a sus hijos a través de propiedades
    components: { FontAwesome, DataEvaluacion },
    mixins: [evaluationWatchers, evaluationMethods, dataMixin, methodsGlobalMixin, watchersGlobalMixin]


}


</script>


