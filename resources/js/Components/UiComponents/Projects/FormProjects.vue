<template inline-template>
    <div :class="formClass.container">
        <form :class="formClass.form">
            <div :class="formClass.button" @click="$emit('return-view')">
                Regresar
            </div>
            <!-- Data Principal -->
            <legend :class="formClass.legend" v-text="isEdit ? messages.titleEdit : messages.titleCreate"></legend>
            <div :class="formClass.requiredTitle">
                Campos Obligatorios (<span :class="formClass.requiredField">*</span>)
            </div>
            <project-principal :scope="DTOData" @transfer-ref="emitTransfer($event, 'principal')"></project-principal>
            <!-- Datos de participantes del proyecto -->
            <legend :class="formClass.legend" v-text="messages.contact"></legend>
            <project-distribution :scope="DTOData" @transfer-ref="emitTransfer($event, 'distribution')"
                @active-hiring="insertDate"></project-distribution>
            <!-- Datos para distribucion de divisiones -->
            <legend :class="formClass.legend" v-text="messages.addiontalInfo"></legend>
            <project-departments :scope="DTOData" @transfer-ref="emitTransfer($event, 'departments')" :is-edit="isEdit"
                @reload-changes="reAsignInfo"></project-departments>
            <!-- Distribucion de divisiones -->
            <project-managers :scope="DTOData" @total-hours="totalHours" :is-edit="isEdit"
                @reload-changes="reAsignInfo"></project-managers>
            <!-- Submit Button. Que permanece inactivo mientras que no se hayan llegano todos los datos requeridos -->
            <div :class="formClass.button"
                :id="[!submitButton.isValid ? formClass.disableButton : isClick ? formClass.disableButton : null,]"
                v-if="submitButton.isValid" @click="projectEmit()">
                <span v-if="isEdit & !isClick">{{ messages.buttonEdit }}</span>
                <span v-else-if="!isEdit & !isClick">{{
                    messages.buttonCreate
                }}</span>
                <span v-else-if="isClick"><font-awesome string-icon="fa-solid fa-spinner" is-spin></font-awesome></span>
            </div>
        </form>
    </div>
</template>

<script>
import Calendar from "@/Components/Calendar.vue";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import { createdMixin } from "@/Components/UiComponents/Projects/LifecycleProjects/CreatedProject.js";
import { mountedMixin } from "@/Components/UiComponents/Projects/LifecycleProjects/MountedProject.js";
import { projectMethods } from "@/Components/UiComponents/Projects/LifecycleProjects/MethodsProject.js";
import { projectWatchers } from "@/Components/UiComponents/Projects/LifecycleProjects/WatchersProject.js";
//Templates
import ProjectPrincipal from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectPrincipal.vue";
import ProjectDistribution from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectDistribution.vue";
import ProjectDepartments from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectDepartments.vue";
import ProjectManagers from "@/Components/UiComponents/Projects/TemplatesProjects/ProjectManagers.vue";

//Config Global
import {
    classConfig,
    dataMixin,
    methodsGlobalMixin,
    watchersGlobalMixin,
} from "../UiComponentsConfig";

