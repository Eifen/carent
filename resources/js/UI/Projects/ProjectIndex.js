import { createApp } from "vue";
import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";
import { projectInfoMethods } from "./ProjectInfoMethods";
//Bootstrap
import * as bootstrap from "bootstrap";
import axios from "axios";

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
                settings: {
                    columnS1: "Editar",
                    columnS2: "Avance del proyecto",
                },
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
            controlProjectModal: {}, //Controla el modal para proyectos
            previewProjectInfo: null, //Array informativo del proyecto
            previewAllLoadHours: [], //Almacena la información de todas las horas a proyectos cargadas en el sistema
        };
    },
    created() {
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this, this.tableTarget, this.lengthColumns);
    },
    mounted() {
        CrudUi.getTable("/projects/all-projects", this);
        //Control del modal
        this.controlProjectModal = new bootstrap.Modal(
            document.getElementById("projectInfoModal"),
            { keyboard: false, backdrop: true }
        );
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
        /**
         * Metodo que abre el modal y carga la información completa del proyecto
         * @param {int} idProject Corresponde al id del proyecto
         */
        infoProject(idProject) {
            //Reiniciamos la informacion
            this.previewProjectInfo = null;
            this.controlProjectModal.show();
            //Cargamos la información
            axios
                .post("/projects/info-project", { project_id: idProject })
                .then((request) => {
                    this.previewProjectInfo = request.data;
                    console.log(this.previewProjectInfo);
                })
                .catch((error) => {
                    console.error(error);
                });
        },
    },
    mixins: [componentsUI, methodsUI, watchUI, dataUI, projectInfoMethods],
});

if (document.getElementById("section-projects") !== null) {
    projectsIndex.mount("#section-projects");
    window.location.hash = "#03";
}
