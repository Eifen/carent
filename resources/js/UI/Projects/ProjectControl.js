import { createApp } from "vue/dist/vue.esm-bundler";
import FormProjects from "../../Components/UiComponents/Projects/FormProjects.vue";
import { componentsUI, methodsUI, CrudUi } from "../UIConfig";

const clientsControl = createApp({
    data() {
        return {
            isMounted: false, //Controla el estado del componente
            isClick: false, //Controla el estado del boton
            updateModel: {}, //Objeto encargado de inicializar la data del edit
            paramsDTOProjects: {
                projectDescription: "",
                clientId: 0,
                statusId: 0,
                managerId: 0,
                partnerId: 0,
                qualityPartnerId: 0,
                currencyId: 0,
                companyId: 0,
                hiringDate: "",
                departments: [],
                projectValue: 0,
                averageRate: 0,
            }, //Objeto para el create
            paramsDTOEdit: {
                projectId: 0,
                additionalHours: [],
                additionalValues: [],
            }, //Objeto para el edit
        };
    },
    methods: {
        /**
         * Metodo que crea un nuevo Proyecto
         * @param {*} dataParams Recibe la data que proviene de formulario
         */
        newProject(dataParams) {
            this.isClick = true;
            this.paramsDTOProjects = dataParams;
            //AXIOS Create Project
            const paramsToPost = {
                project: JSON.parse(JSON.stringify(this.paramsDTOProjects)),
                isEdit: false,
            };
            const routesSelfDTO = {
                post: "/projects/create/new-project",
                redirect: "/projects",
                self: this,
            };
            CrudUi.controlCrud(routesSelfDTO, paramsToPost);
        },
        /**
         * Actualiza un cliente
         * @param {Object} dataParams Objeto con la información a actualizar
         */
        updateProject(dataParams) {
            this.isClick = true;
            //Pasamos la data Proyectos
            //Asociamos los parametros del Edit
            this.paramsDTOEdit = dataParams;

            //AXIOS Update Project
            const paramsToPost = {
                project: JSON.parse(JSON.stringify(this.paramsDTOEdit)),
                isEdit: true,
            };
            const routesSelfDTO = {
                post: "/projects/update/update-project",
                redirect: "/projects",
                self: this,
            };
            CrudUi.controlCrud(routesSelfDTO, paramsToPost);
        },
    },
    components: { FormProjects },
    mounted() {
        setTimeout(() => {
            this.isMounted = true;
        }, 300);
    },
    mixins: [componentsUI, methodsUI],
});

if (
    document.getElementById("create-project") !== null ||
    document.getElementById("update-project") !== null
) {
    if (document.getElementById("create-project") !== null)
        clientsControl.mount("#create-project");
    if (document.getElementById("update-project") !== null)
        clientsControl.mount("#update-project");
    window.location.hash = "#03";
}
