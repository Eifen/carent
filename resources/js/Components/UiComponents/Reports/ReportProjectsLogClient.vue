<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de proyectos-cliente" not-found-message="No hay proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraProyectosCliente.xls"
            white-space :excel-view="listDTO2">
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
                column1: "ID",
                column2: 'Cliente',
                column3: 'Proyecto',
                column4: 'Socio',
                column5: 'Fecha contrato',
                column6: 'Monto estimado',
                column7: 'Monto real',
                column8: 'Horas estimadas',
                column9: 'Estatus',
            },
            selectSearch: {
                select1: "Cliente",
                select2: "Proyecto",
                select3: "Socio",
                select4: "Estatus"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            isListMounted: false,
            arrayDTO: [], //Almacena el array de facturas
            listDTO2: [], //Almacena el array a exportar a excel
        }
    },
    mounted() {
        this.isListMounted = false;
        this.$emit('update-mounted', this.isListMounted)
        //Cargamos la informacion
        axios.post("/reports/list-logs-projects", {
            startDate: this.scope.dateStart,
            endDate: this.scope.dateEnd
        })
            .then(request => {
                this.isListMounted = true;
                this.arrayDTO = request.data.message
                this.$emit('update-mounted', this.isListMounted)
                const startDate = new Date(this.scope.dateStart);
                const endDate = new Date(this.scope.dateEnd);
                //Cargamos la informacion inicial
                this.listDTO2 = this.arrayDTO.filter((rep) => {
                    const dateInterval = new Date(rep.fecha)
                    return dateInterval >= startDate && endDate <= endDate
                })
                //Despues de filtrar por fecha
                this.listDTO2 = this.listDTO2.reduce((acum, logInfo, pos) => {
                    const key = logInfo.project_id + "-" + logInfo.user_name

                    if (!acum[key]) {
                        acum[key] = {
                            id: logInfo.project_id,
                            cliente: logInfo.cliente,
                            proyecto: logInfo.proyecto,
                            socio: logInfo.partner_name,
                            fecha_contrato: logInfo.fecha,
                            monto_est: logInfo.monto + logInfo.moneda,
                            monto_real: (parseFloat(logInfo.monto) + parseFloat(logInfo.monto_adicional)) + logInfo.moneda,
                            hora_proyecto: logInfo.project_hours,
                            estatus: logInfo.estatus
                        }
                    }

                    return acum;
                }, {})

                this.listDTO2 = Object.values(this.listDTO2)

                this.listDTO2 = this.listDTO2.sort((a, b) => b.id - a.id)

                this.directiveList = this.listDTO2
                //Acomodamos la longitud minima y su paginacion
                if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                this.directivePaginatio = Math.ceil(
                    this.directiveList.length / this.directiveLength
                );
            })
            .catch(error => console.error(error))

    },
    mixins: [globalMethodsReport],
    components: { ListingCrud, Loading }
}
</script>
