<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte directivo mensual" not-found-message="No hay horas cargadas" :select-search="selectSearch"
            view-search view-excel title-excel="ReporteDirectivoMensual.xls" status-table="usuarios" view-hours directive>
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
                column1: 'Nombre',
                column2: 'Area',
                column3: "Nivel",
                column4: "% Carga proy",
                column5: "% Carga admon",
                column6: "Hor Esp proy",
                column7: "Hor Esp admon",
                column8: 'Ref Total',
                column9: "Eval",
                column10: 'Horas Proy',
                column11: '% Proy',
                column12: 'Horas Admon',
                column13: '% Horas Admon',
                column14: 'Total horas',
                column15: '% Carga total',
                column16: 'Estatus',
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
                        nivel: period.nivel,
                        mes: period.mes,
                        "%_carga_min_proy": Number(period.percen_carg).toLocaleString("de-DE"),
                        "%_carga_min_admon": Number(100 - period.percen_carg).toLocaleString("de-DE"),
                        hor_esp_proy: Number((refTotal * (period.percen_carg / 100)).toFixed(2)).toLocaleString("de-DE"),
                        hor_esp_admon: Number((refTotal * ((100 - period.percen_carg) / 100)).toFixed(2)).toLocaleString("de-DE"),
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
    components: { ListingCrud, Loading }
}
</script>
