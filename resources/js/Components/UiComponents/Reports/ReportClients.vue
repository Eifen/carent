<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de clientes" not-found-message="No hay clientes con proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraClientes.xls" white-space>
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
                column1: "Código",
                column2: 'Cliente',
                column3: "Teléfono",
                column4: "Correo",
                column5: 'SocioC',
                column6: 'Proyecto',
                column7: 'SocioP',
                column8: 'Estatus',
            },
            selectSearch: {
                select1: "Código",
                select2: "Cliente",
                select3: "SocioC",
                select4: "SocioP",
                select5: "Estatus"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
        }
    },
    mounted() {
        if (this.scope.isMounted) {
            //Cargamos la informacion inicial
            this.scope.listData.forEach(clients => {
                this.directiveList.push({
                    "código": clients.client_code,
                    "cliente": clients.bussiness_name,
                    "teléfono": clients.tax_phone,
                    "correo": clients.tax_email,
                    "socioc": clients.partner_client,
                    "proyecto": clients.project_description,
                    "sociop": clients.partner_project,
                    estatus: clients.status_id
                })
            })
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
