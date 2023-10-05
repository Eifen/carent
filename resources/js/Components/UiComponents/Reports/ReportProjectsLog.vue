<template>
    <div>
        <Loading :active="!scope.isMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="scope.isMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de proyectos" not-found-message="No hay proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraProyectos.xls" white-space>
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
                column1: "ID",
                column2: 'Cliente',
                column3: 'Proyecto',
                column4: 'Area',
                column5: 'Cargo',
                column6: 'Empleado',
                column7: 'Horas asignadas',
                column8: 'Horas registradas',
                column9: 'Horas totales del proyecto',
                column10: 'Estatus',
            },
            selectSearch: {
                select1: "Cliente",
                select2: "Proyecto",
                select3: "Estatus"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
        }
    },
    mounted() {
        if (this.scope.isMounted) {
            let areaDTO = this.scope.listData.map(object => object.department_name)
            areaDTO = Object.values(areaDTO);
            //Cargamos la informacion inicial
            let listDTO = this.scope.listData.reduce((acum, logInfo, pos) => {
                const key = logInfo.project_id

                if (!acum[key]) {
                    acum[key] = {
                        id: logInfo.project_id,
                        cliente: logInfo.cliente,
                        proyecto: logInfo.proyecto,
                        area: logInfo.department_name,
                        cargo: logInfo.position_name,
                        empleado: logInfo.user_name,
                        hora_asignada: logInfo.assigned_hour,
                        hora_registrada: Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour).toLocaleString('de-DE'),
                        hora_proyecto: logInfo.project_hours,
                        estatus: logInfo.estatus
                    }
                } else {
                    this.scope.listData[pos - 1].department_name == logInfo.department_name ? acum[key].area += `\n` : acum[key].area += `\n${logInfo.department_name}`;
                    acum[key].cargo += `\n${logInfo.position_name}`;
                    acum[key].empleado += `\n${logInfo.user_name}`;
                    acum[key].hora_asignada += `\n${logInfo.assigned_hour}`;
                    acum[key].hora_registrada += `\n${Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour).toLocaleString('de-DE')}`;
                }

                return acum;
            }, {})
            listDTO = Object.values(listDTO)
            this.directiveList = listDTO
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
