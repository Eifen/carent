<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de horas administrativas" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteHorasNoCargables.xls"
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
                column1: 'Nombre',
                column2: 'Código',
                column3: 'Concepto',
                column4: 'Total horas cargadas',
            },
            selectSearch: {
                select1: "Código",
                select2: "Nombre",
                select3: "Concepto",
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            isListMounted: false
        }
    },
    mounted() {
        //Acomodamos el array resultante de la informacion de carga de usuarios
        if (this.scope.listIntervalData) {
            console.log(this.scope.listIntervalData)
            let listDTO = this.scope.listIntervalData.reduce((acum, intervalData) => {
                //Creamos una Key
                const key = intervalData.user_id + "-" + intervalData.admin_hours_id + "-" + intervalData.concept_description
                //Verificar si la fecha entra dentro del intervalo
                const dateStart = new Date(this.scope.dateStart);
                const dateEnd = new Date(this.scope.dateEnd);
                const dateToSearch = new Date(intervalData.register_date);

                if (dateToSearch.getTime() >= dateStart.getTime() && dateToSearch.getTime() <= dateEnd.getTime()) {
                    //Filtramos la informacion por clave
                    !acum[key]
                        ? acum[key] = {
                            user_id: intervalData.user_id,
                            concept_admin: intervalData.concept_description,
                            admin_hours: parseFloat(intervalData.register_hour)
                        }
                        : acum[key].admin_hours += parseFloat(intervalData.register_hour)
                }
                return acum;
            }, {});
            //Convertimos a un array
            listDTO = Object.values(listDTO)

            //Lo pasamos como parametro al controlador para crear el formato
            axios.post("reports/admin-hours-report", { adminList: listDTO })
                .then(request => {
                    this.isListMounted = true
                    this.directiveList = request.data.message
                    //Acomodamos la longitud minima y su paginacion
                    if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                    this.directivePaginatio = Math.ceil(
                        this.directiveList.length / this.directiveLength
                    );
                })
                .catch(error => { console.error(error) })
        }
    },
    components: { ListingCrud, Loading }
}
</script>
