import { createApp } from 'vue/dist/vue.esm-bundler';
import { componentsUI, dataUI, CrudUi, methodsUI, watchUI } from './UIConfig';
import { Chart } from 'chart.js';


const homeApp = createApp({
    data() {
        return {}
    },
    mounted() {
        //Llamamos a la informacion del usuario
        CrudUi.getTable("/log-user-info", this)
    },
    methods: {},
    computed: {},
    watch: {
        listData() {
            console.log(this.listData)
        }
    },
    mixins: [componentsUI, dataUI, methodsUI, watchUI]
});

if (document.getElementById("home-page") !== null) {
    homeApp.mount("#home-page")
}
