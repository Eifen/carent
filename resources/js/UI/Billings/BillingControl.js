import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, CrudUi, dataUI, watchUI } from "../UIConfig";
import BillingInfo from "@/Components/UiComponents/Billings/BillingInfo.vue";

const controlBillingApp = createApp({
    data() {
        return {};
    },
    created() {
        //Llamamos a la sesion
        this.getSession("/billings/delete-update-data");
    },
    methods: {},
    computed: {},
    watch: {},
    mixins: [componentsUI, methodsUI, dataUI, watchUI],
    components: { BillingInfo },
});

if (document.getElementById("billing-control") !== null) {
    controlBillingApp.mount("#billing-control");
    window.location.hash = "#04";
}
