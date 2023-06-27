import { createApp } from "vue/dist/vue.esm-bundler";
import { componentsUI, dataUI, CrudUi } from "../../UIConfig";
import { AXIOSINTERVAL } from "../../../app";
import { preparDateMethod } from "./PrepareDateMethod";
import { weeksFromDateMethod } from "./WeeksFromDateMethod";
import { hoursForWeeksMethod } from "./HoursForWeeksMethod";

const registerApp = createApp({
    data() {
        return {
            projectAssociatedToCharge: [], //Array que almacena los proyectos por cargar
            conceptHourArray: [], //Array que almacena los conceptos de horas administrativas
            listClass: "", //Controla la clase del contenedor de la lista
            inputConceptSelect: 0, //Captura el input seleccionado
            monthNames: [
                "Enero",
                "Febrero",
                "Marzo",
                "Abril",
                "Mayo",
                "Junio",
                "Julio",
                "Agosto",
                "Septiembre",
                "Octubre",
                "Noviembre",
                "Diciembre",
            ], //Intervalo de meses
            dayNames: [
                "Lunes",
                "Martes",
                "Miercoles",
                "Jueves",
                "Viernes",
                "Sabado",
                "Domingo",
            ],
            yearInitial: 2023, //Año inicial para el select
            monthInitial: 5, //Mes en valor numerico inicial (Julio, para 2023) para el año inicial
            isSelectRange: false, //Variable que se coloca en true cuando se selecciona el mes, semana y año del rango de fechas
            //Inputs del registro
            inputMonthSelect: 0, //Selector de meses
            inputWeekSelect: 0, //Selector de semana
            inputYearSelect: 0, //Selector de año
            //Options de los select
            inputMonthOptions: ["Seleccione un mes"], //Opciones de los meses
            inputWeekOptions: [
                {
                    startDay: 0,
                    endDay: 0,
                    month: 0,
                    message: "Seleccione una semana",
                },
            ], //Opciones de las semanas
            inputYearOptions: ["Seleccione un año"], //Opciones de los años
            prepareDateRequest: [], //Array que se encarga de acomodar la información para consultar en la base de datos
            listDayData: [], //Array que distribuye la fecha a lo largo de los theads
            listProjectHourData: [], //Array que desglosa las horas a proyectos
            listAdminHourData: [], //Array que desglosa las horas administrativas
        };
    },
    created() {
        this.listClass = "list-container register-hour";
        //Configuramos las fechas
        this.prepareYear();
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
    methods: {
        /**
         * Metodo que se encarga se acomodar la fecha para una solicitud a la base de datos
         * @param {Object} dateInfo Objeto que almacena la informacion del startDate, endDate, year, y month actual
         */
        prepareRequest(dateInfo) {
            //Cargamos el array luego de limpiarlo
            this.prepareDateRequest = [];
            for (
                let day = dateInfo["startDay"];
                day <= dateInfo["endDay"];
                day++
            ) {
                //Acomodamos el mes y día a formato ingles (01,02,03,...,09,10,...,)
                const monthFormat =
                    dateInfo["month"] < 10
                        ? `0${dateInfo["month"] + 1}`
                        : dateInfo["month"] + 1;
                const dayFormat = day < 10 ? `0${day}` : day;
                this.prepareDateRequest.push({
                    userId: this.projectAssociatedToCharge[0].user_id, //Captura el id del usuario que inicio sesion
                    registerDate: `${dateInfo["year"]}-${monthFormat}-${dayFormat}`, //Fecha en formato ingles (Y-m-d)
                });
            }

            //Pasamos la información a la base de datos
            axios
                .post("/projects/register-hours/get-load-hours", {
                    request: this.prepareDateRequest,
                })
                .then((request) => {
                    //Distribuimos la informacion
                    this.hoursWeeksDistribution(
                        request.data["date_interval"],
                        request.data["projects_hours"],
                        request.data["admin_hours"]
                    );

                    //Esperamos un tiempo antes de activar la grilla
                    setTimeout(() => {
                        this.isSelectRange = true;
                    }, AXIOSINTERVAL);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
    },
    computed: {},
    watch: {
        inputYearSelect(catchSelectYear) {
            //Detectamos el año y procedemos a cargar los meses
            this.prepareMonth(this.inputYearOptions[catchSelectYear]);
        },
        inputMonthSelect(catchSelectMonth) {
            //Detectamos el mes y procedemos a cargar las semanas
            const getIndexMonth = this.monthNames.indexOf(
                this.inputMonthOptions[catchSelectMonth]
            );

            this.prepareWeek(getIndexMonth);
        },
        inputWeekSelect(catchSelectWeek) {
            //Si ya estaba activo el calendario, lo desactivamos
            this.isSelectRange = false;
            console.log(this.inputWeekOptions[catchSelectWeek]);
            //Una vez seleccionada la semana, acomodamos la informacion
            this.prepareRequest(this.inputWeekOptions[catchSelectWeek]);
        },
    },
    mixins: [
        componentsUI,
        dataUI,
        preparDateMethod,
        weeksFromDateMethod,
        hoursForWeeksMethod,
    ],
});

if (document.getElementById("hour-register") !== null) {
    registerApp.mount("#hour-register");
    window.location.hash = "#03";
}
