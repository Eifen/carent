import { createApp } from 'vue';
import { CrudUi,componentsUI,dataUI,methodsUI,watchUI } from '../UIConfig';

//TODO Realizar proceso para projectos
const projectsIndex = createApp({
    data(){
        return {
            projectsColumns: {
                "column1": "Codigo",
                "column2": "Proyecto",
                "column3": "Horas contratadas",
                "column4": "Fecha contratacion",
                "column5": "Cliente",
                "column6": "Socio",
                "column7": "Gerente",
                "column8": "Estatus",
                "settings": {"columnS1":"Editar"}
            },
            selectSearch: {
                "select1": "Proyecto",
                "select2": "Cliente",
                "select3": "Estatus",
                "select4": "Divisiones"
            },
            tableTarget: "projects"
        }
    },
    created(){
        //Hacemos el llamado al método estatico
        CrudUi.limitPagData(this,this.tableTarget,this.lengthColumns)
    },
    mounted(){ CrudUi.getTable('/projects/all-projects',this) },
    mixins: [componentsUI, methodsUI, watchUI, dataUI]
});

if(document.getElementById('section-projects') !== null){
    projectsIndex.mount('#section-projects');
    window.location.hash = '#03';
}