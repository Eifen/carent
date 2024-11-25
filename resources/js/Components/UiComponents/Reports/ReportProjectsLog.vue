<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud style="width: 90%;" v-if="isListMounted && directivePaginatio != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte bitácora de proyectos" not-found-message="No hay proyectos cargados"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteBitacoraProyectos.xls" white-space
            :excel-view="listDTO2">
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
                column5: 'Area',
                column6: 'Cargo',
                column7: 'Empleado',
                column8: 'Monto estimado',
                column9: 'Monto real',
                column10: 'Horas reales asignadas',
                column11: 'Horas registradas',
                column12: 'Horas estimadas',
                column13: 'Tasa promedio inicial',
                column14: 'Horas totales asignadas',
                column15: 'Tasa promedio total',
                column16: 'Total horas registradas',
                column17: 'Rentabilidad actual (Tasa Final)',
                column18: 'Estatus',
            },
            selectSearch: {
                select1: "Cliente",
                select2: "Proyecto",
                select3: "Empleado",
                select4: "Estatus"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            isListMounted: false,
            arrayDTO: [], //Almacena el array de facturas
            listDTO2: [] //Almacena el array a exportar a excel
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
                let areaDTO = this.arrayDTO.map(object => object.department_name)
                areaDTO = Object.values(areaDTO);
                //Cargamos la informacion inicial
                this.listDTO2 = this.scope.listData.reduce((acum, logInfo, pos) => {
                    const key = logInfo.project_id + "-" + logInfo.user_name

                    if (!acum[key]) {
                        acum[key] = {
                            id: logInfo.project_id,
                            cliente: logInfo.cliente,
                            proyecto: logInfo.proyecto,
                            socio: logInfo.partner_name,
                            area: logInfo.department_name,
                            cargo: logInfo.position_name,
                            empleado: logInfo.user_name,
                            monto_est: [logInfo.monto, logInfo.moneda],
                            monto_real: [(parseFloat(logInfo.monto) + parseFloat(logInfo.monto_adicional)), logInfo.moneda],
                            hora_asignada: logInfo.assigned_hour,
                            hora_registrada: Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour),
                            hora_proyecto: logInfo.project_hours,
                            av_est: logInfo.tasa,
                            hor_tot_asig: logInfo.assigned_hour,
                            av_tot_asig: 0,
                            hor_tot_real: Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour),
                            av_tot_rent: 0,
                            estatus: logInfo.estatus
                        }
                    } else {
                        acum[key].hora_asignada += logInfo.assigned_hour;
                        acum[key].hora_registrada += Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour);
                        acum[key].hor_tot_asig += logInfo.assigned_hour
                        acum[key].hor_tot_real += Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour)
                    }

                    return acum;
                }, {})

                this.listDTO2 = Object.values(this.listDTO2)

                let listDTO = this.scope.listData.reduce((acum, logInfo, pos) => {
                    const key = logInfo.project_id

                    if (!acum[key]) {
                        acum[key] = {
                            id: logInfo.project_id,
                            cliente: logInfo.cliente,
                            proyecto: logInfo.proyecto,
                            socio: logInfo.partner_name,
                            area: logInfo.department_name,
                            cargo: logInfo.position_name,
                            empleado: logInfo.user_name,
                            monto_est: [logInfo.monto, logInfo.moneda],
                            monto_real: [(parseFloat(logInfo.monto) + parseFloat(logInfo.monto_adicional)), logInfo.moneda],
                            hora_asignada: logInfo.assigned_hour,
                            hora_registrada: Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour),
                            hora_proyecto: logInfo.project_hours,
                            av_est: logInfo.tasa,
                            hor_tot_asig: logInfo.assigned_hour,
                            av_tot_asig: 0,
                            hor_tot_real: Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour),
                            av_tot_rent: 0,
                            estatus: logInfo.estatus
                        }
                    } else {
                        this.scope.listData[pos - 1].department_name == logInfo.department_name ? acum[key].area += `\n` : acum[key].area += `\n${logInfo.department_name}`;
                        acum[key].cargo += `\n${logInfo.position_name}`;
                        acum[key].empleado += `\n${logInfo.user_name}`;
                        acum[key].hora_asignada += `\n${logInfo.assigned_hour}`;
                        acum[key].hora_registrada += `\n${Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour).toLocaleString('de-DE')}`;
                        acum[key].hor_tot_asig += logInfo.assigned_hour
                        acum[key].hor_tot_real += Number(parseFloat(logInfo.register_hour) > parseFloat(logInfo.assigned_hour) ? logInfo.assigned_hour : logInfo.register_hour)
                    }

                    return acum;
                }, {})
                listDTO = Object.values(listDTO)

                listDTO.forEach((project, index) => {
                    if (project.hor_tot_asig != 0) listDTO[index].av_tot_asig = this.formatReportNumber(project.monto_real[0] / project.hor_tot_asig)
                    if (project.hor_tot_real != 0) listDTO[index].av_tot_rent = this.formatReportNumber(project.monto_real[0] / project.hor_tot_real)
                    //Agregamos el simbolo
                    listDTO[index].monto_est = `${this.formatReportNumber(project.monto_est[0])} ${project.monto_est[1]}`
                    listDTO[index].monto_real = `${this.formatReportNumber(project.monto_real[0])} ${project.monto_real[1]}`
                    //Formateamos los valores
                    listDTO[index].av_est = this.formatReportNumber(project.av_est)
                    listDTO[index].hor_tot_asig = this.formatReportNumber(project.hor_tot_asig)
                    listDTO[index].hor_tot_real = this.formatReportNumber(project.hor_tot_real)
                })

                listDTO = listDTO.sort((a, b) => b.id - a.id)

                this.directiveList = listDTO
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
