import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, dataUI, CrudUi } from "../../UIConfig";
import { AXIOSINTERVAL } from "../../../app";

const registerApp = createApp({
    data() {
        return {
            projectAssociatedToCharge: [], //Array que almacena los proyectos por cargar
            conceptHourArray: [], //Array que almacena los conceptos de horas administrativas
            listClass: "", //Controla la clase del contenedor de la lista
            inputConceptSelect: 0, //Captura el input seleccionado
        };
    },
    created() {
        this.listClass = "list-container register-hour";
        //Preparamos la informacion a través de solicitud al axios
        axios
            .post("/projects/register-hours/prepare-register")
            .then((request) => {
                //Asignamos la información
                this.projectAssociatedToCharge = request.data["hoursPerCharge"];
                this.conceptHourArray = request.data["conceptHour"];

                setTimeout(() => {
                    console.log(this.projectAssociatedToCharge);
                    console.log(this.conceptHourArray);
                    this.isMounted = true;
                }, AXIOSINTERVAL);
            })
            .catch((error) => {
                console.error(error);
            });
    },
    mounted() {},
    methods: {},
    computed: {},
    watch: {},
    mixins: [componentsUI, dataUI],
});

if (document.getElementById("hour-register") !== null) {
    registerApp.mount("#hour-register");
    window.location.hash = "#03";
}
