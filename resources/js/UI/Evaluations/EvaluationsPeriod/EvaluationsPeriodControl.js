import { createApp } from "vue/dist/vue.esm-bundler";
import FormPeriod from "../../../Components/UiComponents/Evaluations/FormPeriod.vue";
import {
    componentsUI,
    methodsUI,
    CrudUi,
    dataUI,
    watchUI,
} from "../../UIConfig";
import { Validate } from "../../../Models/ValidateModel";

const periodControl = createApp({
    data() {
        return {
            updateModel: {}, //Objeto encargado de inicializar la data del edit
            paramsDTOEvaluations: {
                Perido: "",
                FechaDesde: "",
                FechaHasta: "",
                PeriodoDescripcion: "",
                PeriodoObservacion: "",
                IdTipo: 0,
                IdMetodo: 0,
                IdStatus: 0,
            },
            paramsDTOEdit: {
                IdPeriod: 0,
            }, //Objeto para el edit
        };
    },
    methods: {
        /**
         * Metodo que crea un nuevo periodo
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        newPeriod(dataParams) {
            this.isClick = true;
            this.paramsDTOEvaluations = dataParams;

            //AXIOS Create periodo
            const paramsToPost = {
                evaluations_period: JSON.parse(
                    JSON.stringify(this.paramsDTOEvaluations),
                ),
                isEdit: false,
            };
            const routesSelfDTO = {
                post: "/evaluaciones/periodos/create/newPeriod",
                redirect: "/evaluaciones/periodos",
                self: this,
            };
            CrudUi.controlCrud(routesSelfDTO, paramsToPost);
        },
        // /**
        //  * Actualiza un cliente
        //  * @param {Object} dataParams Objeto con la información a actualizar
        //  */
        updatePeriod(dataParams) {
            this.isClick = true;
            //Pasamos la data clientes
            console.log(dataParams.Periodo);
            // this.validateNivel2(dataParams);

            //Verificamos el status
            // dataParams.Status == 0
            //     ? (this.paramsDTOEdit.IdStatus = null)
            //     : (this.paramsDTOEdit.IdStatus = dataParams.Status);

            this.paramsDTOEdit.IdPeriod = this.updateModel.evaluation_period_id;

            //Una vez haya terminado de verificar validateNivel2
            this.paramsDTOEdit = {
                ...this.paramsDTOEdit,
                ...dataParams
            };

            //AXIOS Update Client
            const paramsToPost = {
                period: JSON.parse(JSON.stringify(this.paramsDTOEdit)),
                isEdit: true,
            };
            const routesSelfDTO = {
                post: "/evaluaciones/periodos/update/updatePeriod",
                redirect: "/evaluaciones/periodos",
                self: this,
            };
            CrudUi.controlCrud(routesSelfDTO, paramsToPost);
        },
        /**
         * Espacio de validaciones para campos no obligatorios
         * @param {*} dataToValidate Se importa todo el array de datos
         */
        // validateNivel2(dataToValidate) {
        //     const DTONit = Validate.Number(dataToValidate.Nit);
        //     const DTOWeb = Validate.String(dataToValidate.PaginaWeb, 100);

        //     //Descomponemos el Nit
        //     !DTONit.response ||
        //     (DTONit.response && dataToValidate.Nit.length > 11)
        //         ? (this.paramsDTOEvaluations.Nit = 0)
        //         : (this.paramsDTOEvaluations.Nit = parseInt(
        //               dataToValidate.Nit
        //           ));

        //     //Descomponemos la página Web
        //     !DTOWeb.response
        //         ? (this.paramsDTOEvaluations.PaginaWeb = "")
        //         : (this.paramsDTOEvaluations.PaginaWeb =
        //               dataToValidate.PaginaWeb);
        // },
    },
    components: { FormPeriod },
    created() {
        //Llamamos a la sesion (no cambiar hasta crear la sesion de evaluaciones)
        this.getSession("/evaluaciones/periodos/deleteUpdateData");
    },
    mixins: [componentsUI, methodsUI, dataUI, watchUI],
});

if (
    document.getElementById("create-evaluation-period") !== null ||
    document.getElementById("update-evaluation-period") !== null
) {
    if (document.getElementById("create-evaluation-period") !== null)
        periodControl.mount("#create-evaluation-period");
    if (document.getElementById("update-evaluation-period") !== null)
        periodControl.mount("#update-evaluation-period");
    window.location.hash = "#06";
}
