<template>
    <div class="reports-container">
        <div class="reports-container-select">
            <div class="input-group">
                <select class="form-select" title="MunicipalitySelect" v-model="selectReport" :disabled="!isMounted">
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
    <!-- Reporte de proyectos -->
    <div v-if="selectReport == 5">
        <ReportProjectsLog v-if="reportPermission.rproyectosP == 1" :scope="migrateData" :key="isMounted">
        </ReportProjectsLog>
        <div v-else class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
    <div v-else-if="selectReport == 5 && reportPermission.rproyectosP != 1" class="not-found">
        <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
    </div>
    <!-- Reporte de usuarios -->
    <div v-if="selectReport == 8">
        <ReportUsers v-if="reportPermission.rusersP == 1" :scope="migrateData" :key="isMounted">
        </ReportUsers>
        <div v-else class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
    <div v-else-if="selectReport == 8 && reportPermission.rusersP != 1" class="not-found">
        <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
    </div>
    <!-- Reporte historico de horas -->
    <div v-if="selectReport == 9">
        <ReportHistoryLog v-if="reportPermission.rlogUsersP == 1" :scope="migrateData" @update-mounted="isMounted = $event">
        </ReportHistoryLog>
        <div v-else class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
    </div>
    <div v-else-if="selectReport == 8 && reportPermission.rlogUsersP != 1" class="not-found">
        <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
    </div>
    <!-- Reporte horas no cargables -->
    <div v-if="selectReport > 2 && selectReport != 5 && selectReport != 8 && selectReport != 9" class="reports-container">
        <!-- Fechas  -->
        <span class="reports-container-title">Ingrese el intervalo de fechas</span>
        <div class="reports-container-search">
            <div class="input-group mb-3">
                <span class="input-group-text">Fecha desde</span>
                <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="start_date"
                    v-model="dateStart" disabled />
                <span class="input-group-text" for="calendar">
                    <calendar @to-input="dateSearch($event, 'start')" :key="dateStart"></calendar>
                </span>
            </div>
            <div class="input-group mb-3">
                <span v-if="dateStart.length != 0" class="input-group-text">Fecha Hasta</span>
                <input v-if="dateStart.length != 0" type="text" class="form-control" placeholder="Ejemplo: 1990-02-18"
                    id="end_date" v-model="dateEnd" disabled />
                <span v-if="dateStart.length != 0" class="input-group-text" for="calendar">
                    <calendar @to-input="dateSearch($event, 'end')"></calendar>
                </span>
            </div>
        </div>
        <!-- Reporte horas administrativas -->
        <ReportAdminHours class="reports-container-list"
            v-if="selectReport == 3 && reportPermission.rhorasP == 1 && dateEnd.length != 0" :scope="migrateData"
            :key="dateEnd" @update-mounted="isMounted = $event">
        </ReportAdminHours>
        <div v-else-if="selectReport == 3 && reportPermission.rhorasP != 1" class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
        <!-- Reporte directivo acumulado -->
        <ReportDirectiveTotal class="reports-container-list"
            v-if="selectReport == 4 && reportPermission.rdirectiveAP == 1 && dateEnd.length != 0" :scope="migrateData"
            :key="dateEnd" @update-mounted="isMounted = $event">
        </ReportDirectiveTotal>
        <div v-else-if="selectReport == 4 && reportPermission.rdirectiveAP != 1" class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
        <!-- Reporte personas por cargar -->
        <ReportNoRegisterHour class="reports-container-list"
            v-if="selectReport == 6 && reportPermission.rnoRegisterP == 1 && dateEnd.length != 0" :scope="migrateData"
            :key="dateEnd" @update-mounted="isMounted = $event">
        </ReportNoRegisterHour>
        <div v-else-if="selectReport == 6 && reportPermission.rnoRegisterP != 1" class="not-found">
            <div class="badge bg-warning text-dark">{{ notFoundMessage }}</div>
        </div>
        <!-- Reporte horas a proyecto -->
        <ReportProjHours class="reports-container-list"
            v-if="selectReport == 7 && reportPermission.rproyP == 1 && dateEnd.length != 0" :scope="migrateData"
            :key="dateEnd" @update-mounted="isMounted = $event">
        </ReportProjHours>
        <div v-else-if="selectReport == 7 && reportPermission.rproyP != 1" class="not-found">
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
import ReportProjectsLog from '@/Components/UiComponents/Reports/ReportProjectsLog.vue';
import ReportNoRegisterHour from '@/Components/UiComponents/Reports/ReportNoRegisterHour.vue';
import ReportProjHours from '@/Components/UiComponents/Reports/ReportProjHours.vue';
import ReportUsers from '@/Components/UiComponents/Reports/ReportUsers.vue';
import ReportHistoryLog from '@/Components/UiComponents/Reports/ReportHistoryLog.vue';
export default {
    props: {
        listReports: Array, //Lista de reportes que se abstraen desde la base de datos
        reportPermission: Object, //Array de permisos
        isAdmin: Number, //Captura si el usuario es administrador o no
        areaId: Number //Captura el id del departamento de la persona
    },

    data() {
        return {
            selectReport: 0, //Entero que captura la informacion del reporte seleccionado
            isMounted: true, //Desactiva el loading cuando carga el componente
            lengthColumns: 50,
            maxLengthPagination: 0, //Controlan la páginación
            listData: [], //Object que almacena la data de los usuarios a mostrar en la lista
            listIntervalData: [], //Object que almacena la data de horas en funcion de un intervalo
            dateStart: "", //Fecha inicial
            dateEnd: "", //Fecha final
            notFoundMessage: "La visualización de este reporte requiere elevación. Comuníquese con el administrador del sistema",
            controlAdmin: 0,
            departmentId: 0
        }
    },
    created() {
        //Pasamos la informacion del id del departamento y si es el administrador del sistema
        this.controlAdmin = this.isAdmin;
        this.departmentId = this.areaId;
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
                case 5:
                    this.getTable('/reports/list-logs-projects')
                    break;
                case 8:
                    this.getTable('/reports/list-users')
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
        }
    },
    computed: {
        migrateData() { return this.$data }
    },
    components: {
        ReportClosureProject,
        ReportDirectiveMonth,
        ReportAdminHours,
        Calendar,
        ReportDirectiveTotal,
        ReportProjectsLog,
        ReportNoRegisterHour,
        ReportProjHours,
        ReportUsers,
        ReportHistoryLog
    }
}
</script>
