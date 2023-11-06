<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte directivo mensual" not-found-message="No hay horas cargadas" :select-search="selectSearch"
            view-search view-excel title-excel="ReporteDirectivoMensual.xls"
            title-resume-excel="ResumenDirectivoMensual.xls" title-consolidated-excel="ConsolidadoDirectivoMensual.xls"
            status-table="usuarios" view-hours directive :is-admin="scope.controlAdmin" :area-id="scope.departmentId">
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
    data() {
        return {
            reportColumns: {
                column1: 'Nombre',
                column2: 'Area',
                column3: 'Correo',
                column4: "Nivel",
                column5: "% Carga proy",
                column6: "% Carga admon",
                column7: "Hor Esp proy",
                column8: "Hor Esp admon",
                column9: 'Ref Total',
                column10: "Eval",
                column11: 'Horas Proy',
                column12: '% Proy',
                column13: 'Horas Admon',
                column14: '% Horas Admon',
                column15: 'Total horas',
                column16: '% Carga total',
                column17: 'Estatus',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Estatus",
                select3: "Mes",
                select4: "Area"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
        }
    },
    mounted() {
        //Acomodamos el array resultante de la informacion de carga de usuarios
        if (this.scope.isMounted) {
            this.scope.listData.forEach(user => {
                user.forEach(period => {
                    const refTotal = period.ref_total.replace(/\./g, "").replace(",", ".");
                    this.directiveList.push({
                        nombre: period.nombre,
                        area: period.area,
                        correo: period.correo,
                        nivel: period.nivel,
                        mes: period.mes,
                        "%_carga_min_proy": this.formatReportNumber(period.percen_carg),
                        "%_carga_min_admon": this.formatReportNumber(100 - period.percen_carg),
                        hor_esp_proy: this.formatReportNumber((refTotal * (period.percen_carg / 100))),
                        hor_esp_admon: this.formatReportNumber((refTotal * ((100 - period.percen_carg) / 100))),
                        hor_ref: period.ref_total,
                        eval: period.eval,
                        tot_hor_proy: period.proy_hours,
                        "%_hor_proy": period.percen_proy,
                        tot_hor_admon: period.admin_hours,
                        "%_hor_admon": period.percen_admon,
                        tot_hor: period.total_hours,
                        "%_tot_hor": period.percen_total,
                        estatus: period.estatus,
                        fecha_egreso: period.fecha_egreso
                    });
                });
            });

            //Ordenamos la lista por fechas
            this.directiveList.sort((mes1, mes2) => {
                //Convertimos la cadena a una Date
                const dateA = new Date(mes1.mes);
                const dateB = new Date(mes2.mes);
                //Comparamos
                return dateB.getFullYear() - dateA.getFullYear() || dateB.getMonth() - dateA.getMonth();
            });

            //Acomodamos la longitud minima y su paginacion
            if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
            this.directivePaginatio = Math.ceil(
                this.directiveList.length / this.directiveLength
            );
        }
    },
    mixins: [globalMethodsReport],
    components: { ListingCrud, Loading }
}
</script>
