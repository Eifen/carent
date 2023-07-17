import { createApp } from "vue/dist/vue.esm-bundler";
import CloseProject from "@/Components/UiComponents/CloseProjects/CloseProject.vue";
const closeApp = createApp({
    data() {
        return {};
    },
    methods: {},
    components: { CloseProject },
    computed: {},
    watch: {},
});

if (document.getElementById("close-project") !== null) {
    closeApp.mount("#close-project");
    window.location.hash = "#03";
}
