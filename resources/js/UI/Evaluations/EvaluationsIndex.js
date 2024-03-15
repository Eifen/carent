// import { createApp } from "vue";
// import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";
// import Evaluations from "@/Components/UiComponents/Evaluations/Evaluations.vue";

// const evaluationsApp = createApp({
//     data() {
//         return {};
//     },
//     methods: {},
//     components: { Evaluations },
//     computed: {},
//     watch: {},
// });

// if (document.getElementById("evaluations-project") !== null) {
//     evaluationsApp.mount("#evaluations-project");
//     window.location.hash = "#06";
// }

import { createApp } from "vue";
import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";
import * as bootstrap from "bootstrap";
import { AXIOSINTERVAL } from "../../app";

const evaluationsApp = createApp({
    data() {
        return {
            EvaluationsColumns: {
                column1: "Codigo",
                column3: "Área",
                column4: "Proyecto",
                column5: "Evaluado",
                column6: "Evaluador",
                column7: "Fecha de Evaluación",
                // column6: "Fecha",
                column8: "Estatus",
                // settings: {
                //     columnS1: "Editar",
                //     columnS2: "Información del cliente",
                // },
            },
            selectSearch: {
                select1: "Periodo",
                select2: "Proyecto",
                select3: "Evaluado",
                select4: "Evaluador",
            },
            tableTarget: "evaluations",
            controlEvaluationModal: {}, //Propiedad que controla el modal de la información de clientes
            previewEvaluationInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/evaluaciones/allEvaluations", this);
        //Configuramos el modal
        // this.controlClientModal = new bootstrap.Modal(
        //     document.getElementById("clientInfoModal"),
        //     { keyboard: false, backdrop: "static" }
        // );
    },
    methods: {
        createEvaluation() {
            window.location.href = "/evaluaciones/create";
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
    computed: {},
    watch: {},
});

if (document.getElementById("evaluations-project") !== null) {
    evaluationsApp.mount("#evaluations-project");
    window.location.hash = "#06";
}
