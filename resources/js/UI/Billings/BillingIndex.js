import { createApp } from "vue";
import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";

//App
const billingApp = createApp({
    data() {
        return {
            billingsColumns: {
                column1: "Código",
                column2: "Proyecto",
                column3: "Cliente",
                column4: "Fecha contratacion",
                column5: "Monto del proyecto",
                column6: "Estatus",
                settings: {
                    columnS1: "Informacion de facturacion",
                },
            },
            selectSearch: {
                select2: "Proyecto",
                select5: "Cliente",
                select6: "Estatus",
            },
            tableTarget: "projects",
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/billing/all-projects", this);
    },
    methods: {},
    computed: {},
    watch: {},
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

//Detectamos el ID
if (document.getElementById("billing-project") !== null) {
    billingApp.mount("#billing-project");
    window.location.href = "#04";
}
