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

const evaluationsIndexProject = createApp({
    data() {
        return {
            evaluationsProjectColumns: {
                column1: "Código",
                column2: "Código User",
                column3: "Proyecto",
                settings: {
                    columnS1: "Consultar Evaluación",
                    columnS2: "Autoevaluación",
                    columnS3: "Información Evaluación",
                },
            },
            selectSearch: {
                select1: "Proyecto",
            },
            tableTarget: "evaluations",
            controlUserModal: {}, //Propiedad que controla el modal de la información de clientes
            previewUserInfo: null, //Almacena la informacion del cliente seleccionado para sus vista preeliminar
            cargoData: null,
            cargoSelect: null,
            acumu: { auto: 0, eva: 0 },
        };
    },
    mounted() {
        CrudUi.getTable(
            "/evaluaciones/proyecto-para-evaluar/allEvaluationsProject/",
            this
        );

        // CrudUi.getTable("/clientes/allClients", this);

        //Configuramos el modal
        this.controlUserModal = new bootstrap.Modal(
            document.getElementById("UserInfoModal"),
            { keyboard: false, backdrop: "static" }
        );

    },
    methods: {
        /**
         * Metodo que recibe el codigo del cliente para mostrar información detallada del mismo
         * @param {*} idUser Almacena la ID seleccionada de la lista de clientes
         * @param {int} idUser
         */
        infoEvaluation(idUser) {
            //Reiniciamos el preview
            this.previewUserInfo = null;
            //Abrimos el modal
            this.controlUserModal.show();
            console.log(JSON.parse(JSON.stringify(this.listData.find(list => list['código'] === idUser))))
            //Cargamos su información
            axios
                .post("/evaluaciones/proyecto-para-evaluar/info-User", {
                    user_code: JSON.parse(JSON.stringify(this.listData.find(list => list['código'] === idUser)))
                })
                .then((request) => {
                    this.previewUserInfo = request.data[0];
                    this.promemodal(JSON.parse(this.previewUserInfo['dt_section1_total']))
                })
                .catch((error) => {
                    console.error(error);
                });

        },
        promemodal(value) {
            value.map((val) => {
                this.acumu.auto += val.auto / 6
                this.acumu.eva += val.eva / 6
            })
            this.acumu.auto = this.acumu.auto.toFixed(2);
            this.acumu.eva = this.acumu.eva.toFixed(2);
        },
        // Cambiar el post de cliente por usuario
        //Metodo dedicados a las configuraciones de la tabla
        autoEvaluation(idUsuario) {
            //Seccionamos el ID y luego lo pasamos al controlador
            const paramsDTO = { codigoSQL: JSON.parse(JSON.stringify(this.listData.find(list => list['código'] === idUsuario))) };
            const routesDTO = {
                post: "proyecto-para-evaluar/planilla/loadingAuEva",
                redirect: "proyecto-para-evaluar/planilla",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },

        // Cambiar el post de cliente por usuario
        //Metodo dedicados a las configuraciones de la tabla
        infoAutoEvaluation(idUsuario) {
            //Seccionamos el ID y luego lo pasamos al controlador
            const paramsDTO = { codigoSql: JSON.parse(JSON.stringify(this.listData.find(list => list['código'] === idUsuario))) };
            const routesDTO = {
                post: "proyecto-para-evaluar/planilla/loadingEva",
                redirect: "proyecto-para-evaluar/planilla/AuEvaInfo",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },


    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-evaluations-project") !== null) {
    evaluationsIndexProject.mount("#section-evaluations-project");
    window.location.hash = "#06";
}
