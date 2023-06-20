import { createApp } from "vue";
import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";

//TODO Realizar proceso para projectos
const projectsIndex = createApp({
    data() {
        return {
            projectsColumns: {
                column1: "Código",
                column2: "Proyecto",
                column3: "Horas contratadas",
                column4: "Fecha contratacion",
                column5: "Cliente",
                column6: "Socio",
                column7: "Gerente",
                column8: "Estatus",
                settings: { columnS1: "Editar" },
            },
            selectSearch: {
                select1: "Codigo",
                select2: "Proyecto",
                select3: "Socio",
                select4: "Gerente",
                select5: "Cliente",
                select6: "Estatus",
            },
            tableTarget: "projects",
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/projects/all-projects", this);
    },
    methods: {
        createProject() {
            window.location.href = "/projects/create";
        },
        /**
         * Metodo que crea una instancia en Sessión para pasarla temporalmente a un formulario de actualización
         * @param {*} idProject Almacena la ID seleccionada de la lista de projects
         */
        editProject(idProject) {
            const paramsDTO = { codigoSQL: idProject };
            const routesDTO = {
                post: "/projects/update/loading-project",
                redirect: "/projects/update",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

if (document.getElementById("section-projects") !== null) {
    projectsIndex.mount("#section-projects");
    window.location.hash = "#03";
}
