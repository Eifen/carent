<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted" :title-object="reportColumns"
            :pagination-lenght="scope.maxLengthPagination" :pagination-limit="scope.lengthColumns"
            :table-info="directiveList" title-table="Reporte cierre de proyectos"
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
    data() {
        return {
            reportColumns: {
                column1: 'Código',
                column2: 'Gerente',
                column3: 'Socio',
                column4: 'Fecha de cierre',
                column5: 'Proyecto',
                column6: 'Horas estimadas',
                column7: 'Moneda',
                column8: 'Honorarios estimados',
                column9: 'Tasa promedio',
                column10: 'Horas adicionales',
                column11: 'Honorarios adicionales',
                column12: 'Tasa promedio total',
                column13: 'Horas Reales',
                column14: 'Honorarios Reales',
                column15: 'Tasa promedio real',
                column16: 'Diferencial facturado',
                column17: 'Diferencial tasa'
            },
            selectSearch: {
                select1: "Código",
                select2: "Proyecto",
                select3: "Gerente",
                select4: "Socio",
                select5: "Fecha desde",
                select6: "Fecha hasta",
            },
            directiveList: []
        }
    },
    mounted() {
        console.log(this.scope.listData)
        if (this.scope.isMounted) {
            let listDTO = this.scope.listData.reduce((acum, closureInfo, pos) => {
                const key = closureInfo['código']

                if (!acum[key]) {
                    acum[key] = {
                        ...closureInfo,
                        dif_factu: Number(parseFloat(closureInfo.honorarios_reales) - (parseFloat(closureInfo.honorarios_estimados) + parseFloat(closureInfo.honorarios_adicionales))).toLocaleString('de-DE'),
                        dif_tasa: Number(parseFloat(closureInfo.tasa_promedio_real) - parseFloat(closureInfo.tasa_promedio_total)).toLocaleString('de-DE'),
                    }
                }

                return acum;
            }, {})

            console.log(listDTO)
            this.directiveList = Object.values(listDTO).sort((a, b) => b['código'] - a['código'])
        }
    },
    components: { ListingCrud, Loading }
}
</script>
