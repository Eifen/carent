<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de personas por cargar" not-found-message="No hay usuarios faltos de carga"
            :select-search="selectSearch" view-search view-excel title-excel="ReportePersonasPorCargar.xls"
            status-table="usuarios">
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
                column2: 'Area',
                column3: "Cargo",
                column4: 'Horas Proy',
                column5: 'Horas Admon',
                column6: 'Fecha de egreso',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Area"
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
        axios.post("reports/list-no-register-hour", { start: this.scope.dateStart, end: this.scope.dateEnd })
            .then(request => {
                this.isListMounted = true
                this.$emit('update-mounted', this.isListMounted)
                //Recorremos el array del reporte
                request.data.message.forEach(infoUser => {
                    this.directiveList.push({
                        empleado: infoUser.user_name,
                        area: infoUser.department_name,
                        cargo: infoUser.position_name,
                        tot_hor_admon: this.formatReportNumber(infoUser.admon_register),
                        tot_hor_proy: this.formatReportNumber(infoUser.proj_register),
                        fecha_egreso: infoUser.departure_date === null ? "activo" : infoUser.departure_date
                    })
                })
                //Acomodamos la longitud minima y su paginacion
                if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                this.directivePaginatio = Math.ceil(
                    this.directiveList.length / this.directiveLength
                );
            }).catch(error => console.error(error))
    },
    mixins: [globalMethodsReport],
    components: { ListingCrud, Loading }
}
</script>
