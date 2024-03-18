import { createApp } from "vue/dist/vue.esm-bundler";
import CloseProject from "@/Components/UiComponents/CloseProjects/CloseProject.vue";
import {
    methodsUI,
    dataUI,
    watchUI,
    componentsUI,
    CrudUi,
} from "../../UIConfig";

import EvaluationForm from "@/Components/UiComponents/Evaluations/EvaluationForm.vue";

const evaluationsForm = createApp({
    data() {
        return {
            selectSearch: {
                select1: "Proyecto",
            },
        };
    },

    // created() {
    //     //Llamamos a la sesion
    //     this.getSession("/projects/delete-update-data");
    // },
    //se transpasa, se carga y se elimina las sesiones
    //Mixin: fragmento de codigo que adquiere las propiedades del componente,
    // en este caso: adquiero las propiedades al importar esa constante methosUI
    methods: {
        // closeProject(params) {
        //     this.isClick = true;
        //     CrudUi.controlCrud(
        //         {
        //             post: "/projects/closure/submit-close",
        //             redirect: "/projects",
        //             self: this,
        //         },
        //         params
        //     );
        // },
    },
    mixins: [methodsUI, dataUI, watchUI, componentsUI],
    components: { EvaluationForm },
    computed: {},
    watch: {},
});

if (document.getElementById("section-evaluations-form") !== null) {
    formApp.mount("#section-evaluations-form");
    window.location.hash = "#06";
}
