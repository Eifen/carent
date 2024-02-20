<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de informacion de facturas" not-found-message="No hay facturas emitidas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteFacturasProj.xls">
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
                column2: 'Cliente',
                column3: 'Proyecto',
                column4: 'SocioC',
                column5: 'SocioP',
                column6: 'Monto propuesto',
                column7: 'Monto cobrado',
                column8: 'Estatus',
            },
            selectSearch: {
                select1: "Cliente",
                select2: "Proyecto",
                select3: "Estatus",
                select4: "SocioC",
                select5: "SocioP"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            isListMounted: false,
            arrayDTO: [] //Almacena el array de facturas
        }
    },
    mounted() {
        this.isListMounted = false;
        this.$emit('update-mounted', this.isListMounted)
        //Cargamos la informacion
        axios.post("/reports/list-billings-proj", {
            start: this.scope.dateStart,
            end: this.scope.dateEnd
        })
            .then(request => {
                this.isListMounted = true;
                this.arrayDTO = request.data.message
                this.$emit('update-mounted', this.isListMounted)
                //Acomodamos el array a enviar
                this.arrayDTO.forEach(billingInfo => {
                    this.directiveList.push({
                        fecha: billingInfo.hiring_date,
                        cliente: billingInfo.bussiness_name,
                        proyecto: billingInfo.project_description,
                        socioc: billingInfo.quality_partner_name,
                        sociop: billingInfo.partner_name,
                        monto: this.formatReportNumber(billingInfo.project_value) + ' ' + billingInfo.currency_symbol,
                        billings: this.formatReportNumber(billingInfo.billings) + ' ' + billingInfo.currency_symbol,
                        estatus: billingInfo.status_id
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
