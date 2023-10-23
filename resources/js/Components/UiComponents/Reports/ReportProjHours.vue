<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de horas a proyectos" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteHorasCargables.xls"
            status-table="usuarios" :is-admin="scope.controlAdmin" :area-id="scope.departmentId">
        </ListingCrud>
    </div>
</template>
<script>
import ListingCrud from '@/Components/ListingCrud.vue';
import Loading from '@/Components/Loading.vue';
import { globalMethodsReport } from './GlobalReportMethods';
export default {
    props: {
        scope: Object //Importa la data del padre
    },
    emits: ['update-mounted'],
    data() {
        return {
            reportColumns: {
                column1: 'Nombre',
                column2: 'Código',
                column3: 'Area',
                column4: "Proyecto",
                column5: 'Fecha de contratación',
                column6: 'Total horas cargadas',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Proyecto",
                select3: "Area",
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            isListMounted: false
        }
    },
    mounted() {
        this.isListMounted = false;
        this.$emit('update-mounted', this.isListMounted)
        //Cargamos la informacion
        axios.post("/reports/list-proy-hours", { startDate: this.scope.dateStart, endDate: this.scope.dateEnd })
            .then(request => {
                this.isListMounted = true;
                this.$emit('update-mounted', this.isListMounted)
                //Acomodamos el array a enviar
                request.data.message.forEach(projectInfo => {
                    this.directiveList.push({
                        nombre: projectInfo.user_name,
                        "código": projectInfo.user_code,
                        area: projectInfo.department_name,
                        proyecto: projectInfo.project_name,
                        contrato: projectInfo.hiring_date,
                        horas_registradas: this.formatReportNumber(projectInfo.register_hour)
                    })
                })
                //Acomodamos la longitud minima y su paginacion
                if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                this.directivePaginatio = Math.ceil(
                    this.directiveList.length / this.directiveLength
                );
            })
            .catch(error => console.error(error))
    },
    components: { ListingCrud, Loading },
    mixins: [globalMethodsReport]
}
</script>
