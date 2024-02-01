<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de Cuotas" not-found-message="No hay cuotas cargadas" :select-search="selectSearch"
            view-search view-excel title-excel="ReporteCuotas.xls" white-space status-table="usuarios"
            :is-admin="scope.controlAdmin" :area-id="scope.departmentId">
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
                column3: "Cliente",
                column4: 'Monto',
                column5: 'Cuotas',
                column6: 'Cuotas por facturar',
                column7: 'Facturas por cobrar'
            },
            selectSearch: {
                select1: "Proyecto",
                select2: "Cliente"
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
            //Almacenamos la cantidad de facturas
            const billingHistory = Object.values(this.scope.listData.reduce((acum, billing) => {
                const key = billing.project_id

                if (!acum[key]) {
                    acum[key] = {
                        id: billing.project_id,
                        billing_total: 1,
                        total_quota_value: parseFloat(billing.billing_value)
                    }
                } else {
                    acum[key].billing_total += 1
                    acum[key].total_quota_value += parseFloat(billing.billing_value)
                }

                return acum
            }, {}))

            //Cargamos la informacion inicial
            const reportDTO = this.scope.listData.reduce((acum, billing) => {
                const key = billing.project_id
                const findTotal = billingHistory.find(billing => billing.id == key).total_quota_value

                if (!acum[key]) {
                    acum[key] = {
                        "código": billing.project_id,
                        proyecto: billing.project_description,
                        cliente: billing.bussiness_name,
                        monto: this.formatReportNumber(parseFloat(billing.project_value)) + billing.currency_symbol,
                        cuotas: billing.project_quotas,
                        cuotas_por_facturar: parseFloat(billing.project_value) <= findTotal ? 0 : billing.project_quotas,
                        facturar_por_cobrar: billing.payment_date === null ? 1 : 0
                    }
                } else {
                    const billingCompare = {
                        value: findTotal,
                        payment: acum[key].facturar_por_cobrar,
                        quotas: acum[key].cuotas_por_facturar,
                    }
                    //Si la factura es mayor a la cuota, restamos de las cuotas por facturar
                    //Si la factura no ha sido cobrada, aumentando el valor de las facturas por cobrar
                    if (acum[key]["código"] == 2) console.log(billingCompare.quotas)
                    acum[key].cuotas_por_facturar = parseFloat(billing.project_value) <= billingCompare.value
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
