import { createApp } from "vue/dist/vue.esm-bundler";
import CloseProject from "@/Components/UiComponents/CloseProjects/CloseProject.vue";
import { methodsUI, dataUI, watchUI, componentsUI } from "../../UIConfig";

const closeApp = createApp({
    data() {
        return {};
    },

    created() {
        //Llamamos a la sesion
        this.getSession("/projects/delete-update-data");
    },
    //se transpasa, se carga y se elimina las sesiones
    //Mixin: fragmento de codigo que adquiere las propiedades del componente,
    // en este caso: adquiero las propiedades al importar esa constante methosUI
    methods: {},
    mixins: [methodsUI, dataUI, watchUI, componentsUI],
    components: { CloseProject },
    computed: {},
    watch: {},
});

if (document.getElementById("close-project") !== null) {
    closeApp.mount("#close-project");
    window.location.hash = "#03";
}
