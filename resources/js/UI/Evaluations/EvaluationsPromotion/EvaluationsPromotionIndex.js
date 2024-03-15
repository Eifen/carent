import { createApp } from "vue/dist/vue.esm-bundler";
import {
    componentsUI,
    methodsUI,
    watchUI,
    CrudUi,
    dataUI,
} from "../../UIConfig";
//Bootstrap
import * as bootstrap from "bootstrap";
import { AXIOSINTERVAL } from "../../../app";

const evaluationsIndexPromotion = createApp({
    data() {
        return {
            evaluationsPromotionColumns: {
                column1: "Codigo",
                column2: "Area",
                column3: "Proyecto",
                column4: "Evaluado",
                column5: "Cargo aprobado",
                column6: "Fecha ultimo ascenso",
                column7: "Cargo actual",
                column8: "Cargo propuesto",
                column9: "Evaluador",
                // settings: {
                //     columnS1: "Editar",
                //     columnS2: "Información del cliente",
                // },
            },
            selectSearch: {
                select1: "Codigo",
                select2: "Proyecto",
                select3: "Evaluador",
                select4: "Evaluado",
            },
            tableTarget: "evaluations",
            controlClientModal: {}, //Propiedad que controla el modal de la información de clientes
            previewClientInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable(
            "/evaluaciones/promociones-ascensos/allEvaluationsPromotion/",
            this
        );
    },
    methods: {},
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-evaluations-promotion") !== null) {
    evaluationsIndexPromotion.mount("#section-evaluations-promotion");
    window.location.hash = "#06";
}
