import { createApp } from "vue/dist/vue.esm-bundler";

const reportsApp = createApp({
    data() {
        return {};
    },
    methods: {},
    computed: {},
    watch: {},
});

if (document.getElementById("section-reports") !== null) {
    reportsApp.mount("#section-reports");
    window.location.hash = "#05";
}
