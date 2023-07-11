import { createApp } from "vue/dist/vue.esm-bundler";
import {
    componentsUI,
    CrudUi,
    dataUI,
    methodsUI,
    watchUI,
} from "../../UIConfig";

//Importamos bootstrap
import bootstrap from "bootstrap/dist/js/bootstrap";

//Configuracion del modal
let modalConfig = {};
if (document.getElementById("asignHourModal") !== null) {
    modalConfig = new bootstrap.Modal(
        document.getElementById("asignHourModal"),
        { keyboard: false, backdrop: "static" }
    ); //Propiedad que configura el modal de Bootstrap
}

const assignApp = createApp({
    data() {
        return {
            assignColumns: {
                column1: "Código",
                column2: "Proyecto",
                column3: "Cliente",
                column4: "Division",
                column5: "Horas Asignadas",
                settings: { columnS1: "Asignar" },
            },
            selectSearch: {
                select1: "Proyecto",
                select2: "Cliente",
            },
            tableTarget: "vw_projects_assign_preview",
        };
    },
    created() {
        //Cargamos los parametros de la páginación
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        //Asignamos la tabla
        CrudUi.getTable("/projects/assign/assign-projects", this);
    },
    methods: {
        /**
         * Metodo que inicializa el modal de asignación de proyectos
         * @param {*} departmentAssignedId Captura el id del departamento asignado al proyecto
         */
        assignProject(departmentAssignedId) {
            //Accedemos a una consulta en axios para abstraer los usuarios por division. Solo si department es distinto  de 0
            axios
                .post("/projects/assign/assign-users", {
                    department: departmentAssignedId,
                })
                .then((request) => {})
                .catch((error) => {
                    console.error(error);
                });
            //Activamos el modal
            modalConfig.show();
        },
    },
    computed: {},
    watch: {},
    mixins: [componentsUI, CrudUi, dataUI, watchUI, methodsUI],
});

//Verificamos que exista el ID
if (document.getElementById("project-assign") !== null) {
    assignApp.mount("#project-assign");
    window.location.hash = "03";
}
