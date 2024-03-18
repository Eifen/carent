import { createApp } from "vue/dist/vue.esm-bundler";
import EvaluationForm from "../../../Components/UiComponents/Evaluations/EvaluationForm.vue";
import {
    componentsUI,
    methodsUI,
    CrudUi,
    dataUI,
    watchUI,
} from "../../UIConfig";
import { Validate } from "../../../Models/ValidateModel";

const planillaControl = createApp({
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
            }, paramDTOUser: {
                FirstName: "",
                SecondName: "",
                LastName: "",
                SecondLastName: "",
                Cedula: "",
                Birthday: "",
                Code: "",
                IdParish: 0,
                IdCargo: 0,
                IdDivision: 0,
                DateIngreso: "",
            },
        };
    },
    created() {
        //Llamamos al metodo para guardar la sesion
        this.getSession("deleteEvaluator");
    },
    methods: {
        /**
         * Metodo que crea un nuevo periodo
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        // newPeriod(dataParams) {
        //     this.isClick = true;
        //     this.paramsDTOEvaluations = dataParams;
        //
        //     //AXIOS Create periodo
        //     const paramsToPost = {
        //         evaluations_period: JSON.parse(
        //             JSON.stringify(this.paramsDTOEvaluations),
        //
        //         ),
        //         isEdit: false,
        //     };
        //     const routesSelfDTO = {
        //         post: "/evaluaciones/periodos/create/newPeriod",
        //         redirect: "/evaluaciones/periodos",
        //         self: this,
        //     };
        //     CrudUi.controlCrud(routesSelfDTO, paramsToPost);
        // },

    },
    components: { EvaluationForm },
    mixins: [componentsUI, methodsUI, dataUI, watchUI],
});

if (
    document.getElementById("evaluator-autoevaluation") !== null
) {
    if (document.getElementById("evaluator-autoevaluation") !== null)
        planillaControl.mount("#evaluator-autoevaluation");
    window.location.hash = "#06";
}
