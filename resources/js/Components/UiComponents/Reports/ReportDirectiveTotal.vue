<template>
    <div>
        <Loading :active="!isListMounted"></Loading>
        <ListingCrud v-if="isListMounted && directivePaginatio != 0 && refTotal != 0" :title-object="reportColumns"
            :pagination-lenght="directivePaginatio" :pagination-limit="directiveLength" :table-info="directiveList"
            title-table="Reporte de horas administrativas" not-found-message="No hay horas cargadas"
            :select-search="selectSearch" view-search view-excel title-excel="ReporteHorasNoCargables.xls"
            status-table="usuarios" view-hours :hours-ref="refTotal">
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
                column2: 'Cargo',
                column3: 'Area',
                column4: 'Horas Proy',
                column5: '% Proy',
                column6: 'Horas Admon',
                column7: '% Horas Admon',
                column8: 'Total horas',
                column9: '% Carga total',
                column10: 'Ref Total',
                column11: 'Estatus',
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
        //Pasamos como parametro el intervalo de fechas
        axios.post("reports/list-directive-total", { startDate: this.scope.dateStart, endDate: this.scope.dateEnd })
            .then(request => {
                this.isListMounted = true
                let directiveDTO = request.data.message.reduce((acum, intervalData) => {
                    //Creamos una Key
                    const key = intervalData.order_user
                    if (!acum[key]) {
                        acum[key] = {
                            nombre: intervalData.nombre,
                            cargo: intervalData.cargo,
                            area: intervalData.area,
                            proy_hours: parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, ".")),
                            admin_hours: parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, ".")),
                            ref_total: parseFloat(intervalData.ref_total.replace(/\./g, "").replace(/,/, ".")),
                            estatus: intervalData.estatus
                        }
                    } else {
                        acum[key].admin_hours += parseFloat(intervalData.admin_hours.replace(/\./g, "").replace(/,/, "."))
                        acum[key].proy_hours += parseFloat(intervalData.proy_hours.replace(/\./g, "").replace(/,/, "."))
                        acum[key].ref_total += parseFloat(intervalData.ref_total.replace(/\./g, "").replace(/,/, "."))
                    }
                    return acum;
                }, {});
                //Agregamos los porcentajes y el total de horas
                console.log(typeof directiveDTO)
                directiveDTO = Object.values(directiveDTO)
                directiveDTO.forEach((user) => {
                    const totalHours = user.proy_hours + user.admin_hours;
                    const percenAdmin = (user.admin_hours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    const percenProy = (user.proy_hours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    const percenTotal = (totalHours * 100) / (user.ref_total == 0 ? 1 : user.ref_total);
                    //Inservamos el nuevo objeto
                    this.directiveList.push({
                        nombre: user.nombre,
                        cargo: user.cargo,
                        area: user.area,
                        proy_hours: Number(user.proy_hours).toLocaleString('de-DE'),
                        percen_proy: Number(percenProy).toLocaleString('de-DE'),
                        admin_hours: Number(user.admin_hours).toLocaleString('de-DE'),
                        percen_admon: Number(percenAdmin).toLocaleString('de-DE'),
                        total_hours: Number(totalHours).toLocaleString('de-DE'),
                        percen_total: Number(percenTotal).toLocaleString('de-DE'),
                        ref_total: Number(user.ref_total).toLocaleString('de-DE'),
                        estatus: user.estatus
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
