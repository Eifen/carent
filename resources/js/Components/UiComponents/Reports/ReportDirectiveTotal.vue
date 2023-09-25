<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0 && refTotal != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de horas administrativas" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteDirectivoAcumulado.xls"
            title-resume-excel="ResumenDirectivoAcumulado.xls" title-consolidated-excel="ConsolidadoDirectivoAcumulado.xls"
            status-table="usuarios" view-hours :hours-ref="refTotal" directive>
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
    emits: ['update-mounted'],
    data() {
        return {
            reportColumns: {
                column1: 'Nombre',
                column2: 'Area',
                column3: "Nivel",
                column4: "% Carga proy",
                column5: "% Carga admon",
                column6: "Hor Esp proy",
                column7: "Hor Esp admon",
                column8: 'Ref Total',
                column9: "Eval",
                column10: 'Horas Proy',
                column11: '% Proy',
                column12: 'Horas Admon',
                column13: '% Horas Admon',
                column14: 'Total horas',
                column15: '% Carga total',
                column16: 'Estatus',
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
                        acum[key].ref_total += parseFloat(intervalData.ref_total.replace(/\./g, "").replace(/,/, "."))
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
                        nivel: user.nivel,
                        "%_carga_min_proy": Number(user.percen_carg).toLocaleString("de-DE"),
                        "%_carga_min_admon": Number(100 - user.percen_carg).toLocaleString("de-DE"),
                        hor_esp_proy: Number((refTotal * (user.percen_carg / 100)).toFixed(2)).toLocaleString("de-DE"),
                        hor_esp_admon: Number((refTotal * ((100 - user.percen_carg) / 100)).toFixed(2)).toLocaleString("de-DE"),
                        hor_ref: Number(user.ref_total.toFixed(2)).toLocaleString('de-DE'),
                        eval: user.percen_carg > percenProy ? "DE" : "E",
                        tot_hor_proy: Number(user.proy_hours.toFixed(2)).toLocaleString('de-DE'),
                        "%_hor_proy": Number(percenProy.toFixed(2)).toLocaleString('de-DE'),
                        tot_hor_admon: Number(user.admin_hours.toFixed(2)).toLocaleString('de-DE'),
                        "%_hor_admon": Number(percenAdmin.toFixed(2)).toLocaleString('de-DE'),
                        tot_hor: Number(totalHours.toFixed(2)).toLocaleString('de-DE'),
                        "%_tot_hor": Number(percenTotal.toFixed(2)).toLocaleString('de-DE'),
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
    components: { ListingCrud, Loading }
}
</script>
