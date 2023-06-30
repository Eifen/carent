<template>
    <div>
        <select class="form-select" v-model="inputHourSelected" title="ConceptSelect">
            <option v-for="(hour, cursor) in limitHours" :key="cursor" :value="cursor" :selected="cursor == 0"
                :disabled="cursor == 0">{{
                    hour.label }}</option>
        </select>
        <textarea type="text" rows="5" class="form-control register-hour-select-hours" placeholder="Direccion del cliente"
            id="direccion" aria-describedby="basic-addon4"></textarea>
    </div>
</template>
<script>
import Multiselect from "@vueform/multiselect";
export default {
    props: {
        selectModel: Array, //Variable del v-model
        selectMode: String, //mode del multiSelect
        placeholderMessage: String, //Información del select
        listOptions: Array, //Opciones del multiSelect
    },
    data() {
        return {
            limitHours: [{
                value: 0,
                label: "Hora trabajada"
            }], // Array que muestra las horas en un intervalo de 0.30 a 11 horas
            intervalHours: 1 / 2, //Determina el intervalo entre horas en fracciones
            maximumHours: 11, // Multiplicador de horas
            inputHourSelected: 0, //Controla el seleccionador de la hora
        }
    },
    created() {
        //Proceso de carga de horas\
        let fraccionCount = 0;
        for (let countHours = 0; countHours < (this.maximumHours / this.intervalHours); countHours++) {
            //Sumamos la fraccion
            fraccionCount = fraccionCount + this.intervalHours
            const hourFraccion = 60 * (fraccionCount % 1)
            //Agregamos la fraccion
            this.limitHours.push({
                value: fraccionCount,
                label: `${Math.trunc(fraccionCount).toString().padStart(2, '0')}:${hourFraccion.toString().padStart(2, '0')}`
            })

        }
    },
    components: { Multiselect }
}
</script>
