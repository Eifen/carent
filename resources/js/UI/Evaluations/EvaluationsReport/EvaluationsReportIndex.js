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
import ReportsIndex from "@/Components/UiComponents/Reports/ReportsIndex.vue";

const evaluationsIndexReport = createApp({
    data() {
        return {
            evaluationsReportColumns: {
                column1: "Periodo",
                column2: "Área",
                column3: "Proyecto",
                column4: "Evaluador",
                column5: "Evaluado",
                column6: "Código",
                column7: "Cargo Actual",
                column8: "Cargo Propuesto",
                column9: "Cargo Aprobado",
                column10: "Observación",
                settings: {
                    columnS1: "Descargar Memorándum",
                    columnS2: "Editar Cargo",
                },
            },
            selectSearch: {
                select1: "Periodo",
                select2: "Proyecto",
                select3: "Evaluador",
                select4: "Cargo Aprobado",
            },
            tableTarget: "evaluations",
            controlUserModal: {}, //Propiedad que controla el modal de la información de clientes
            previewUserInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
            cargoData: null,
            cargoSelect: null,
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
            CrudUi.getTable("/evaluaciones/reporte-de-evaluaciones/list-reports-ev", this);

        // CrudUi.getTable("/clientes/allClients", this);

        // //Configuramos el modal
        // this.controlUserModal = new bootstrap.Modal(
        //     document.getElementById("UserInfoModal"),
        //     { keyboard: false, backdrop: "static" }
        // );
    },
    methods: {

        /**
         * Metodo que recibe el codigo del cliente para mostrar información detallada del mismo
         * @param {*} idUser Almacena la ID seleccionada de la lista de clientes
         * @param {int} idUser
         */
        editarCargo(idUser) {
            //Reiniciamos el preview
            this.previewUserInfo = null;
            //Abrimos el modal
            this.controlUserModal.show();
            //Cargamos su información
            axios
                .post("/evaluaciones/reporte-de-evaluaciones/info-User/", { user_code: idUser })
                .then((request) => {
                    this.previewUserInfo = request.data[0];
                })
                .catch((error) => {
                    console.error(error);
                });

            axios
                .post("/evaluaciones/reporte-de-evaluaciones/getModalsInits")
                .then((request) => {
                    if (request.status !== 200) throw request;

                    self.cargoData = request.data.cargos;
                    // this.cargoData = response.data.cargos;

                })
                .catch((error) => {
                    console.error(error);
                });

        },


    },

    mixins: [componentsUI, methodsUI, watchUI, dataUI],
    components: { ReportsIndex },
});



if (document.getElementById("section-evaluations-report") !== null) {
    evaluationsIndexReport.mount("#section-evaluations-report");
    window.location.hash = "#06";
}



