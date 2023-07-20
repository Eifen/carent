import { createApp } from "vue/dist/vue.esm-bundler";
import {
    CrudUi,
    componentsUI,
    dataUI,
    methodsUI,
    watchUI,
} from "../../UIConfig";

const validateApp = createApp({
    data() {
        return {
            validateColumns: {
                column1: "Código",
                column2: "Información",
                column3: "Concepto",
                column4: "Usuario",
                column5: "Fecha",
                column6: "Horas cargadas",
                column7: "Estatus",
                settings: {
                    columnS1: "Aprobar",
                    columnS2: "Rechazar",
                },
            }, //Informacion de las horas
            selectSearch: {
                select1: "Concepto",
                select2: "Usuario",
                select3: "Fecha",
                select4: "Estatus",
            },
            //Seleccionamos la tabla
            tableTarget: "vw_projects_admin_preview",
        };
    },
    created() {
        //Configuramos la paginacion
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        //Llamamos a la tabla
        CrudUi.getTable("/projects/assign/all-admin-hours", this);
    },
    methods: {},
    computed: {},
    watch: {},
    mixins: [componentsUI, dataUI, methodsUI, watchUI],
});

if (document.getElementById("validate-admin") !== null) {
    validateApp.mount("#validate-admin");
    window.location.hash = "#03";
}
