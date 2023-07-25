import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, CrudUi, dataUI, watchUI } from "../UIConfig";
import BillingInfo from "@/Components/UiComponents/Billings/BillingInfo.vue";
//Bootstrap
import * as bootstrap from "bootstrap";

const controlBillingApp = createApp({
    data() {
        return {
            controlBillingModal: null, //Variable de configuracion para bootstrap modal
            updateBillingInfo: {}, //Objeto que recibe la informacion del emit billing-update
            billingConceptSelect: [], //Array que almacena los conceptos
            billingIvaSelect: [], //Array que almacena los iva
            billingRetSelect: [], //Array que almacena la retencion de iva
            billingIslrSelect: [], //Array que almacena los islr
            isEdit: false,
            //Espacio para los v-model
            inputConcept: 0, //Concepto
            inputBilling: "", //Numero de factura
            inputDate: "", //Fecha de emision
            inputValue: Number(0).toLocaleString("de-DE"), //Monto
            //Espacio para errores
            errorMessage: {
                billingError: "", //Mensaje de error para la factura
                dateError: "", //Mensaje de error para la fecha de emision
            },
            //Espacio para validaciones
            validate: {
                conceptValid: false, //Tipo de factura, aplica para todos
                billingValid: false, //Numero de factura, no aplica para otros gastos
                dateValid: false, //Fecha de emision, no aplica para otros gastos
                valueValid: false, //Monto de la factura, no aplica para nota de credito
                isValid: false, //Bandera de validacion del formulario
            },
        };
    },
    created() {
        //Luego hacemos una obtencion de los parametros para la creacion
        axios
            .post("/billings/control/get-params")
            .then((request) => {
                //Cargamos los select
                this.billingConceptSelect = request.data.conceptType;
                this.billingIvaSelect = request.data.ivaInfo;
                this.billingRetSelect = request.data.retIvaInfo;
                this.billingIslrSelect = request.data.islrInfo;
            })
            .catch((error) => {
                console.error(error);
            });
        //Llamamos a la sesion
        this.getSession("/billings/delete-update-data");
    },
    mounted() {
        //Control del modal
        this.controlBillingModal = new bootstrap.Modal(
            document.getElementById("billingModal"),
            { keyboard: false, backdrop: true }
        );
    },
    methods: {
        /**Abre el modal de creacion de factura */
        prepareCreateBilling() {
            //Cambiamos el estado del edit
            this.isEdit = false;
            console.log(this.isEdit);
            this.controlBillingModal.show();
        },
        /**Abre el modal de actualización de factura */
        prepareUpdateBilling(billingDTO) {
            //Cambiamos el estado del edit
            this.isEdit = true;
            console.log(this.isEdit);
            this.updateBillingInfo = billingDTO;
            console.log(this.updateBillingInfo);
            this.controlBillingModal.show();
        },
        /**
         * Formatea la fecha a 0000-00-00 capturada por el componente Calendar
         * @param {string} dateEmit Captura la fecha en formato string del emit del Calendar
         */
        formatDate(dateEmit) {
            this.inputDate = `${dateEmit.year}-${dateEmit.month}-${dateEmit.day}`;
        },
    },
    computed: {},
    watch: {},
    mixins: [componentsUI, methodsUI, dataUI, watchUI],
    components: { BillingInfo },
});

if (document.getElementById("billing-control") !== null) {
    controlBillingApp.mount("#billing-control");
    window.location.hash = "#04";
}
