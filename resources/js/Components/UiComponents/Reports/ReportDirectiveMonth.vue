<template>
    <div>
        TODO: Reporte directivo mensual
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
                column1: 'Código',
                column2: 'Gerente',
                column3: 'Socio',
                column4: 'Fecha de cierre',
                column5: 'Proyecto',
                column6: 'Horas estimadas',
                column7: 'Honorarios estimados',
                column8: 'Tasa promedio',
                column9: 'Horas adicionales',
                column10: 'Honorarios adicionales',
                column11: 'Tasa promedio total',
                column12: 'Horas Reales',
                column13: 'Honorarios Reales',
                column14: 'Tasa promedio real',
            },
            selectSearch: {
                select1: "Código",
                select2: "Proyecto",
                select3: "Gerente",
                select4: "Socio",
                select5: "Fecha desde",
                select6: "Fecha hasta",
            },
        }
    },
    mounted() {
        console.log(this.scope)
    },
    components: { ListingCrud, Loading }
}
</script>
