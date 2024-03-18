<template>
    <fieldset :class="scope.formClass.fieldset">
        <!-- Periodo -->
        <div class="mb-3">
            <label for="Periodo">Periodo<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon3">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Primer periodo" id="Periodo"
                    aria-describedby="basic-addon3" v-model="scope.inputPeriodo" />
            </div>
        </div>
        <!-- Fecha Desde -->
        <div class="mb-3">
            <label for="Fecha_desde">Fecha Desde<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <input disabled type="text" placeholder="DD-MM-AAAA" class="form-control" v-model="scope.inputFechaDesde"
                    aria-describedby="basic-addon1">
                <span class="input-group-text" id="basic-addon1">
                    <Calendar @to-input="fechasPeriodo($event, 'desde')"></Calendar>
                </span>
            </div>
        </div>
        <!-- Fecha hasta -->
        <div class="mb-3">
            <label for="Fecha_hasta">Fecha Hasta<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <input disabled type="text" placeholder="DD-MM-AAAA" class="form-control" v-model="scope.inputFechaHasta"
                    aria-describedby="basic-addon1">
                <span class="input-group-text" id="basic-addon1">
                    <Calendar @to-input="fechasPeriodo($event, 'hasta')"></Calendar>
                </span>
            </div>
        </div>
        <!-- Descripcion -->
        <div class="mb-3">
            <label for="Descripcion_periodo">Descripción</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon3">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Algún detalle" id="descripcionPeriodo"
                    aria-describedby="basic-addon3" v-model="scope.inputPeriodoDescripcion" />
            </div>
        </div>
        <!-- Selección de Tipo de evaluacion -->
        <div class="mb-3">
            <label for="Tipos">Tipo de Evaluación <span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" title="TipoSelect" v-model="scope.inputTipoSelect">
                    <option v-for="(select, cursor) in scope.dataSelect.tipos" :key="cursor"
                        :value="select.evaluation_type_id" :disabled="select.evaluation_type_id == 0"
                        :selected="select.evaluation_type_id == 0">
                        <span v-if="select.evaluation_type_id != 0">{{ select.evaluation_type_description }}</span>
                        <span v-if="select.evaluation_type_id == 0">Seleccione el Tipo</span>
                    </option>
                </select>
            </div>
        </div>
        <!-- Selección de Metodo -->
        <div class="mb-3">
            <label for="Metodo">Método de Evaluación<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" title="MethodoSelect" v-model="scope.inputMetodoSelect">
                    <option v-for="(select, cursor) in scope.dataSelect.metodos" :key="cursor"
                        :value="select.evaluation_method_id" :disabled="select.evaluation_method_id == 0"
                        :selected="select.evaluation_method_id == 0">
                        <span v-if="select.evaluation_method_id != 0">{{ select.status_description }}</span>
                        <span v-if="select.evaluation_method_id == 0">Método de Evaluación</span>
                    </option>
                </select>
            </div>
        </div>
        <!-- Observacion -->
        <div class="mb-3">
            <label for="observacion">Observación</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon3">
                    <font-awesome string-icon="fa-solid fa-user"></font-awesome>
                </span>
                <input type="text" class="form-control" placeholder="Ejemplo: Debido a..." id="observacion"
                    aria-describedby="basic-addon3" v-model="scope.inputObservacion" />
            </div>
        </div>
        <!-- Estado del periodo.-->
        <div class="mb-3">
            <label for="Status">Estatus del periodo<span :class="scope.formClass.requiredField">*</span></label>
            <div class="input-group">
                <select class="form-select" v-model="scope.inputStatusSelect" title="StatusSelect">
                    <option value=0 selected disabled>Seleccione el estado</option>
                    <option v-for="(select, cursor) in scope.dataSelect.status" :key="cursor" :value="select.status_id">
                        {{ select.status_description }}
                    </option>
                </select>
            </div>
        </div>
    </fieldset>
</template>

<script>
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";
import Calendar from "@/Components/Calendar.vue"

export default {
    props: {
        scope: Object, //Hereda la data del padre
        isEdit: Boolean, //Cambia la información en caso de edit
    },



    components: { FontAwesome, Calendar },
    methods: {


        fechasPeriodo(mostrarFecha, tipoFecha) {
            // año-mes-dia

            /* TODO
            1.- algoritmo que defina en que tipo esta desde o hasta
            2.- dependiendo en que tipo esté va a almacenar la fecha en formato yyyy-mm-dd en el input de fecha desde o hasta
            */
            if (tipoFecha === "desde") {
                this.scope.inputFechaDesde = `${mostrarFecha.year}-${mostrarFecha.month}-${mostrarFecha.day}`
            } else if (tipoFecha === "hasta") {
                this.scope.inputFechaHasta = `${mostrarFecha.year}-${mostrarFecha.month}-${mostrarFecha.day}`
            }


            // console.log(mostrarFecha)
            // console.log(tipoFecha)
        }
    },
    mounted() { },

};
</script>
