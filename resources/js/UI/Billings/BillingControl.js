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
            nullBillingId: 0, //Almacena el id de la factura a anular
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
                nullBillValid: true, //Factura a anular
                nullControlValid: true, //Control de factura a anular, este y el de arriba solo aplica para nota de credito
                isValid: false, //Bandera de validacion del formulario
            },
            //Controles de string
            limitString: {
                number: 20,
                comments: 100,
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
            { keyboard: false, backdrop: "static" }
        );
    },
    methods: {
        /** Se encarga de actualizar o crear una nueva factura */
        controlBilling() {
            this.isClick = true;
            let prepareParams = {
                projectId: this.updateModel.project.project_id,
                concept: this.inputConcept, //Concepto
                numberBilling: this.inputBilling, //Numero de factura
                dateBilling: this.inputDate, //Fecha de emision
                valueBilling: this.inputValue
                    .replace(/\./g, "")
                    .replace(",", "."), //Monto
                description: this.inputDescription, //Descripcion de la factura
                numberControl: this.inputControl, //Numero de control
                datePayment: this.inputPayment, //Fecha de de cobro
                observation: this.inputObservation, //Observacion de la factura
                nullId: this.nullBillingId, //Input de factura a anular
                edit: this.isEdit,
            };

            if (this.isEdit) {
                prepareParams["billingId"] = this.updateBillingInfo.billing_id;
            }

            //Validamos los taxes
            const ivaIndex = this.billingIvaSelect
                .map((billing) => billing.iva_value)
                .indexOf(this.inputIva);
            const retIvaIndex = this.billingRetSelect
                .map((billing) => billing.retention_value)
                .indexOf(this.inputRetIva);
            const islrIndex = this.billingIslrSelect
                .map((billing) => billing.deduction_value)
                .indexOf(this.inputIslr);

            //Llenamos la informacion de los taxes
            prepareParams["iva"] =
                ivaIndex != -1 ? this.billingIvaSelect[ivaIndex].iva_id : 3;
            prepareParams["retIva"] =
                retIvaIndex != -1
                    ? this.billingRetSelect[retIvaIndex].retention_id
                    : 3;
            prepareParams["islr"] =
                islrIndex != -1
                    ? this.billingIslrSelect[islrIndex].billing_deduction_id
                    : 4;

            //Enviamos la informacion al controlador
            CrudUi.controlCrud(
                {
                    post: "/billings/control/submit-billing",
                    redirect: "",
                    self: this,
                },
                prepareParams
            );

            //Hacemos un update de la informacion
            axios
                .post("/billings/control/refresh-billing", {
                    project_id: this.updateModel.project.project_id,
                })
                .then((request) => {
                    //Reasignamos la data
                    this.updateModel = request.data;
                    //Cerramos el modal
                    this.controlBillingModal.hide();
                    this.emptyFields(true);
                    this.isClick = false;
                })
                .catch((error) => {
                    console.error(error);
                    this.isClick = false;
                });
        },
        //Limpia los inputs asociados a la facturacion a anular
        emptyInput() {
            this.inputNullBill = "";
            this.inputNullControl = "";
        },
        /**
         * Metodo que limpia todos los campos
         * @param {boolean} [all=false] Si se coloca true elimina tambien el inputConcept. Por defecto es false
         */
        emptyFields(all = false) {
            if (all) this.inputConcept = 0;
            this.inputBilling = ""; //Numero de factura
            this.inputDate = ""; //Fecha de emision
            this.inputValue = ""; //Monto
            this.inputIva = 0; //Valor del iva
            this.inputRetIva = 0; //Retencion del iva
            this.inputIslr = 0; //Valor del ISLR
            this.inputDescription = ""; //Descripcion de la factura
            this.inputControl = ""; //Numero de control
            this.inputPayment = ""; //Fecha de de cobro
            this.inputObservation = ""; //Observacion de la factura
            this.inputNullBill = ""; //Input de factura a anular
            this.inputNullControl = ""; //Input del control referencia de la factura a anular
        },
        /**
         * MEtodo que se encarga de cargar los campos del edit
         */
        enableEdit() {
            this.inputBilling =
                this.updateBillingInfo.billing_number == null
                    ? ""
                    : this.updateBillingInfo.billing_number; //Numero de factura
            this.inputDate =
                this.updateBillingInfo.billing_date == null
                    ? ""
                    : this.updateBillingInfo.billing_date; //Fecha de emision
            this.inputValue = Number(
                this.updateBillingInfo.billing_value
            ).toLocaleString("de-DE"); //Monto
            this.inputIva = this.updateBillingInfo.iva_value; //Valor del iva
            this.inputRetIva = this.updateBillingInfo.retention_value; //Retencion del iva
            this.inputIslr = this.updateBillingInfo.deduction_value; //Valor del ISLR
            this.inputDescription =
                this.updateBillingInfo.billing_description == null
                    ? ""
                    : this.updateBillingInfo.billing_description; //Descripcion de la factura
            this.inputControl =
                this.updateBillingInfo.control_number == null
                    ? ""
                    : this.updateBillingInfo.control_number; //Numero de control
            this.inputPayment =
                this.updateBillingInfo.payment_date == null
                    ? ""
                    : this.updateBillingInfo.payment_date; //Fecha de de cobro
            this.inputObservation =
                this.updateBillingInfo.billing_observations == null
                    ? ""
                    : this.updateBillingInfo.billing_observations; //Observacion de la factura
        },
        /**Abre el modal de creacion de factura */
        prepareCreateBilling() {
            //Cambiamos el estado del edit
            this.emptyFields(true);
            this.isEdit = false;
            //Cargamos las facturas a anular
            this.billingNullInfo = this.prepareNullBill();
            this.controlBillingModal.show();
        },
        /**Abre el modal de actualización de factura */
        prepareUpdateBilling(billingDTO) {
            //Cambiamos el estado del edit
            this.emptyFields(true);
            this.isEdit = true;
            //Cargamos las facturas a modificar
            this.updateBillingInfo = billingDTO;
            //Almacenamos la informacion
            this.inputConcept = this.updateBillingInfo.billing_concept_id; //Concepto
            this.controlBillingModal.show();
        },
        /**
         * Metodo que se encarga de asociar el id del proyecto a las facturaciones y abstrae los que no tengan billing_cancel_id en null
         */
        prepareNullBill() {
            return this.updateModel.billings.filter((billing) => {
                return (
                    billing.billing_concept_id != 4 &&
                    billing.billing_number != null &&
                    billing.control_number != null &&
                    billing.status_id != 2 &&
                    billing.billing_number.length != 0
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
        },
        /**
         * Metodo que se encarga de autocompletar el campo de control
         * @param {string} stringToCompare Almacena el string a comparar
         */
        autoCompleteControlBill(stringToCompare) {
            const getIndex = this.updateModel.billings
                .map((billing) => billing.billing_number)
                .indexOf(stringToCompare);
            //Llenamos el control
            this.inputNullControl =
                getIndex != -1
                    ? this.updateModel.billings[getIndex].control_number
                    : "";
        },
    },
    computed: {
        subTotal() {
            const value = this.validate.valueValid
                ? parseFloat(
                      this.inputValue.replace(/\./g, "").replace(",", ".")
                  )
                : 0;
            const ivaValue = (value * parseFloat(this.inputIva)) / 100;
            const retIvaValue = (ivaValue * parseFloat(this.inputRetIva)) / 100;
            return value + ivaValue - retIvaValue;
        },
        total() {
            const value = this.validate.valueValid
                ? parseFloat(
                      this.inputValue.replace(/\./g, "").replace(",", ".")
                  )
                : 0;
            const islrValue = (value * this.inputIslr) / 100;
            return islrValue;
        },
    },
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

            if (this.inputConcept == 4) {
                this.autoCompleteControlBill(nullBilling);
                //Muestra un mensaje de error si el campo no coincide
                const getIndex = this.billingNullInfo
                    .map((billing) => billing.billing_number)
                    .indexOf(nullBilling);
                if (getIndex == -1) {
                    this.nullBillingId = null;
                    this.validate.nullBillValid = false;
                    this.errorMessage.nullBillError =
                        "El valor no coincide a una factura asociada en la base de datos";
                } else {
                    this.nullBillingId =
                        this.billingNullInfo[getIndex].billing_id;
                    this.validate.nullBillValid = true;
                    this.errorMessage.nullBillError = "";
                }

                //Si esta vacio desactiva
                if (nullBilling.length == 0) {
                    this.validate.nullBillValid = false;
                    this.errorMessage.nullBillError = "";
                }
            }
        },
        inputNullControl(nullControl) {
            //Muestra un mensaje de error si el campo no coincide
            if (this.inputConcept == 4) {
                const getIndex = this.billingNullInfo
                    .map((billing) => billing.control_number)
                    .indexOf(nullControl);
                if (getIndex == -1) {
                    this.validate.nullControlValid = false;
                    this.errorMessage.nullControlError =
                        "El valor no coincide a un control asociada en la base de datos";
                } else {
                    this.validate.nullControlValid = true;
                    this.errorMessage.nullControlError = "";
                }

                //Si esta vacio desactiva
                if (nullControl.length == 0) {
                    this.validate.nullControlValid = false;
                    this.errorMessage.nullControlError = "";
                }
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
