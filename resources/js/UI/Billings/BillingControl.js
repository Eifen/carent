import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, methodsUI, CrudUi, dataUI } from "../UIConfig";
import BillingInfo from "@/Components/UiComponents/Billings/BillingInfo.vue";

const controlBillingApp = createApp({
    data() {
        return {
            updateModel: [], //Corresponde a la carga del proyecto, variable necesaria para el prepareUpdate
        };
    },
    mounted() {
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    methods: {},
    computed: {},
    watch: {},
    mixins: [componentsUI, methodsUI, dataUI],
    components: { BillingInfo },
});

if (document.getElementById("billing-control") !== null) {
    controlBillingApp.mount("#billing-control");
    window.location.hash = "#04";
}
