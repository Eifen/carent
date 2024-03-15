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

const evaluationsIndexList = createApp({
    data() {
        return {
            evaluationsListColumns: {
                column1: "Código",
                column2: "Proyecto",
                column3: "Trabajador",
                column4: "Estatus",
                settings: {
                    columnS1: "Realizar Evaluación",
                    columnS2: "Editar Evaluación",
                    columnS3: "Subir Memorándums",
            },
        },
            selectSearch: {
                select1: "Proyecto",
                select2: "Trabajador",
            },
            tableTarget: "evaluations",
            controlMemoModal: {}, //Propiedad que controla el modal de la información de clientes
            controlcodemodal: 0,
            // previewClientInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable(
            "/evaluaciones/listado-del-personal/allEvaluationsList/",
            this
        );
        //Configuramos el modal
        this.controlMemoModal = new bootstrap.Modal(
            document.getElementById("evamemomodal"),
            { keyboard: false, backdrop: "static" }
        );
        //Axios
        // axios
        // .post("/evaluaciones/listado-del-personal/datos")
        // .then((request) => {
        //     if (request.status !== 200) throw request;
        //     self.dataSelect.codigo = request.data.dataCodigo;
        // })
        // .catch((error) => {
        //     console.error(error);
        // });
    },
    methods: {
        // Cambiar el post de cliente por usuario
        evaluation(idClient) {
            const paramsDTO = { codigoSQL: idClient };
            const routesDTO = {
                post: "listado-del-personal/loadingevaluator",
                redirect: "listado-del-personal/evaluacion",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },

        memoModal(memoModal) {
            this.controlMemoModal.show();
            this.controlcodemodal = memoModal
            console.log(memoModal);
        },

    },

    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-evaluations-list") !== null) {
    evaluationsIndexList.mount("#section-evaluations-list");
    window.location.hash = "#06";
}
