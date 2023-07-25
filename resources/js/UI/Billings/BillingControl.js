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
            isEdit: false,
        };
    },
    created() {
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
