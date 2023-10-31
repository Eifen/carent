<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de proyectos" not-found-message="No hay proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraProyectos.xls" white-space
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
    data() {
        return {
            reportColumns: {
                column1: "Código",
                column2: 'Proyecto',
                column3: 'Monto',
                column4: 'Cuotas',
                column5: 'Monto Cuotas',
                column6: 'Cuotas por facturar',
                column7: 'Facturas por cobrar'
            },
            selectSearch: {
                select1: "Proyecto",
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            countQuotas: 0, //Cuenta las cuotas por facturar
            countPayment: 0, //Cuenta las facturas por cobrar
        }
    },
    mounted() {
        if (this.scope.isMounted) {
            //Cargamos la informacion inicial
            const reportDTO = this.scope.listData.reduce((acum, billing) => {
                const key = billing.project_id

                if (!acum[key]) {
                    acum[key] = {
                        "código": billing.project_id,
                        proyecto: billing.project_description,
                        monto: this.formatReportNumber(parseFloat(billing.project_value)),
                        cuotas: billing.project_quotas,
                        monto_cuotas: this.formatReportNumber(parseFloat(billing.quotas_value)),
                        cuotas_por_facturar: billing.project_quotas,
                        facturar_por_cobrar: billing.payment_date === null ? 1 : 0
                    }
                } else {
                    const billingCompare = {
                        quotas: acum[key].cuotas_por_facturar,
                        payment: acum[key].facturar_por_cobrar,
                    }
                    //Si la factura es mayor a la cuota, restamos de las cuotas por facturar
                    //Si la factura no ha sido cobrada, aumentando el valor de las facturas por cobrar
                    acum[key].cuotas_por_facturar = parseFloat(billing.billing_value) >= parseFloat(billing.quotas_value)
                        ? (billingCompare.quotas == 0 ? 0 : (billingCompare.quotas - 1))
                        : (billingCompare.quotas >= billing.project_quotas ? billing.project_quotas : billingCompare.quotas + 1);

                    acum[key].facturar_por_cobrar = billing.payment_date === null
                        ? billingCompare.payment + 1
                        : (billingCompare.payment == 0 ? 0 : billingCompare.payment - 1);
                }

                return acum;
            }, {})
            //Asignamos la reduccion al array
            this.directiveList = Object.values(reportDTO)
            //Acomodamos la longitud minima y su paginacion
            if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
            this.directivePaginatio = Math.ceil(
                this.directiveList.length / this.directiveLength
            );
        }
    },
    components: { ListingCrud, Loading },
    mixins: [globalMethodsReport]
}
</script>
