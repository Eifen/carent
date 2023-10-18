<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de proyectos" not-found-message="No hay proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraProyectos.xls" white-space
            status-table="usuarios">
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
                column1: "Nombre",
                column2: 'Código',
                column3: 'Cédula',
                column4: 'Area',
                column5: 'Cargo',
                column6: 'Estatus',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Código",
                select3: "Area",
                select4: "Estatus"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
        }
    },
    mounted() {
        if (this.scope.isMounted) {
            //Cargamos la informacion inicial
            this.scope.listData.forEach(users => {
                this.directiveList.push({
                    nombre: users.user_name,
                    "código": users.user_code,
                    "cedúla": users.user_identity,
                    area: users.department_name,
                    cargo: users.position_name,
                    estatus: users.status_id
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
