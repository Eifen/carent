import { createApp } from "vue/dist/vue.esm-bundler";
import {
    CrudUi,
    componentsUI,
    dataUI,
    methodsUI,
    watchUI,
} from "../../UIConfig";
import { NOTIFYINTERVAL, AXIOSINTERVAL } from "../../../app";
//Importamos toast
//Toastify
import { toast } from "vue3-toastify";

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
                    columnS3: "Deshacer cambio",
                },
            }, //Informacion de las horas
            selectSearch: {
                select1: "Concepto",
                select2: "Usuario",
                select3: "Fecha desde",
                select4: "Fecha hasta",
                select5: "Estatus",
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
        CrudUi.getTable("/projects/register-hours/all-admin-hours", this);
    },
    methods: {
        /**
         * Metodo que controla el tipo de status a colocar en la hora administrativa
         * @param {int} idLoad Id de la hora administrativa cargada
         * @param {int} type 1: AProbar, 2: Rechazar, 3: Cancelar, controla el estado de la hora
         */
        controlHours(idLoad, type) {
            let statusId = 0; //Controla el estado de la carga en funcion del tipo de proceso
            //Si type es 1 el status sera 2, si es 2 el status sera 3, si es  3 el status es 1
            if (type === 1) statusId = 2;
            if (type === 2) statusId = 3;
            if (type === 3) statusId = 1;

            //Hacemos la llamada al controlador
            const params = {
                operation: type,
                loadId: idLoad,
                loadStatus: statusId,
            };
            console.log(params);
            axios
                .post("/projects/register-hours/control-load-hours", params)
                .then((request) => {
                    //Controlamos los errores
                    if (request.status === 200 && !request.data.response)
                        throw request.data.message;
                    //Mensaje de confirmacion
                    toast.success(request.data.message, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });
                    //Refescamos los valores de la tabla
                    CrudUi.getTable(
                        "/projects/register-hours/all-admin-hours",
                        this
                    );
                })
                .catch((errorMessage) => {
                    console.error(errorMessage);
                    toast.error(errorMessage.error, {
                        position: toast.POSITION.TOP_LEFT,
                        autoClose: NOTIFYINTERVAL,
                    });
                });
        },
    },
    computed: {},
    watch: {},
    mixins: [componentsUI, dataUI, methodsUI, watchUI],
});

if (document.getElementById("validate-admin") !== null) {
    validateApp.mount("#validate-admin");
    window.location.hash = "#03";
}
