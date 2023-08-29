<template>
    <div class="reports-container">
        <div class="reports-container-select">
            <div class="input-group">
                <select class="form-select" title="MunicipalitySelect" v-model="selectReport">
                    <option value=0 selected disabled>Seleccione el reporte</option>
                    <option v-for="(select, cursor) in listReports" :key="cursor" :value="select.report_id">
                        <span>{{ select.report_description }}</span>
                    </option>
                </select>
            </div>
        </div>
    </div>
    <div v-if="selectReport == 1">
        <ReportClosureProject :scope="migrateData" :key="isMounted"></ReportClosureProject>
    </div>
    <div v-if="selectReport == 2">
        <ReportDirectiveInter :scope="migrateData" :key="isMounted"></ReportDirectiveInter>
    </div>
</template>
<script>
//Importamos los tipos de reportes
import ReportClosureProject from '@/Components/UiComponents/Reports/ReportClosureProject.vue';
import ReportDirectiveInter from '@/Components/UiComponents/Reports/ReportDirectiveInter.vue';
export default {
    props: {
        listReports: Array //Lista de reportes que se abstraen desde la base de datos
    },

    data() {
        return {
            selectReport: 0, //Entero que captura la informacion del reporte seleccionado
            isMounted: false, //Desactiva el loading cuando carga el componente
            lengthColumns: 50,
            maxLengthPagination: 0, //Controlan la páginación
            listData: [], //Object que almacena la data de los usuarios a mostrar en la lista
        }
    },
    methods: {
        /**
         * Metodo que se encarga de traer del modelo toda la información de la tabla seleccionada en función de su ruta. Va en el mounted
         * @param {String} route Almacena la URL que debe hacer request Axios
         */
        getTable(route) {
            this.isMounted = false
            //Cargamos toda la data
            axios
                .post(route)
                .then((request) => {
                    if (request.status === 200 && !request.data.response)
                        throw request.data.message;
                    //Si no se activa la exceptión, asignamos el objeto
                    setTimeout(() => {
                        this.listData = request.data.message;
                        //Preparamos la paginación en función del tamaño del array resultante
                        if (this.listData.length < 50) {
                            this.lengthColumns = this.listData.length;
                        }
                        this.maxLengthPagination = Math.ceil(
                            this.listData.length / this.lengthColumns
                        );
                    }, 300);
                })
                .catch((error) => {
                    console.error(error);
                });
        }
    },
    watch: {
        selectReport(newSelect) {
            switch (newSelect) {
                case 1:
                    this.getTable('/reports/list-closure-projects')
                    break;
                // case 2:
                //     this.getTable('/reports/list-directive-inter')
                //     break;
            }
        },
        listData() {
            this.isMounted = true
        }
    },
    computed: {
        migrateData() { return this.$data }
    },
    components: { ReportClosureProject, ReportDirectiveInter }
}
</script>
