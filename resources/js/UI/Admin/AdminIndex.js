import { createApp } from "vue/dist/vue.esm-bundler";
import { CrudUi, dataUI, watchUI, componentsUI, methodsUI } from "../UIConfig";

const adminApp = createApp({
    data() {
        return {
            dateStart: "", //Fecha inicial
            dateEnd: "", //Fecha final
        };
    },
    mounted() {
        //Llamamos a la lista de reportes
        CrudUi.getTable("/reports/list-reports", this);
    },
    methods: {
        /**
 * Metodo que registra la fecha en los respectivos cambios
 * @param {String} dateSelect Fecha seleccionada en formato YYY-mm-dd
 * @param {String} type Tipo de fecha, si inicial o final
 */
        dateSearch(dateSelect, type) {
            switch (type) {
                case 'start':
                    this.dateStart = `${dateSelect.year}-${dateSelect.month}-${dateSelect.day}`
                    break;
                case 'end':
                    this.dateEnd = `${dateSelect.year}-${dateSelect.month}-${dateSelect.day}`
                    break;
            }
        }
    },
    computed: {},
    watch: {},
    mixins: [dataUI, watchUI, componentsUI, methodsUI],
});

if (document.getElementById("admin-index") !== null) {
    adminApp.mount("#admin-index");
}
