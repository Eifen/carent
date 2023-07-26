import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, CrudUi, dataUI, watchUI } from "../UIConfig";
import BillingInfo from "@/Components/UiComponents/Billings/BillingInfo.vue";
//Bootstrap
import * as bootstrap from "bootstrap";
import { billingWatch } from "./BillingControlWatch";

const controlBillingApp = createApp({
    data() {
        return {
            controlBillingModal: null, //Variable de configuracion para bootstrap modal
            updateBillingInfo: {}, //Objeto que recibe la informacion del emit billing-update
            billingConceptSelect: [], //Array que almacena los conceptos
            billingNullInfo: [], //Array que almacena las facturaciones disponibles para anular en caso de notas de credito
            billingIvaSelect: [], //Array que almacena los iva
            billingRetSelect: [], //Array que almacena la retencion de iva
            billingIslrSelect: [], //Array que almacena los islr
            isEdit: false,
            noInput: true,
            //Espacio para los v-model
            inputConcept: 0, //Concepto
            inputBilling: "", //Numero de factura
            inputDate: "", //Fecha de emision
            inputValue: "", //Monto
            inputIva: 0, //Valor del iva
            inputRetIva: 0, //Retencion del iva
            inputIslr: 0, //Valor del ISLR
            inputDescription: "", //Descripcion de la factura
            inputControl: "", //Numero de control
            inputPayment: "", //Fecha de de cobro
            inputObservation: "", //Observacion de la factura
            inputNullBill: "", //Input de factura a anular
            inputNullControl: "", //Input del control referencia de la factura a anular
            //Espacio para errores
            errorMessage: {
                billingError: "", //Mensaje de error para la factura
                dateError: "", //Mensaje de error para la fecha de emision
                valueError: "", //Mensaje de error para el monto factura
                descriptionError: "", //Mensaje de error para la descripcion de la factura
                controlError: "", //Mensaje de error para el numero de control
                paymentError: "", //Mensaje de error para la fecha de cobro
                observationError: "", //Mensaje de error para las observaciones
                nullBillError: "", //Mensaje de error para la factura a anular
                nullControlError: "", //Mensaje de error para el control a anular
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
        //Limpia los inputs asociados a la facturacion a anular
        emptyInput() {
            this.inputNullBill = "";
            this.inputNullControl = "";
        },
        /**Abre el modal de creacion de factura */
        prepareCreateBilling() {
            //Cambiamos el estado del edit
            this.isEdit = false;
            //Cargamos las facturas a anular
            this.billingNullInfo = this.prepareNullBill();
            this.controlBillingModal.show();
        },
        /**Abre el modal de actualización de factura */
        prepareUpdateBilling(billingDTO) {
            //Cambiamos el estado del edit
            this.isEdit = true;
            //Cargamos las facturas a anular
            this.billingNullInfo = this.prepareNullBill();
            this.updateBillingInfo = billingDTO;
            this.controlBillingModal.show();
        },
        /**
         * Metodo que se encarga de asociar el id del proyecto a las facturaciones y abstrae los que no tengan billing_cancel_id en null
         */
        prepareNullBill() {
            return this.updateModel.billings.filter((billing) => {
                return (
                    billing.billing_cancel_id == null &&
                    billing.billing_concept_id != 4
                );
            });
        },
        /**
         * Formatea la fecha a 0000-00-00 capturada por el componente Calendar
         * @param {string} dateEmit Captura la fecha en formato string del emit del Calendar
         */
        formatDate(dateEmit) {
            this.inputDate = `${dateEmit.year}-${dateEmit.month}-${dateEmit.day}`;
        },
        /**
         * Formatea la fecha a 0000-00-00 capturada por el componente Calendar
         * @param {string} dateEmit Captura la fecha en formato string del emit del Calendar
         */
        formatPayment(dateEmit) {
            this.inputPayment = `${dateEmit.year}-${dateEmit.month}-${dateEmit.day}`;
        },
        /**
         * Metodo que autocompleta el valor
         * @param {string} stringSelect
         */
        autoCompleteBill(stringSelect) {
            this.inputNullBill = stringSelect;
            //Buscamos la informacion asociada
            const getIndex = this.updateModel.billings
                .map((billing) => billing.billing_number)
                .indexOf(this.inputNullBill);
            //Llenamos el control
            this.inputNullControl =
                this.updateModel.billings[getIndex].control_number;
        },
    },
    computed: {},
    watch: {
        /**
         * Detecta los cambios el input del dropdown
         */
        inputNullBill(nullBilling) {
            //Activa el dropdown unicamente si esta enfocado el input y si no esta vacio el mismo
            if (this.$refs["billAssociated"] !== document.activeElement) {
                this.noInput = false;
            } else {
                this.noInput = true;
                if (nullBilling.length == 0) this.noInput = false;
            }
        },
    },
    mixins: [componentsUI, methodsUI, dataUI, watchUI, billingWatch],
    components: { BillingInfo },
});

if (document.getElementById("billing-control") !== null) {
    controlBillingApp.mount("#billing-control");
    window.location.hash = "#04";
}
