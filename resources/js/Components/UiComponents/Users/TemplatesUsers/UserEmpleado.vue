<template>
  <fieldset :class="scope.formClass.fieldset" v-if="scope.inputCheckedCrowe">
    <!-- Estado -->
    <div class="mb-3">
      <label for="Estado">Estado</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputEstadoSelect" title="StateSelect">
          <option v-for="(select, cursor) in scope.stateData" :key="cursor" :value="select.state_id"
            :selected="select.state_id == 0" :disabled="select.state_id == 0">
            <span v-if="select.state_id != 0">{{ select.state_name }}</span>
            <span v-if="select.state_id == 0">Seleccione el Estado</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Municipio -->
    <div class="mb-3">
      <label for="Municipio">Municipio</label>
      <div class="input-group">
        <select class="form-select" title="MunicipalitySelect" :disabled="scope.inputEstadoSelect == 0"
          v-model="scope.inputMunicipioSelect">
          <option value=0 selected disabled>Seleccione el Municipio</option>
          <option v-for="(select, cursor) in scope.municipality.select" :key="cursor" :value="select.municipality_id"
            :selected="select.municipality_id == 0" :disabled="select.municipality_id == 0">
            <span v-if="select.municipality_id != 0">{{ select.municipality_name }}</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Parroquia -->
    <div class="mb-3">
      <label for="Parroquia">Parroquia</label>
      <div class="input-group">
        <select class="form-select" title="ParishSelect" :disabled="scope.inputMunicipioSelect == 0"
          v-model="scope.inputParroquiaSelect">
          <option value=0 selected disabled>Seleccione la Parroquia</option>
          <option v-for="(select, cursor) in scope.parish.select" :key="cursor" :value="select.parish_id"
            :selected="select.parish_id == 0" :disabled="select.parish_id == 0">
            <span v-if="select.parish_id != 0">{{ select.parish_name }}</span>
          </option>
        </select>
      </div>
    </div>
    <!-- División -->
    <div class="mb-3">
      <label for="Division">División</label>
      <div class="input-group">
        <select class="form-select" v-model="scope.inputDivisionSelect" title="DivisionSelect">
          <option v-for="(select, cursor) in scope.divisionData" :key="cursor" :value="select.department_id"
            :selected="select.department_id == 0" :disabled="select.department_id == 0">
            <span v-if="select.department_id != 0">{{ select.department_name }}</span>
            <span v-if="select.department_id == 0">Seleccione la División</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Cargo -->
    <div class="mb-3">
      <label for="Cargo">Cargo</label>
      <div class="input-group">
        <select class="form-select" title="CargoSelect" :disabled="scope.inputDivisionSelect == 0"
          v-model="scope.inputCargoSelect">
          <option v-for="(select, cursor) in scope.cargoData" :key="cursor" :value="select.position_id"
            :selected="select.position_id == 0" :disabled="select.position_id == 0">
            <span v-if="select.position_id != 0">{{ select.position_name }}</span>
            <span v-if="select.position_id == 0">Seleccione el Cargo</span>
          </option>
        </select>
      </div>
    </div>
    <!-- Fecha de ingreso -->
    <div class="mb-3">
      <label for="ingreso">Fecha de Ingreso</label>
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="ingreso"
          v-model="scope.inputIngreso" disabled />
        <span class="input-group-text">
          <calendar @to-input="ingresoDTO"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.ingresoError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.ingresoError }}
      </div>
    </div>
    <!-- Fecha de egreso. Solo para el edit -->
    <div class="mb-3" v-if="enableEdit">
      <label for="egreso">Fecha de Egreso</label>
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Ejemplo: 1990-02-18" id="egreso" v-model="scope.inputEgreso"
          disabled />
        <span class="input-group-text">
          <calendar @to-input="egresoDTO"></calendar>
        </span>
      </div>
      <!-- Mensajes de error en fecha -->
      <div :class="scope.formClass.failureValidation" v-if="scope.messages.error.egresoError != ''">
        <font-awesome string-icon="fa-solid fa-circle-exclamation"></font-awesome>
        {{ scope.messages.error.egresoError }}
      </div>
    </div>
  </fieldset>
</template>

<script>
import Calendar from "@/Components/Calendar.vue";
import FontAwesome from "@/Components/FontAwesome/FontAwesome.vue";

export default {
  props: { scope: Object, enableEdit: Boolean }, //scope: Objeto encargado de heredar la data del padre
  components: { Calendar, FontAwesome },
  methods:
  {
    ingresoDTO(ingresoValue) { this.$emit('active-ingreso', ingresoValue) },
    egresoDTO(egresoValue) { this.$emit('active-egreso', egresoValue) }
  }
};
</script>
