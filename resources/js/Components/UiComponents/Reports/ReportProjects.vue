<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted" :title-object="reportColumns"
            :pagination-lenght="scope.maxLengthPagination" :pagination-limit="scope.lengthColumns"
            :table-info="scope.listData" title-table="Reporte cierre de proyectos"
            not-found-message="No hay proyectos cerrados" :select-search="selectSearch" view-search view-excel
            :info-excel="scope.listData" title-excel="ReporteCierreDeProyectos.xls">
        </ListingCrud>
    </div>
</template>
<script>
import ListingCrud from '@/Components/ListingCrud.vue';
import Loading from '@/Components/Loading.vue';
export default {
    props: {
        scope: Object //Importa la data del padre
    },
    emits: ["update-mounted"],
    data() {
        return {
            reportColumns: {
                column1: 'Código',
                column2: 'Gerente',
                column3: 'Socio',
                column4: 'Fecha de cierre',
                column5: 'Proyecto',
                column6: 'Horas estimadas',
                column7: 'Honorarios estimados',
                column8: 'Tasa promedio',
                column9: 'Horas adicionales',
                column10: 'Honorarios adicionales',
                column11: 'Tasa promedio total',
                column12: 'Horas Reales',
                column13: 'Honorarios Reales',
                column14: 'Tasa promedio real',
            },
            selectSearch: {
                select1: "Código",
                select2: "Proyecto",
                select3: "Gerente",
                select4: "Socio",
                select5: "Fecha desde",
                select6: "Fecha hasta",
            },
        }
    },
    mounted() {
    },
    components: { ListingCrud, Loading }
}
</script>
