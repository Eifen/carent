<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de facturas" not-found-message="No hay facturas emitidas" :select-search="selectSearch"
            view-search view-excel title-excel="ReporteFacturas.xls">
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
                column1: 'Fecha',
                column2: 'Referencia',
                column3: 'Cliente',
                column4: 'Monto',
                column5: 'Socio',
            },
            selectSearch: {
                select1: "Socio",
                select2: "Cliente",
                select3: "Referencia"
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
        axios.post("/reports/list-billings", { start: this.scope.dateStart, end: this.scope.dateEnd })
            .then(request => {
                this.isListMounted = true;
                this.$emit('update-mounted', this.isListMounted)
                //Acomodamos el array a enviar
                request.data.message.forEach(billingInfo => {
                    this.directiveList.push({
                        fecha: billingInfo.billing_date,
                        referencia: billingInfo.billing_number,
                        cliente: billingInfo.bussiness_name,
                        monto: this.formatReportNumber(billingInfo.billing_value),
                        socio: billingInfo.partner_name,
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
