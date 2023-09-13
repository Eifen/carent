<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte directivo mensual" not-found-message="No hay horas cargadas" :select-search="selectSearch"
            view-search view-excel title-excel="ReporteDirectivoMensual.xls" status-table="usuarios" view-hours
            :hours-estimated="directiveList[0].ref_estimated">
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
                column2: 'Cargo',
                column3: 'Area',
                column4: 'Horas Proy',
                column5: '% Proy',
                column6: 'Horas Admon',
                column7: '% Horas Admon',
                column8: 'Total horas',
                column9: '% Carga total',
                column10: 'Ref Total',
                column11: 'Estatus',
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
                    this.directiveList.push(period);
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
