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
    <!-- Reporte de cierra de proyectos -->
    <div v-if="selectReport == 1">
        <ReportClosureProject v-if="reportPermission.rclosureP == 1" :scope="migrateData" :key="isMounted">
        </ReportClosureProject>
        <div v-else class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
    <!-- Reporte diretivo mensual -->
    <div v-if="selectReport == 2">
        <ReportDirectiveMonth v-if="reportPermission.rdirectiveMP == 1" :scope="migrateData" :key="isMounted">
        </ReportDirectiveMonth>
        <div v-else class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
    <!-- Reporte horas no cargables -->
    <div v-if="selectReport > 2" class="reports-container">
        <!-- Fechas  -->
        <span class="reports-container-title">Ingrese el intervalo de fechas</span>
        <div class="reports-container-search">
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">Fecha desde</span>
                <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="start_date"
                    aria-describedby="basic-addon1" v-model="dateStart" disabled />
                <span class="input-group-text" for="calendar">
                    <calendar @to-input="dateSearch($event, 'start')"></calendar>
                </span>
            </div>
            <div class="input-group mb-3">
                <span v-if="dateStart.length != 0" class="input-group-text" id="basic-addon3">Fecha Hasta</span>
                <input v-if="dateStart.length != 0" type="text" class="form-control" placeholder="Ejemplo: 1990-02-18"
                    id="end_date" aria-describedby="basic-addon3" v-model="dateEnd" disabled />
                <span v-if="dateStart.length != 0" class="input-group-text" for="calendar">
                    <calendar @to-input="dateSearch($event, 'end')"></calendar>
                </span>
            </div>
        </div>
        <!-- Reporte horas administrativas -->
        <ReportAdminHours class="reports-container-list"
            v-if="selectReport == 3 && reportPermission.rhorasP == 1 && listIntervalData.length != 0" :scope="migrateData"
            :key="listIntervalData">
        </ReportAdminHours>
        <div v-else-if="reportPermission.rhorasP != 1" class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
        <!-- Reporte directivo acumulado -->
        <ReportDirectiveTotal class="reports-container-list"
            v-if="selectReport == 4 && reportPermission.rdirectiveAP == 1 && dateEnd.length != 0" :scope="migrateData"
            :key="dateEnd">
        </ReportDirectiveTotal>
        <div v-else-if="reportPermission.rdirectiveAP != 1" class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
</template>
<script>
//Importamos los tipos de reportes
import ReportClosureProject from '@/Components/UiComponents/Reports/ReportClosureProject.vue';
import ReportDirectiveMonth from '@/Components/UiComponents/Reports/ReportDirectiveMonth.vue';
import ReportAdminHours from '@/Components/UiComponents/Reports/ReportAdminHours.vue';
import ReportDirectiveTotal from '@/Components/UiComponents/Reports/ReportDirectiveTotal.vue';
import Calendar from '@/Components/Calendar.vue';
export default {
    props: {
        listReports: Array, //Lista de reportes que se abstraen desde la base de datos
        reportPermission: Object //Array de permisos
    },

    data() {
        return {
            selectReport: 0, //Entero que captura la informacion del reporte seleccionado
            isMounted: false, //Desactiva el loading cuando carga el componente
            lengthColumns: 50,
            maxLengthPagination: 0, //Controlan la páginación
            listData: [], //Object que almacena la data de los usuarios a mostrar en la lista
            listIntervalData: [], //Object que almacena la data de horas en funcion de un intervalo
            dateStart: "", //Fecha inicial
            dateEnd: "", //Fecha final
            notFoundMessage: "La visualización de este reporte requiere elevación. Comuníquese con el administrador del sistema"
        }
    },
    created() {
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
        },
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
    watch: {
        selectReport(newSelect) {
            this.isMounted = false
            this.dateStart = ""
            this.dateEnd = ""
            switch (newSelect) {
                case 1:
                    this.getTable('/reports/list-closure-projects')
                    break;
                case 2:
                    this.getTable('/reports/list-directive-month')
                    break;
                case 3:
                    this.getTable('/reports/list-admin-hours')
                    break;
            }
        },
        listData() {
            this.isMounted = true
        },
        dateStart() {
            //Borramos la fecha final
            this.dateEnd = ""
        },
        dateEnd(newDate, oldDate) {
            //Definimos la fecha inicial
            const starDate = this.dateStart.length != 0 ? new Date(this.dateStart) : null
            //Determinamos cada caso de intervalos
            switch (true) {
                //Reporte de horas administrativas
                case newDate.length != 0 && this.selectReport == 3:
                    const endDate = new Date(newDate);
                    //Filtramos el array resultante por fecha
                    if (starDate.getTime() <= endDate.getTime()) {
                        this.listIntervalData = this.listData.filter(data => {
                            let dateToSearch = new Date(data.register_date)
                            return dateToSearch.getTime() >= starDate.getTime() && dateToSearch.getTime() <= endDate.getTime();
                        })
                    } else {
                        this.dateEnd = oldDate
                    }
                    break;
            }
        }
    },
    computed: {
        migrateData() { return this.$data }
    },
    components: { ReportClosureProject, ReportDirectiveMonth, ReportAdminHours, Calendar, ReportDirectiveTotal }
}
</script>
