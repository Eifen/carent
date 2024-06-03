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
                settings: {
                    columnS2: "Avance del proyecto",
                },
                column1: "Código",
                column2: "Proyecto",
                column3: "Horas contratadas",
                column4: "Fecha contratacion",
                column5: "Cliente",
                column6: "Socio",
                column7: "Gerente",
                column8: "Estatus",
            },
            selectSearch: {
                select1: "Código",
                select2: "Proyecto",
                select3: "Socio",
                select4: "Gerente",
                select5: "Cliente",
                select6: "Estatus",
            },
            tableTarget: "projects",
            controlProjectModal: {}, //Controla el modal para proyectos
            previewProjectInfo: null, //Array informativo del proyecto
            /** Objeto que controla el pogreso de la barra
             * class: clase de la barra
             * style: controla la anchura de la barra
             * percent: define el porcentaje de la barra
             * department_id: departamento asignado
             * department_name: nombre del departamento asignado
             * loadHour: Hora cargada
             * estimatedHour: Hora estimada
             */
            progressBarInfo: [],
            viewCreate: false,
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
        configSettings(accessSession) {
            //Verificamos los permisos
            if (accessSession.projectP == 1) {
                this.projectsColumns.settings = {
                    columnS1: "Editar",
                    ...this.projectsColumns.settings,
                };
                //Activamos permisos de visualizacion
                this.viewCreate = true;
            }
            //Verificamos si puede cerrar proyectos
            if (accessSession.closeP == 1) {
                this.projectsColumns.settings = {
                    ...this.projectsColumns.settings,
                    columnS3: "Cierre de proyecto",
                };
            }
        },
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
        closeProject(idProject) {
            const paramsDTO = { codigoSQL: idProject };
            const routesDTO = {
                post: "/projects/closure/prepare-info",
                redirect: "/projects/closure",
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
                    //Asignamos las horas estimadas y cargadas por proyecto
                    this.showProgressHours(this.previewProjectInfo);
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