export default {
    props: {
        isEdit: Boolean, //Define si el formulario es de edición o de creación
        isClick: Boolean, //Controla el estado del boton
        dataEdit: Object, //Almacena la data para update
    },
    data() {
        return {
            messages: {
                titleCreate: "Estas creando a un nuevo proyecto",
                titleEdit: "Estas modificando al proyecto",
                contact: "Datos de distribución del proyecto",
                addiontalInfo: "Distribución de horas",
                buttonEdit: "Actualizar proyecto",
                buttonCreate: "Crear proyecto",
                error: {
                    hiringDateError: "",
                    projectDescriptionError: "",
                    clientAssociatedError: "",
                    partnerError: "",
                    qualityPartnerError: "",
                    managerError: "",
                    valueError: "",
                    hoursAssignedError: "",
                    averageRateError: "",
                }, //Control de errores de cada uno de los campos
            },
            inputWatchers: [], //Array que almacena todos los watcher
            inputStatusSelect: 0, //Posicion del estado del proyecto
            inputCurrenciesSelect: 0, //Select del tipo de moneda
            inputCompaniesSelect: 0, //Select del tipo de empresa
            inputProjectDescription: "", //String que de la descripcion del proyecto
            inputClientAssociated: "", //String que almacena la información del cliente asociado
            inputManagerAssociated: "", //String que almacena la información del manager asociado
            inputPartnerAssociated: "", //String que almacena la información del socio asociado
            inputQualityPartnerAssociated: "", //String que almacena la información del socio de calidad asociado
            inputHiringDate: "", //Fecha de contratacion
            inputValue: Number(0).toLocaleString("de-DE"), //Monto del proyecto
            inputAverageRate: Number(0).toLocaleString("de-DE"), //Tasa promedio
            inputDepartments: [], //Array que almacena los valores del multiselect
            inputHoursAssigned: Number(0).toLocaleString("de-DE"), //Horas totales asignadas
            inputAdditionalHours: Number(0).toLocaleString("de-DE"), //Horas adicionales
            inputAdditionalValue: Number(0).toLocaleString("de-DE"), //Montos adicionales
            inputQuotas: 0, //Cantidad de cuotas maximas a emitir para facturar
            lastValueId: 0, //Almacena el ultimo ID del array de montos adicionales
            lastHoursId: 0, //Almacena el ultimo ID del array de horas adicionales
            projectId: 0, //Almacena el ID del proyecto para el formulario de edit
            listInfoToUpdate: [], //Objeto de transferencia para el crud de las horas
            listInfoToCancel: [], //Objeto de transferencia para cancelar operaciones
            additionalInput: 0, //Input que se encuentra en el modal de valores adicionales
            controlAverage: Number(0).toLocaleString("de-DE"), //Almacena el cambio de la tasa de un proyecto

            //Espacio dedicado a variables de data en la base de datos
            dataSelect: {
                currencies: [], //Data de las monedas activas
                companies: [], //Data de las empresas activas
                departments: [], //Data de las divisiones activas
                clients: [], //Data de los clientes activos
                partners: [], //Data de los socios activos
                managers: [], //Data de los gerentes activos
                managersPerDepartment: [], //Almacena la estructura de la carga de horas. DTO
                status: [], //Data de los estados disponibles a proyectos
                additionalHours: [], //Array que almacena las horas adicionales de un proyecto
                additionalValues: [], //Array que almacena los montos adicionales de un proyecto
            },
            //Espacio reservado para el control del Create / Edit
            submitButton: {
                hiringDateValid: false,
                projectDescriptionValid: false,
                clientAssociatedValid: false,
                statusValid: false,
                partnerValid: false,
                qualityPartnerValid: false,
                managerValid: false,
                currenciesValid: false,
                valueValid: false,
                companiesValid: false,
                departmentsValid: false,
                hoursAssignedValid: false,
                averageRateValid: false,
                isValid: false, //Gestiona si cumple todos los campos requeridos
            },
            //Constantes
            LimitString: { DESCRIPTION: 200, NAME: 50 },
            dropDownControl: {
                clients: { noInput: false, ref: "clientAssociated" },
                manager: { noInput: false, ref: "managerAssociated" },
                partner: { noInput: false, ref: "partnerAssociated" },
                qualityPartner: {
                    noInput: false,
                    ref: "qualityPartnerAssociated",
                },
            }, //Define si se ha ingresado data en los campos de clientes, socios, gerente
            childsRefs: {
                principal: {},
                distribution: {},
                departments: {},
            }, //Almacena la informacion de los refs hijos
        };
    },
    components: {
        Calendar,
        FontAwesome,
        ProjectPrincipal,
        ProjectDistribution,
        ProjectDepartments,
        ProjectManagers,
    },
    created() {
        classConfig(this);
        createdMixin(this);
    },
    mounted() {
        mountedMixin(this);
    },
    //Propiedad computada encarga de pasar toda la data como parametro,
    computed: {
        DTOData() {
            return this.$data;
        },
    },
    methods: {
        /**
         * Metodo que almacena las Refs de los componentes hijo
         * @param {Object} childRefsEmit almacena los ref que vienen desde el emit
         * @param {String} targetRef Le indica al padre a que refs pertenece
         */
        emitTransfer(childRefsEmit, targetRef) {
            this.childsRefs[targetRef] = childRefsEmit;
        },
    },
    //Insertamos los methods y los watchers
    mixins: [
        projectMethods,
        projectWatchers,
        dataMixin,
        methodsGlobalMixin,
        watchersGlobalMixin,
    ],
};
</script>
