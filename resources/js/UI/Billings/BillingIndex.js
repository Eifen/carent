import { createApp } from "vue";
import { CrudUi, componentsUI, dataUI, methodsUI, watchUI } from "../UIConfig";

//App
const billingApp = createApp({
    data() {
        return {
            billingsColumns: {
                column1: "Código",
                column2: "Proyecto",
                column3: "Cliente",
                column4: "Fecha contratacion",
                column5: "Monto del proyecto",
                column6: "Estatus",
                settings: {
                    columnS1: "Informacion de facturacion",
                },
            },
            selectSearch: {
                select2: "Proyecto",
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
        CrudUi.getTable("/billings/all-projects", this);
    },
    methods: {
        /**
         * Metodo que crea una instancia en Sessión para pasarla temporalmente la pantalla de facturacion
         * @param {*} idProject Almacena la ID seleccionada de la lista de projects
         */
        infoBilling(idProject) {
            const paramsDTO = { codigoSQL: idProject };
            const routesDTO = {
                post: "/billings/loading-project",
                redirect: "/billings/control",
            };
            //Llamamos al método Static que hace la consulta Axios
            CrudUi.enableEdit(routesDTO, paramsDTO);
        },
    },
    computed: {},
    watch: {},
    mixins: [componentsUI, methodsUI, watchUI, dataUI],
});

//Detectamos el ID
if (document.getElementById("billing-project") !== null) {
    billingApp.mount("#billing-project");
    window.location.href = "#04";
}
