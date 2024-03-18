import { createApp } from "vue";
import {
    CrudUi,
    componentsUI,
    dataUI,
    methodsUI,
    watchUI,
} from "../../UIConfig";
//Bootstrap
import * as bootstrap from "bootstrap";
import { AXIOSINTERVAL } from "../../../app";

const periodIndex = createApp({
    data() {
        return {
            PeriodsColumns: {
                column1: "Código",
                column2: "Periodo",
                column3: "Descripción",
                column4: "Fecha Desde",
                column5: "Fecha Hasta",
                column6: "Observación",
                column7: "Tipo de personal",
                column8: "Método",
                column9: "Estatus",
                settings: {
                    columnS1: "Editar Periodo",

                },
            },
            selectSearch: {
                select1: "Periodo",
                select2: "Estatus",
            },
            tableTarget: "evaluations_period",
            controlEvaluationModal: {}, //Propiedad que controla el modal de la información de clientes
            previewEvaluationInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        // CrudUi.getTable("/clientes/allClients", this);
        // CrudUi.getTable("/evaluaciones/allEvaluations", this);
        // CrudUi.getTable("/usuarios/allUsers", this);
        CrudUi.getTable("/evaluaciones/periodos/allPeriods", this);

        //Configuramos el modal
        // this.controlClientModal = new bootstrap.Modal(
        //     document.getElementById("clientInfoModal"),
        //     { keyboard: false, backdrop: "static" }
        // );
    },
    methods: {
        createPeriod() {
            window.location.href = "periodos/create";
        },
        editPeriod(idPeriod) {
            // window.location.href = "periodos/update/"+idPeriod;
            const paramsDTO = { codigoSQL: idPeriod };
            const routesDTO = {
                post: "periodos/update/loadingPeriod",
                redirect: "periodos/update",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
    computed: {},
    watch: {},
});

if (document.getElementById("evaluations-period-project") !== null) {
    periodIndex.mount("#evaluations-period-project");
    window.location.hash = "#06";
}
