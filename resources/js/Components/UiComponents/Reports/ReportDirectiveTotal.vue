<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0 && refTotal != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de horas administrativas" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteDirectivoAcumulado.xls"
            title-resume-excel="ResumenDirectivoAcumulado.xls" title-consolidated-excel="ConsolidadoDirectivoAcumulado.xls"
            status-table="usuarios" view-hours :hours-ref="refTotal" directive :is-admin="scope.controlAdmin"
            :area-id="scope.departmentId">
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
                column1: 'Nombre',
                column2: 'Area',
                column3: 'Correo',
                column4: "Nivel",
                column5: "% Carga proy",
                column6: "% Carga admon",
                column7: "Hor Esp proy",
                column8: "Hor Esp admon",
                column9: 'Ref Total',
                column10: "Eval",
                column11: 'Horas Proy',
                column12: '% Proy',
                column13: 'Horas Admon',
                column14: '% Horas Admon',
                column15: 'Total horas',
                column16: '% Carga total',
                column17: 'Estatus',
            },
            selectSearch: {
                select1: "Nombre",
                select2: "Estatus",
                select3: "Area"
            },
            directiveList: [], //Lista directiva mensual
            directiveLength: 50, //Numero maximo por pagina
            directivePaginatio: 0, //Numero maximo de paginas
            refTotal: 0, //Numero total de horas a cargar
            isListMounted: false
        }
    },
    mounted() {
        this.isListMounted = false;
        this.$emit('update-mounted', this.isListMounted)
        //Pasamos como parametro el intervalo de fechas
        axios.post("reports/list-directive-total", { startDate: this.scope.dateStart, endDate: this.scope.dateEnd })
            .then(request => {
                this.isListMounted = true
                this.$emit('update-mounted', this.isListMounted)
                let requestDTO = [];
                request.data.message.forEach(user => {
                    user.forEach(period => {
                        requestDTO.push(period);
                    });
                });
                let directiveDTO = requestDTO.reduce((acum, intervalData) => {
                    //Creamos una Key
                    const key = intervalData.order_user
                    if (!acum[key]) {
                        acum[key] = {
                            nombre: intervalData.nombre,
                            area: intervalData.area,
                            correo: intervalData.correo,
                            nivel: intervalData.nivel,
                            percen_carg: intervalData.percen_carg,
                            proy_hours: parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, ".")),
                            admin_hours: parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, ".")),
                            ref_total: parseFloat(intervalData.ref_total.replace(/\./g, "").replace(/,/, ".")),
                            estatus: intervalData.estatus,
                            egreso: intervalData.fecha_egreso,
                            order: intervalData.order,
                            department_order: intervalData.department_order
                        }
                    } else {
                        acum[key].admin_hours += parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, "."))
                        acum[key].proy_hours += parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, "."))
                    }
                    return acum;
                }, {});
                //Agregamos los porcentajes y el total de horas
                directiveDTO = Object.values(directiveDTO)
                //Ordenamos el array
                directiveDTO.sort(function (a, b) {
                    //Comparamos el orden de cargos
                    let sort = a.department_order - b.department_order
                    if (sort == 0) sort = a.order - b.order;
                    return sort;
                })
                directiveDTO.forEach((user) => {
                    const totalHours = user.proy_hours + user.admin_hours;
                    const percenAdmin = (user.admin_hours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    const percenProy = (user.proy_hours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    const percenTotal = (totalHours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    const refTotal = user.ref_total;
                    //Inservamos el nuevo objeto
                    this.directiveList.push({
                        nombre: user.nombre,
                        area: user.area,
                        correo: user.correo,
                        nivel: user.nivel,
                        "%_carga_min_proy": this.formatReportNumber(user.percen_carg),
                        "%_carga_min_admon": this.formatReportNumber(100 - user.percen_carg),
                        hor_esp_proy: this.formatReportNumber((refTotal * (user.percen_carg / 100))),
                        hor_esp_admon: this.formatReportNumber((refTotal * ((100 - user.percen_carg) / 100))),
                        hor_ref: this.formatReportNumber(user.ref_total),
                        eval: user.percen_carg > percenProy ? "DE" : "E",
                        tot_hor_proy: this.formatReportNumber(user.proy_hours),
                        "%_hor_proy": this.formatReportNumber(percenProy),
                        tot_hor_admon: this.formatReportNumber(user.admin_hours),
                        "%_hor_admon": Number(percenAdmin.toFixed(2)).toLocaleString('de-DE'),
                        tot_hor: this.formatReportNumber(totalHours),
                        "%_tot_hor": this.formatReportNumber(percenTotal),
                        estatus: user.estatus,
                        fecha_egreso: user.egreso
                    });
                })
                this.refTotal = request.data.refHour
                //Acomodamos la longitud minima y su paginacion
                if (this.directiveList.length < 50) this.directiveLength = this.directiveList.length;
                this.directivePaginatio = Math.ceil(
                    this.directiveList.length / this.directiveLength
                );
            })
            .catch(error => { console.error(error) })
    },
    mixins: [globalMethodsReport],
    components: { ListingCrud, Loading }
}
</script>
