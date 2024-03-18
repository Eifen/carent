import { createApp } from "vue/dist/vue.esm-bundler";
import {
    methodsUI,
    dataUI,
    watchUI,
    componentsUI,
    CrudUi,
} from "../../UIConfig";

import EvaluationFormTwo from "@/Components/UiComponents/Evaluations/EvaluationFormTwo.vue";

const formApp = createApp({
    // data() {
    //     return {};
    // },

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
    components: { EvaluationFormTwo },
    computed: {},
    watch: {},
});

if (document.getElementById("section-evaluations-form-two") !== null) {
    formApp.mount("#section-evaluations-form-two");
    window.location.hash = "#06";
}
