import { createApp } from "vue/dist/vue.esm-bundler";
import { CrudUi, dataUI, watchUI, componentsUI, methodsUI } from "../UIConfig";
import ReportsIndex from "@/Components/UiComponents/Reports/ReportsIndex.vue";

const reportsApp = createApp({
    data() {
        return {};
    },
    mounted() {
        //Llamamos a la lista de reportes
        CrudUi.getTable("/reports/list-reports", this);
    },
    methods: {},
    computed: {},
    watch: {},
    mixins: [dataUI, watchUI, componentsUI, methodsUI],
    components: { ReportsIndex },
});

if (document.getElementById("section-reports") !== null) {
    reportsApp.mount("#section-reports");
    window.location.hash = "#05";
}
